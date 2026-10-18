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
        'details',
        'description',
        'price',
        'currency',
        'is_negotiable',
        'region',
        'location',
        'status',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
        'moderated_by',
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
            'details' => 'array',
            'quantity' => 'integer',
            'male_quantity' => 'integer',
            'female_quantity' => 'integer',
            'moderated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->content)) {
                $post->content = $post->description ?? '';
            }
        });

        static::updating(function (Post $post) {
            if (empty($post->content)) {
                $post->content = $post->description ?? '';
            }
        });
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

    /**
     * What the form asked for in this listing's category (see config/listing_types.php).
     */
    public function profile(): \App\Support\ListingProfile
    {
        return \App\Support\ListingProfile::forName($this->category?->name);
    }

    /**
     * False for listings that are not sold by sex (fish, beekeeping): they are counted as one group.
     */
    public function usesGender(): bool
    {
        return $this->profile()->usesGender();
    }

    /**
     * The category-specific answers as label/value pairs for the listing page.
     */
    public function detailRows(): array
    {
        return $this->profile()->detailRows($this->details);
    }

    /**
     * "Chilonzor tumani, Toshkent shahri": the exact place the owner typed, then the region they chose.
     */
    public function fullLocation(): string
    {
        return collect([$this->location, $this->region])->filter()->implode(', ');
    }

    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Public listings that are still on sale (fully reserved ones included, they show a "Band" badge).
     */
    public function scopeListed($query)
    {
        return $query->approved()->whereNotIn('status', ['sold', 'archived']);
    }

    /**
     * Order by price converted to so'm, so a 100 USD listing doesn't rank as cheaper than 200 000 UZS.
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $rates = config('zoomarket.currency_rates');

        return $query->orderByRaw(
            "price * (CASE currency WHEN 'USD' THEN ? WHEN 'EUR' THEN ? WHEN 'RUB' THEN ? ELSE 1 END) {$direction}",
            [$rates['USD'], $rates['EUR'], $rates['RUB']]
        );
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

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
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
