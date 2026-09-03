<?php

namespace App\Services;

use App\Models\DatabaseImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DatabaseImageService
{
    /**
     * Store an uploaded image directly into the MySQL database as a LONGBLOB.
     * Does NOT save anything to the local filesystem.
     */
    public static function store(UploadedFile $file, string $folder = 'uploads'): string
    {
        $binary = file_get_contents($file->getRealPath());
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $randomName = Str::random(40) . '.' . strtolower($extension);
        $path = trim($folder, '/') . '/' . $randomName;

        $mimeType = $file->getClientMimeType() ?: 'image/jpeg';
        $size = strlen($binary);
        $originalName = $file->getClientOriginalName();

        DatabaseImage::create([
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'data' => $binary,
        ]);

        return $path;
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
