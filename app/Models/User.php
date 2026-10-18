<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'telegram_username',
        'avatar',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    /**
     * Saved listings that can still be opened and bought: what the favourites page and its counter show.
     */
    public function favoritePosts(): BelongsToMany
    {
        return $this->likedPosts()->approved()->whereNotIn('posts.status', ['sold', 'archived']);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function buyerChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    public function sellerChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    /**
     * Listings this user has a waiting or approved purchase request for. Read once per HTTP request
     * (kept on the request, so a page full of cards asks the database a single time).
     *
     * @return list<int>
     */
    public function activeRequestedAnimalIds(): array
    {
        return array_keys($this->activeRequestsByAnimal());
    }

    /**
     * The same open requests as [listing id => request id], so a card can offer "cancel" too.
     *
     * @return array<int, int>
     */
    public function activeRequestsByAnimal(): array
    {
        $attributes = request()->attributes;
        $key = 'active_requests_by_animal.' . $this->getKey();

        if (! $attributes->has($key)) {
            $attributes->set($key, $this->purchaseRequests()
                ->whereIn('status', ['pending', 'approved'])
                ->latest('id')
                ->pluck('id', 'animal_id')
                ->all());
        }

        return $attributes->get($key);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? route('images.show', ['path' => $this->avatar]) : null;
    }
}
