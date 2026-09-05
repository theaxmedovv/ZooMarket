<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GroqModerationService
{
    /**
     * Moderate a given animal listing post using the Groq API.
     * Analyzes both text fields and uploaded images.
     * Fast, resilient, and never leaves listings blocked in pending queues.
     *
     * @param Post $post
     * @return array{status: string, reason: ?string, flags: array, confidence: ?float}
     */
    public function moderate(Post $post): array
    {
        $apiKey = config('services.groq.key');
        $endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
        $visionModel = config('services.groq.vision_model', 'qwen/qwen3.8-27b');
        $textModel = config('services.groq.text_model', 'openai/gpt-oss-20b');
        $timeout = (int) config('services.groq.timeout', 15);

        // If API key is not configured, auto-approve immediately with zero delay and no pending wait
        if (empty($apiKey)) {
            Log::info("GroqModerationService: GROQ_API_KEY is not set. Auto-approving post #{$post->id} without delay.");

            $result = [
                'status' => 'approved',
                'reason' => null,
                'flags' => ['api_key_not_configured_auto_approved'],
                'confidence' => 1.0,
            ];

            $this->applyResultToPost($post, $result);
            return $result;
        }

        try {
            Log::info("Groq moderation started for post #{$post->id}");

            // Collect images (max 1 primary optimized image for rapid transmission)
            $imageUrls = $this->extractImageBase64DataUrls($post);
            $hasImages = !empty($imageUrls);

            // Build listing summary text for moderation
            $textPrompt = $this->buildListingTextPrompt($post);
            $systemPrompt = $this->buildSystemPrompt();

            $response = null;

            // Attempt 1: If images present, send primary image to vision model
            if ($hasImages) {
                $userContent = [
                    [
                        'type' => 'text',
                        'text' => $textPrompt,
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $imageUrls[0],
                        ],
                    ],
                ];

                $response = Http::withToken($apiKey)
                    ->timeout($timeout)
                    ->asJson()
                    ->post($endpoint, [
                        'model' => $visionModel,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userContent],
                        ],
                        'temperature' => 0.1,
                        'response_format' => ['type' => 'json_object'],
                    ]);
            }

            // Attempt 2: If no image OR vision model returned an error, fallback immediately to fast text model
            if (!$response || !$response->successful()) {
                if ($hasImages && $response) {
                    Log::warning("GroqModerationService: Vision model attempt returned {$response->status()}. Immediately falling back to text model.");
                }

                $response = Http::withToken($apiKey)
                    ->timeout($timeout)
                    ->asJson()
                    ->post($endpoint, [
                        'model' => $textModel,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $textPrompt],
                        ],
                        'temperature' => 0.1,
                        'response_format' => ['type' => 'json_object'],
                    ]);
            }

            // If API still failed or unreachable, do NOT block the listing in pending/time; auto-approve smoothly
            if (! $response->successful()) {
                Log::warning("GroqModerationService API error for post #{$post->id}: " . $response->body() . ". Auto-approving post to prevent queue delay.");

                $result = [
                    'status' => 'approved',
                    'reason' => null,
                    'flags' => ['api_error_fallback_approved'],
                    'confidence' => 1.0,
                ];

                $this->applyResultToPost($post, $result);
                return $result;
            }

            $data = $response->json();
            $rawContent = $data['choices'][0]['message']['content'] ?? '{}';
            $parsed = json_decode($rawContent, true) ?: [];

            $status = strtolower((string) ($parsed['status'] ?? 'approved'));
            // Default to approved if unexpected value to avoid stalling
            if (!in_array($status, ['approved', 'rejected'], true)) {
                $status = 'approved';
            }

            $reason = !empty($parsed['reason']) ? trim((string) $parsed['reason']) : null;
            $flags = (array) ($parsed['flags'] ?? []);
            $confidence = isset($parsed['confidence']) ? (float) $parsed['confidence'] : null;

            // If rejected but no reason provided, supply a helpful default in Uzbek
            if ($status === 'rejected' && empty($reason)) {
                $reason = "E'lon hayvonlar savdosi xavfsizlik qoidalariga yoki rasm talablariga mos kelmadi.";
            }

            Log::info("Groq moderation response for post #{$post->id}: status={$status}, confidence={$confidence}");

            $result = [
                'status' => $status,
                'reason' => $status === 'approved' ? null : $reason,
                'flags' => $flags,
                'confidence' => $confidence,
            ];

            $this->applyResultToPost($post, $result);
            return $result;

        } catch (Throwable $e) {
            Log::warning("GroqModerationService exception for post #{$post->id}: " . $e->getMessage() . ". Auto-approving post to prevent queue delay.");

            $result = [
                'status' => 'approved',
                'reason' => null,
                'flags' => ['exception_fallback_approved'],
                'confidence' => 1.0,
            ];

            $this->applyResultToPost($post, $result);
            return $result;
        }
    }

    /**
     * Helper to update the post record with moderation outcome.
     */
    protected function applyResultToPost(Post $post, array $result): void
    {
        $post->update([
            'moderation_status' => $result['status'],
            'moderation_reason' => $result['reason'],
            'moderated_at' => now(),
        ]);
    }

    /**
     * Retrieve images from DatabaseImageService and encode to Base64 data URIs.
     * Optimizes large images for ultra-fast payload delivery.
     *
     * @param Post $post
     * @return array<string>
     */
    public function extractImageBase64DataUrls(Post $post): array
    {
        $imagePaths = $post->allImages();
        $dataUrls = [];

        foreach (array_slice($imagePaths, 0, 1) as $path) {
            $record = DatabaseImageService::get($path);
            if ($record && !empty($record->data)) {
                $mime = $record->mime_type ?: 'image/jpeg';
                $binary = $this->optimizeImageBinary($record->data, $mime);
                $base64 = base64_encode($binary);
                $dataUrls[] = "data:{$mime};base64,{$base64}";
            }
        }

        return $dataUrls;
    }

    /**
     * Compress/resize image binary data if larger than 300KB to ensure fast network transfer.
     */
    protected function optimizeImageBinary(string $binary, string $mime): string
    {
        if (strlen($binary) <= 300 * 1024) {
            return $binary;
        }

        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return $binary;
        }

        try {
            $img = @imagecreatefromstring($binary);
            if (!$img) {
                return $binary;
            }

            $width = imagesx($img);
            $height = imagesy($img);
            $maxDim = 800;

            if ($width > $maxDim || $height > $maxDim) {
                $ratio = min($maxDim / $width, $maxDim / $height);
                $newW = (int) round($width * $ratio);
                $newH = (int) round($height * $ratio);

                $resized = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($img);
                $img = $resized;
            }

            ob_start();
            imagejpeg($img, null, 75);
            $compressed = ob_get_clean();
            imagedestroy($img);

            return $compressed ?: $binary;
        } catch (Throwable) {
            return $binary;
        }
    }

    /**
     * Build text description representing the post details.
     */
    protected function buildListingTextPrompt(Post $post): string
    {
        $categoryName = $post->category?->name ?? 'Belgilanmagan';
        $title = $post->title ?? '';
        $breed = $post->breed ?? '';
        $gender = $post->gender ?? '';
        $age = $post->age ?? '';
        $color = $post->color ?? '';
        $price = $post->price ? "{$post->price} {$post->currency}" : 'Kelishilgan';
        $location = $post->location ?? '';
        $description = $post->description ?? $post->content ?? '';

        return <<<TEXT
Hayvon e'loni ma'lumotlari:
- Sarlavha: {$title}
- Kategoriya: {$categoryName}
- Zot (Breed): {$breed}
- Jinsi: {$gender}
- Yoshi: {$age}
- Rangi: {$color}
- Narxi: {$price}
- Manzil: {$location}
- Batafsil tavsif: {$description}

Iltimos, ushbu matn va ilova qilingan barcha rasmlarni sinchiklab tekshirib, moderatsiya xulosasini JSON formatida qaytaring.
TEXT;
    }

    /**
     * Detailed system prompt enforcing animal marketplace safety rules.
     */
    protected function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are an expert Content Safety & Quality AI Moderator for "ZooMarket", an animal and pet marketplace.
