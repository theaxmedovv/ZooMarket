<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    /** The code stays valid this long after it is emailed. */
    public const CODE_MINUTES = 10;

    /** Wrong guesses allowed per code. */
    public const MAX_ATTEMPTS = 5;

    /** After the code is accepted, the new password must be set within this time. */
    public const RESET_MINUTES = 15;

    protected $fillable = ['email', 'code_hash', 'attempts', 'expires_at', 'verified_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public static function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, config('app.key'));
    }

    /**
     * Replace any earlier code for this email with a new random 6-digit one and return the plain code.
     */
    public static function issue(string $email): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::updateOrCreate(['email' => $email], [
            'code_hash' => static::hashCode($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::CODE_MINUTES),
            'verified_at' => null,
        ]);

        return $code;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function matches(string $code): bool
    {
        return hash_equals($this->code_hash, static::hashCode($code));
    }

    /**
     * The code was accepted recently enough to set a new password.
     */
    public function canReset(): bool
    {
        return $this->verified_at !== null
            && $this->verified_at->gt(now()->subMinutes(self::RESET_MINUTES));
    }
}
