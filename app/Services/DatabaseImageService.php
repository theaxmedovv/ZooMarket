<?php

namespace App\Services;

use App\Models\DatabaseImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DatabaseImageService
{
    /**
     * Largest width and height an image of a folder is kept at, its WebP quality, and the shape (width / height)
     * every image is cut to, or null to keep the shape it has. Listing photos are all 4:3 so a listing's
     * pictures sit the same way on its card, its page and in the Telegram album.
     */
    private const SIZES = [
        'posts' => [1200, 900, 85, 4 / 3],
        'avatars' => [512, 512, 85],
        'banners' => [1920, 1080, 85],
    ];

    /**
     * Store an uploaded image directly into the MySQL database as a LONGBLOB.
     * Does NOT save anything to the local filesystem.
     *
     * JPEG and PNG are shrunk to the folder's size and saved as WebP together with a tiny base64 preview
     * (see ImageProcessor); anything else (GIF, unreadable files) is kept exactly as uploaded.
     */
    public static function store(UploadedFile $file, string $folder = 'uploads'): string
    {
        $binary = file_get_contents($file->getRealPath());
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $mimeType = $file->getClientMimeType() ?: 'image/jpeg';
        $extra = [];

        [$maxW, $maxH, $quality, $ratio] = (self::SIZES[trim($folder, '/')] ?? [1600, 1600, 85]) + [3 => null];
        if ($processed = ImageProcessor::process($binary, $maxW, $maxH, $quality, ratio: $ratio)) {
            $binary = $processed['webp'];
            $extension = 'webp';
            $mimeType = 'image/webp';
            $extra = ['lqip' => $processed['lqip'], 'width' => $processed['width'], 'height' => $processed['height']];
        }

        $path = trim($folder, '/') . '/' . Str::random(40) . '.' . strtolower($extension);

        DatabaseImage::create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size' => strlen($binary),
            'data' => $binary,
        ] + $extra);

        return $path;
    }

    /**
     * The tiny blurred preview of an image as a data URI, for use as a placeholder while the real one loads.
     */
    public static function lqip(?string $path): ?string
    {
        $lqip = $path ? DatabaseImage::where('path', self::normalizePath($path))->value('lqip') : null;

        return $lqip ? 'data:image/jpeg;base64,' . $lqip : null;
    }

    /**
     * Delete an image from the MySQL database.
     */
    public static function delete(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $normalizedPath = self::normalizePath($path);

        return (bool) DatabaseImage::where('path', $normalizedPath)->delete();
    }

    /**
     * Retrieve an image record from the MySQL database.
     */
    public static function get(?string $path): ?DatabaseImage
    {
        if (empty($path)) {
            return null;
        }

        $normalizedPath = self::normalizePath($path);

        return DatabaseImage::where('path', $normalizedPath)->first();
    }

    /**
     * Generate the URL to display the image directly from the MySQL database.
     */
    public static function url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        $normalizedPath = self::normalizePath($path);

        return route('images.show', ['path' => $normalizedPath]);
    }

    /**
     * Normalize the path by removing leading slashes and optional 'storage/' prefix.
     */
    public static function normalizePath(string $path): string
    {
        $cleaned = ltrim($path, '/');
        if (str_starts_with($cleaned, 'storage/')) {
            $cleaned = substr($cleaned, 8);
        }

        return $cleaned;
    }
}
