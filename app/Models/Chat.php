<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'post_id',
        'buyer_id',
        'seller_id',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function involvesUser(int $userId): bool
    {
        return $this->buyer_id === $userId || $this->seller_id === $userId;
    }

    public function otherParticipant(int $userId): User
    {
        return $this->buyer_id === $userId ? $this->seller : $this->buyer;
    }

    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function isClosed(): bool
    {
        if ($this->closed_at !== null) {
            return true;
        }

        if (! $this->post || in_array($this->post->status, ['sold', 'archived'], true) || $this->post->trashed()) {
            return true;
        }

        if ($this->purchaseRequest && in_array($this->purchaseRequest->status, ['sold', 'rejected'], true)) {
            return true;
        }

        return false;
    }
}