Your task is to analyze an animal listing (including its title, description, breed, category, price, details, and ALL attached photos) and determine whether the listing is safe to publish.

RULES FOR APPROVAL ("status": "approved"):
- Genuine, legal, healthy, domestic pets, livestock, birds, reptiles, fish, farm animals, or legitimate animal equipment/care items.
- Respectful and accurate text description.
- Photos are OPTIONAL. If photos are attached, they must clearly display the actual animal or pet-care item, with no signs of abuse, suffering, violence, or fake/irrelevant content. If NO photos are attached, do NOT reject solely for missing photos; evaluate based on the text.

RULES FOR REJECTION ("status": "rejected"):
Reject the listing ONLY if it violates ANY of the following:
1. Prohibited or Illegal Wildlife: Endangered species, illegally captured wild animals, banned poaching products, exotic species prohibited from domestic trade.
2. Animal Cruelty & Harm: Animal fighting (dog fighting, cock fighting), signs of animal abuse, suffering, visible gore, severed parts, torture, severe untreated injuries.
3. Irrelevant / Non-Animal Content: Listings for cars, real estate, electronics, weapons, random merchandise, or uploaded photos that contain NO animals at all (e.g. human selfies, random vehicles, screenshots, memes, food dishes). Note: A listing without photos is NOT non-animal content if the text is genuinely about an animal.
4. Inappropriate or Offensive Material: Adult/NSFW content, nudity, violence, hate speech, narcotics, illegal items.
5. Deceptive / Fake Listings: Obvious scam descriptions, stolen stock photos with contradictory claims, nonsensical spam.

OUTPUT FORMAT:
You must respond with ONLY a valid JSON object matching this exact schema:
{
  "status": "approved" | "rejected",
  "reason": "If rejected, write a clear, polite, and constructive explanation in Uzbek (O'zbek tili) explaining why the listing or image was rejected so the seller can fix it. If approved, set this to null.",
  "flags": ["prohibited_species", "animal_cruelty", "non_animal_content", "inappropriate_image", "misleading_scam", "offensive_text"],
  "confidence": 0.0 - 1.0
}
PROMPT;
    }
}
