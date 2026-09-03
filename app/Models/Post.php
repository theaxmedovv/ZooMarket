<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'category_id',
        'breed',
        'gender',
        'quantity',
        'male_quantity',
        'female_quantity',
        'age',
        'color',
        'description',
        'price',
        'currency',
        'is_negotiable',
        'location',
        'status',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
        'content',
        'image',
        'images',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'images' => 'array',
            'quantity' => 'integer',
            'male_quantity' => 'integer',
            'female_quantity' => 'integer',
            'moderated_at' => 'datetime',
        ];
    }

    public function isApproved(): bool
    {
        return $this->moderation_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->moderation_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->moderation_status === 'rejected';
    }

    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    public function availableMaleCount(): int
    {
        return max(0, (int) $this->male_quantity);
    }

    public function availableFemaleCount(): int
    {
        return max(0, (int) $this->female_quantity);
    }

    public function totalAvailableCount(): int
    {
        return $this->availableMaleCount() + $this->availableFemaleCount();
    }

    public function hasMaleAvailable(): bool
    {
        return $this->availableMaleCount() > 0;
    }

    public function hasFemaleAvailable(): bool
    {
        return $this->availableFemaleCount() > 0;
    }

    public function isArchived(): bool
    {
        return in_array($this->status, ['sold', 'archived'], true) || $this->trashed();
    }

    public function isSoldOut(): bool
    {
        return $this->isArchived() || $this->totalAvailableCount() <= 0;
    }

    public function allImages(): array
    {
        $list = array_filter((array) ($this->images ?? []));
        if (empty($list) && $this->image) {
            return [$this->image];
        }
        return array_values($list);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? route('images.show', ['path' => $this->image]) : null;
    }

    public function allImageUrls(): array
    {
        return array_map(function ($img) {
            return route('images.show', ['path' => $img]);
        }, $this->allImages());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'animal_id');
    }
}
