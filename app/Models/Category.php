<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name'];

    public static function emojiFor(?string $name): string
    {
        $n = mb_strtolower(trim((string) $name));

        return match (true) {
            $n === 'it' || str_contains($n, 'dog') || str_starts_with($n, 'it ') => '🐶',
            str_contains($n, 'mushuk') || str_contains($n, 'cat') => '🐱',
            str_contains($n, 'qush') || str_contains($n, 'bird') => '🦜',
            str_contains($n, 'quyon') || str_contains($n, 'rabbit') => '🐰',
            str_contains($n, 'baliq') || str_contains($n, 'fish') => '🐠',
            str_contains($n, 'kemiruvchi') || str_contains($n, 'hamster') => '🐹',
            str_contains($n, 'sudral') || str_contains($n, 'reptile') => '🦎',
            $n === 'ot' || str_contains($n, 'horse') => '🐴',
            default => '🐾',
        };
    }

    public function getEmojiAttribute(): string
    {
        return static::emojiFor($this->name);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
