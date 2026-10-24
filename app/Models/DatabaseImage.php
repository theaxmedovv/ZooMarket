<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseImage extends Model
{
    protected $table = 'database_images';

    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'lqip',
        'width',
        'height',
        'data',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];
}
