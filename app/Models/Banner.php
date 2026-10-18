<?php

namespace App\Models;

use App\Services\DatabaseImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A home page hero slide. Any user can place one; it is shown only after the main admin approves it.
 */
class Banner extends Model
{
    /** Background colours the admin can pick from (value => label). */
    public const COLORS = [
        '#212844' => "To'q ko'k",
        '#1f3a5f' => 'Dengiz',
        '#2d2350' => 'Binafsha',
        '#0073a8' => 'Moviy',
        '#9a3412' => "To'q sariq",
        '#1e5631' => 'Yashil',
    ];

    protected $fillable = [
        'eyebrow',
        'title',
        'offer',
        'link',
        'button_text',
        'image',
        'emoji',
        'color',
        'sort_order',
        'is_active',
        'created_by',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
        'moderated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'moderated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * What the home page shows: switched on by the owner and approved by the admin.
     */
    public function scopeShown(Builder $query): Builder
    {
        return $query->active()->approved();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->latest('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? DatabaseImageService::url($this->image) : null;
    }

    /**
     * The offer line, escaped, with **text** turned into the highlighted (yellow) part.
     */
    public function offerHtml(): string
    {
        return preg_replace('/\*\*(.+?)\*\*/u', '<b>$1</b>', e((string) $this->offer));
    }
}
