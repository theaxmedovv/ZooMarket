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
        // Header/footer partials are shared by the layout and the standalone home page.
        View::composer(['partials.site-header', 'partials.site-footer'], function ($view) {
            // once(): both partials render on every page; compute the data a single time per request.
            $view->with(once(function () {
                $pendingRequestsCount = 0;
                $unread = 0;

                if (auth()->check()) {
                    $uid = auth()->id();
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

                return [
                    'globalUnreadCount' => $unread,
                    'pendingRequestsCount' => $pendingRequestsCount,
                    'navCategories' => $navCategories,
                ];
            }));
        });
    }
}
