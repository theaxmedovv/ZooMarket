<?php

namespace App\Services;

use App\Models\Post;
use App\Support\ChannelPostText;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Publishes an approved listing to the public Telegram channel: an album of up to ten photos (one photo
 * goes as a single picture, none as a text message) with the caption on the first one. The first photo is
 * 16:9 with the price on the bottom left and the brand on the bottom right, the others are 4:3.
 *
 * When every detail of the listing does not fit a photo caption (1024 characters), the caption is short
 * and the full text follows as a reply to the album with buttons under it.
 *
 * This is a different bot and channel from the log one (services.telegram_channel, not logging.telegram).
 * Temporary Telegram errors are retried. It never throws: a Telegram outage must not break approving a listing.
 */
class TelegramChannelService
{
    private const MAX_PHOTOS = 10;

    private const MAIN = [1280, 720];

    private const OTHER = [1024, 768];

    public static function enabled(): bool
    {
        return (bool) (config('services.telegram_channel.token') && config('services.telegram_channel.chat_id'));
    }

    /**
     * @return bool true when Telegram accepted the post
     */
    public function publish(Post $post): bool
    {
        if (! self::enabled()) {
            return false;
        }

        try {
            $text = new ChannelPostText($post);
            $photos = $this->preparePhotos($post);
            $chat = config('services.telegram_channel.chat_id');

            if (! $photos) {
                $response = $this->message($chat, $text->fullText(), $text);
            } elseif (count($photos) === 1) {
                $response = $this->client()->attach('photo', $photos[0], 'faunara.jpg')
                    ->post($this->url('sendPhoto'), ['chat_id' => $chat, 'parse_mode' => 'HTML', 'caption' => $text->caption()]);
            } else {
                $request = $this->client();
                $media = [];
                foreach ($photos as $i => $bytes) {
                    $request = $request->attach("photo$i", $bytes, "faunara$i.jpg");
                    $media[] = ['type' => 'photo', 'media' => "attach://photo$i"]
                        + ($i === 0 ? ['caption' => $text->caption(), 'parse_mode' => 'HTML'] : []);
                }
                $response = $request->post($this->url('sendMediaGroup'), ['chat_id' => $chat, 'media' => json_encode($media)]);
            }

            if (! $response->successful()) {
                Log::warning("Telegram kanalga e'lon #{$post->id} yuborilmadi: " . ($response->json('description') ?? $response->status()));

                return false;
            }

            $post->forceFill(['telegram_posted_at' => now()])->saveQuietly();

            // The caption was short: the full details go right under the album, as a reply to it
            if ($photos && $text->needsSplit()) {
                $first = $response->json('result.0.message_id') ?? $response->json('result.message_id');
                $details = $this->message($chat, $text->fullText(), $text, $first);

                if (! $details->successful()) {
                    Log::warning("Telegram kanalga e'lon #{$post->id} ma'lumot matni yuborilmadi: " . ($details->json('description') ?? $details->status()));
                }
            }

            return true;
        } catch (Throwable $e) {
            Log::warning("Telegram kanalga e'lon #{$post->id} yuborilmadi: " . $e->getMessage());

            return false;
        }
    }

    /**
     * The text for the first photo's caption.
     */
    public function buildCaption(Post $post): string
    {
        return (new ChannelPostText($post))->caption();
    }

    /**
     * The text with every detail of the listing.
     */
    public function buildFullText(Post $post): string
    {
        return (new ChannelPostText($post))->fullText();
    }

    /**
     * Up to ten of the listing's photos as JPEG bytes in album order: the first 16:9 with the price and
     * brand labels, the others 4:3. Empty when the listing has no readable photo.
     *
     * @return list<string>
     */
    public function preparePhotos(Post $post): array
    {
        $label = (new ChannelPostText($post))->priceLabel();
        $out = [];

        foreach (array_slice($post->allImages(), 0, self::MAX_PHOTOS) as $path) {
            $record = DatabaseImageService::get($path);
            $image = $record && $record->data ? @imagecreatefromstring($record->data) : false;

            if (! $image) {
                continue;
            }

            [$w, $h] = $out ? self::OTHER : self::MAIN;
            $canvas = imagecreatetruecolor($w, $h);
            $this->cover($canvas, $image, $w, $h);

            // Labels only on the first photo that made it into the album
            if (! $out) {
                $this->pill($canvas, $label, 30, $h - 30, false, [217, 98, 43], 1.0);
                $this->pill($canvas, config('services.telegram_channel.brand', 'Faunara'), $w - 30, $h - 30, true, [0, 0, 0], 0.6);
            }

            ob_start();
            imagejpeg($canvas, null, 95);
            if ($bytes = ob_get_clean()) {
                $out[] = $bytes;
            }
        }

        return $out;
    }

