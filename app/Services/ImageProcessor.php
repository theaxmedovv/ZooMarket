<?php

namespace App\Services;

/**
 * Turns an uploaded JPEG or PNG into a WebP that fits the given size, plus a tiny JPEG preview (LQIP)
 * as base64. Same result as the image_processor tool (goat-dev), done with GD so nothing has to be compiled.
 *
 * With a $ratio (width / height) the picture is first cut to that shape, so every photo of a listing has
 * the same proportions; the cut is centred sideways and sits a little above the middle vertically, where
 * the animal's head usually is.
 *
 * Images never get enlarged. Anything that is not JPEG/PNG, is too big, or cannot be read comes back as
 * null and the caller keeps the original bytes.
 */
class ImageProcessor
{
    /** Pixels above this are not decoded (memory), like MAX_PIXELS in the C tool. */
    private const MAX_PIXELS = 40_000_000;

    /**
     * @return array{webp: string, lqip: string, width: int, height: int}|null
     */
    public static function process(string $binary, int $maxWidth, int $maxHeight, int $quality = 85, int $lqipWidth = 32, int $lqipQuality = 40, ?float $ratio = null): ?array
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $info = @getimagesizefromstring($binary);
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true) || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::MAX_PIXELS) {
            return null;
        }

        $image = @imagecreatefromstring($binary);
        if (! $image) {
            return null;
        }

        if ($info[2] === IMAGETYPE_JPEG) {
            $image = self::orient($image, $binary);
        }

        // The part of the picture that is kept: all of it, or the biggest piece of the wanted shape
        $width = imagesx($image);
        $height = imagesy($image);
        $cropX = $cropY = 0;

        if ($ratio) {
            if ($width / $height > $ratio) {
                $cropX = intdiv($width - (int) round($height * $ratio), 2);
                $width = (int) round($height * $ratio);
            } else {
                $newHeight = (int) round($width / $ratio);
                $cropY = (int) round(($height - $newHeight) * 0.35);
                $height = $newHeight;
            }
        }

        $scale = min($maxWidth / $width, $maxHeight / $height, 1);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $resized = self::resize($image, $newWidth, $newHeight, true, [$cropX, $cropY, $width, $height]);

        $lqipW = max(1, min($lqipWidth, 1024));
        $lqipH = max(1, min((int) round($lqipW * $newHeight / $newWidth), 1024));
        $small = self::resize($resized, $lqipW, $lqipH, false);

        $webp = self::capture(fn () => imagewebp($resized, null, max(0, min($quality, 100))));
        $lqip = self::capture(fn () => imagejpeg($small, null, max(1, min($lqipQuality, 100))));

        if ($webp === '' || $lqip === '') {
            return null;
        }

        return ['webp' => $webp, 'lqip' => base64_encode($lqip), 'width' => $newWidth, 'height' => $newHeight];
    }

    /**
     * Resample to the new size; $alpha keeps transparency (WebP), otherwise it is flattened onto white (JPEG).
     */
    private static function resize($image, int $w, int $h, bool $alpha = true, ?array $source = null)
    {
        [$sx, $sy, $sw, $sh] = $source ?? [0, 0, imagesx($image), imagesy($image)];

        $canvas = imagecreatetruecolor($w, $h);

        if ($alpha) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        } else {
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagealphablending($canvas, true);
        }

        imagecopyresampled($canvas, $image, 0, 0, $sx, $sy, $w, $h, $sw, $sh);

        return $canvas;
    }

    /**
     * Phones store the rotation in EXIF instead of turning the pixels.
     */
    private static function orient($image, string $binary)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($binary));

        $degrees = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $degrees ? (imagerotate($image, $degrees, 0) ?: $image) : $image;
    }

    private static function capture(callable $encode): string
    {
        ob_start();
        $encode();

        return (string) ob_get_clean();
    }
}
