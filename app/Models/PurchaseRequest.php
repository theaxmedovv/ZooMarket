<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'user_id',
        'animal_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'animal_id');
    }

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }
}