    /**
     * Telegram sometimes answers 429 (too many requests) or 5xx, or the connection drops: try again a couple
     * of times, waiting for as long as Telegram asks.
     */
    private function client(): PendingRequest
    {
        return Http::connectTimeout(5)->timeout(30)->retry(
            3,
            fn (int $attempt, \Throwable $e) => $e instanceof RequestException
                ? min(10, (int) ($e->response->json('parameters.retry_after') ?? 2)) * 1000
                : 2000,
            fn (\Throwable $e) => $e instanceof ConnectionException
                || ($e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503, 504], true)),
            throw: false,
        );
    }

    private function url(string $method): string
    {
        return 'https://api.telegram.org/bot' . config('services.telegram_channel.token') . '/' . $method;
    }

    /**
     * The full text as a message with the link buttons, optionally as a reply to the album.
     */
    private function message(string $chat, string $html, ChannelPostText $text, ?int $replyTo = null): Response
    {
        return $this->client()->post($this->url('sendMessage'), array_filter([
            'chat_id' => $chat,
            'text' => $html,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => ['inline_keyboard' => $text->buttons()],
            'reply_parameters' => $replyTo ? ['message_id' => $replyTo, 'allow_sending_without_reply' => true] : null,
        ]));
    }

    /**
     * Fill the whole canvas with the photo, scaled to cover it and cropped in the middle.
     */
    private function cover($canvas, $photo, int $w, int $h): void
    {
        $sw = imagesx($photo);
        $sh = imagesy($photo);
        $scale = max($w / $sw, $h / $sh);
        $cropW = (int) round($w / $scale);
        $cropH = (int) round($h / $scale);

        imagecopyresampled($canvas, $photo, 0, 0, intdiv($sw - $cropW, 2), intdiv($sh - $cropH, 2), $w, $h, $cropW, $cropH);
    }

    /**
     * A rounded label with white bold text. $x is its left edge, or its right edge when $right is set;
     * $bottom is its bottom edge; $opacity below 1 lets the photo show through.
     */
    private function pill($canvas, string $text, int $x, int $bottom, bool $right, array $rgb, float $opacity): void
    {
        $font = resource_path('fonts/Poppins-Bold.ttf');
        $size = 28;
        $hasFont = is_file($font);

        if ($hasFont) {
            $box = imagettfbbox($size, 0, $font, $text);
            $textW = $box[2] - $box[0];
            $textH = $box[1] - $box[7];
        } else {
            $textW = imagefontwidth(5) * strlen($text);
            $textH = imagefontheight(5);
        }

        $padX = 32;
        $height = 70;
        $width = $textW + $padX * 2;
        $x1 = $right ? $x - $width : $x;
        $x2 = $x1 + $width;
        $y1 = $bottom - $height;
        $r = intdiv($height, 2);

        // The shape is drawn solid on a copy of the area, then mixed into the photo at the label's opacity
        $before = imagecreatetruecolor($width + 1, $height + 1);
        imagecopy($before, $canvas, 0, 0, $x1, $y1, $width + 1, $height + 1);

        $color = imagecolorallocate($canvas, ...$rgb);
        imagefilledrectangle($canvas, $x1 + $r, $y1, $x2 - $r, $bottom, $color);
        imagefilledellipse($canvas, $x1 + $r, $y1 + $r, $height, $height, $color);
        imagefilledellipse($canvas, $x2 - $r, $y1 + $r, $height, $height, $color);

        if ($opacity < 1) {
            for ($py = 0; $py <= $height; $py++) {
                for ($px = 0; $px <= $width; $px++) {
                    $new = imagecolorat($canvas, $x1 + $px, $y1 + $py);
                    $old = imagecolorat($before, $px, $py);

                    if ($new === $old) {
                        continue;
                    }

                    $mix = fn (int $shift) => (int) round((($old >> $shift) & 255) * (1 - $opacity) + (($new >> $shift) & 255) * $opacity);
                    imagesetpixel($canvas, $x1 + $px, $y1 + $py, ($mix(16) << 16) | ($mix(8) << 8) | $mix(0));
                }
            }
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        if ($hasFont) {
            imagettftext($canvas, $size, 0, (int) ($x1 + $padX - $box[0]), (int) ($y1 + ($height - $textH) / 2 - $box[7]), $white, $font, $text);

            return;
        }

        imagestring($canvas, 5, $x1 + $padX, $y1 + (int) (($height - $textH) / 2), $text, $white);
    }
}
