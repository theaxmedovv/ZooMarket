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
        'gender',
        'quantity',
        'male_quantity',
        'female_quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'male_quantity' => 'integer',
            'female_quantity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseRequest $request) {
            $male = (int) $request->male_quantity;
            $female = (int) $request->female_quantity;

            // Legacy single-gender requests: derive the split from gender + quantity
            if ($male + $female === 0) {
                $qty = max(1, (int) $request->quantity);
                if ($request->gender === 'female') {
                    $female = $qty;
                } else {
                    $male = $qty;
                }
            }

            $request->male_quantity = $male;
            $request->female_quantity = $female;
            $request->quantity = $male + $female;
            $request->gender = match (true) {
                $male > 0 && $female > 0 => 'mixed',
                $female > 0 => 'female',
                default => 'male',
            };
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'animal_id')->withTrashed();
    }

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }
}
