<?php

namespace App\Support;

use App\Models\ActivityLog;
use Throwable;

/**
 * Records who did what; `telegram:digest` sends the unsent rows to Telegram every few hours.
 * It never throws: losing a log line must not break the page that produced it.
 */
class Activity
{
    public static function record(string $event, ?string $description = null, $user = null): void
    {
        try {
            $user ??= auth()->user();

            ActivityLog::create([
                'event' => $event,
                'who' => $user ? "{$user->name} ({$user->email})" : null,
                'description' => $description !== null ? mb_substr($description, 0, 500) : null,
                'ip' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (Throwable) {
            // logging must never break the request
        }
    }
}
