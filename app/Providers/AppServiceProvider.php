<?php

namespace App\Providers;

use App\Models\Message;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $unread = 0;
            if (auth()->check()) {
                $uid    = auth()->id();
                $unread = Message::whereHas('chat', fn ($q) =>
                    $q->where('buyer_id', $uid)->orWhere('seller_id', $uid)
                )
                ->where('sender_id', '!=', $uid)
                ->whereNull('read_at')
                ->count();
            }
            $view->with('globalUnreadCount', $unread);
        });
    }
}
