<?php

namespace App\Http\Controllers;

use App\Models\DatabaseImage;
use App\Services\DatabaseImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    /**
     * Stream an image directly from the MySQL database to the client.
     */
    public function show(Request $request, string $path): Response
    {
        $normalizedPath = DatabaseImageService::normalizePath($path);

        $image = DatabaseImage::where('path', $normalizedPath)->first();

        if (!$image) {
            abort(404, 'Image not found');
        }

        $etag = '"' . md5($image->id . '-' . $image->updated_at) . '"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304, [
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        return response($image->data, 200, [
            'Content-Type' => $image->mime_type ?: 'image/jpeg',
            'Content-Length' => $image->size ?: strlen($image->data),
            'Cache-Control' => 'public, max-age=86400',
            'ETag' => $etag,
        ]);
    }
}
