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
            $pendingRequestsCount = 0;
            if (auth()->check()) {
                $uid    = auth()->id();
                $unread = Message::whereHas('chat', fn ($q) =>
                    $q->where('buyer_id', $uid)->orWhere('seller_id', $uid)
                )
                ->where('sender_id', '!=', $uid)
                ->whereNull('read_at')
                ->count();

                if (auth()->user()->hasRole('seller')) {
                    $pendingRequestsCount = \App\Models\PurchaseRequest::where('status', 'pending')
                        ->whereHas('animal', fn ($q) => $q->where('user_id', $uid))
                        ->count();
                }
            }
            
            try {
                $navCategories = \App\Models\Category::query()->orderBy('name')->get();
            } catch (\Throwable $e) {
                $navCategories = collect();
            }

            $view->with([
                'globalUnreadCount' => $unread,
                'pendingRequestsCount' => $pendingRequestsCount,
                'navCategories' => $navCategories,
            ]);
        });
    }
}
