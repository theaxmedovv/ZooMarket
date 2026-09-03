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
     *
     * @param Post $post
     * @return array{status: string, reason: ?string, flags: array, confidence: ?float}
     */
    public function moderate(Post $post): array
    {
        $apiKey = config('services.groq.key');
        $endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
        $visionModel = config('services.groq.vision_model', 'llama-3.2-11b-vision-preview');
        $textModel = config('services.groq.text_model', 'llama-3.3-70b-versatile');
        $timeout = config('services.groq.timeout', 30);

        if (empty($apiKey)) {
            Log::warning("GroqModerationService: GROQ_API_KEY is not configured. Marking post #{$post->id} as pending.");

            $result = [
                'status' => 'pending',
                'reason' => "Groq API kaliti sozlanmagan. E'lon AI moderatsiyasini kutmoqda.",
                'flags' => ['api_key_missing'],
                'confidence' => null,
            ];

            $this->applyResultToPost($post, $result);
            return $result;
        }

        try {
            // Collect images and encode them as base64 data URIs
            $imageUrls = $this->extractImageBase64DataUrls($post);
            $hasImages = !empty($imageUrls);

            $model = $hasImages ? $visionModel : $textModel;

            // Build listing summary text for moderation
            $textPrompt = $this->buildListingTextPrompt($post);

            // Construct OpenAI/Groq compatible message content
            $userContent = [];

            if ($hasImages) {
                $userContent[] = [
                    'type' => 'text',
                    'text' => $textPrompt,
                ];

                // Append up to 3 images to keep within token/payload budget
                foreach (array_slice($imageUrls, 0, 3) as $dataUrl) {
                    $userContent[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $dataUrl,
                        ],
                    ];
                }
            } else {
                $userContent = $textPrompt;
            }

            $systemPrompt = $this->buildSystemPrompt();

            $payload = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userContent,
                    ],
                ],
                'temperature' => 0.1,
                'response_format' => [
                    'type' => 'json_object',
                ],
            ];

            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->asJson()
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                Log::error("GroqModerationService API error for post #{$post->id}: " . $response->body());

                $result = [
                    'status' => 'pending',
                    'reason' => "AI moderatsiya xizmatida vaqtinchalik uzilish yuz berdi. E'lon tekshiruv navbatida turibdi.",
                    'flags' => ['api_error'],
                    'confidence' => null,
                ];

                $this->applyResultToPost($post, $result);
                return $result;
            }

            $data = $response->json();
            $rawContent = $data['choices'][0]['message']['content'] ?? '{}';
            $parsed = json_decode($rawContent, true) ?: [];

            $status = strtolower((string) ($parsed['status'] ?? 'pending'));
            if (!in_array($status, ['approved', 'rejected'], true)) {
                $status = 'pending';
            }

            $reason = !empty($parsed['reason']) ? trim((string) $parsed['reason']) : null;
            $flags = (array) ($parsed['flags'] ?? []);
            $confidence = isset($parsed['confidence']) ? (float) $parsed['confidence'] : null;

            // If rejected but no reason provided, supply a helpful default
            if ($status === 'rejected' && empty($reason)) {
                $reason = "E'lon hayvonlar savdosi xavfsizlik qoidalariga yoki rasm talablariga mos kelmadi.";
            }

            $result = [
                'status' => $status,
                'reason' => $status === 'approved' ? null : $reason,
                'flags' => $flags,
                'confidence' => $confidence,
            ];

            $this->applyResultToPost($post, $result);
            return $result;

        } catch (Throwable $e) {
            Log::error("GroqModerationService exception for post #{$post->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $result = [
                'status' => 'pending',
                'reason' => "AI tekshiruvida nosozlik yuz berdi. E'lon tez orada qayta tekshiriladi.",
                'flags' => ['exception'],
                'confidence' => null,
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
     *
     * @param Post $post
     * @return array<string>
     */
    public function extractImageBase64DataUrls(Post $post): array
    {
        $imagePaths = $post->allImages();
        $dataUrls = [];

        foreach ($imagePaths as $path) {
            $record = DatabaseImageService::get($path);
            if ($record && !empty($record->data)) {
                $mime = $record->mime_type ?: 'image/jpeg';
                $base64 = base64_encode($record->data);
                $dataUrls[] = "data:{$mime};base64,{$base64}";
            }
        }

        return $dataUrls;
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
- Uploaded photos clearly display the actual animal or pet-care item, with no signs of abuse, suffering, violence, or fake/irrelevant content.

RULES FOR REJECTION ("status": "rejected"):
Reject the listing if it violates ANY of the following:
1. Prohibited or Illegal Wildlife: Endangered species, illegally captured wild animals, banned poaching products, exotic species prohibited from domestic trade.
2. Animal Cruelty & Harm: Animal fighting (dog fighting, cock fighting), signs of animal abuse, suffering, visible gore, severed parts, torture, severe untreated injuries.
3. Irrelevant / Non-Animal Content: Listings for cars, real estate, electronics, weapons, random merchandise, or uploaded photos that contain NO animals at all (e.g. human selfies, random vehicles, screenshots, memes, food dishes).
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
