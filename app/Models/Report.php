<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'post_id', 'reported_user_id', 'reason', 'comment', 'status', 'handled_by', 'handled_at'];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function reasonLabel(): string
    {
        return config('zoomarket.report_reasons.' . $this->reason, $this->reason);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
