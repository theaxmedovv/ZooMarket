<?php

namespace App\Providers;

use App\Models\Message;
use App\Support\Activity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Activity digest sent to Telegram (php artisan telegram:digest)
        Event::listen(Login::class, fn (Login $e) => Activity::record('login', null, $e->user));
        Event::listen(Logout::class, fn (Logout $e) => $e->user && Activity::record('logout', null, $e->user));
        Event::listen(Failed::class, fn (Failed $e) => Activity::record('login_failed', (string) ($e->credentials['email'] ?? '')));

        // Site-styled Tailwind pagination (resources/views/pagination/default.blade.php).
        Paginator::defaultView('pagination.default');

        // Relative dates ("3 kun avval") in Uzbek Latin, matching the UI language.
        \Illuminate\Support\Carbon::setLocale('uz_Latn');

        // Header/footer partials are shared by the layout and the standalone home page.
        View::composer(['partials.site-header', 'partials.site-footer'], function ($view) {
            // once(): both partials render on every page; compute the data a single time per request.
            $view->with(once(function () {
                $pendingRequestsCount = 0;
                $pendingModerationCount = 0;
                $pendingBannersCount = 0;
                $openReportsCount = 0;
                $unread = 0;
                $favoritesCount = 0;

                if (auth()->check()) {
                    $uid = auth()->id();
                    $unread = Message::whereHas('chat', fn ($q) =>
                        $q->where('buyer_id', $uid)->orWhere('seller_id', $uid)
                    )
                    ->where('sender_id', '!=', $uid)
                    ->whereNull('read_at')
                    ->count();

                    if (auth()->user()->hasRole('user')) {
                        $favoritesCount = auth()->user()->favoritePosts()->count();
                        $pendingRequestsCount = \App\Models\PurchaseRequest::where('status', 'pending')
                            ->whereHas('animal', fn ($q) => $q->where('user_id', $uid))
                            ->count();
                    }

                    if (auth()->user()->hasRole('admin')) {
                        $pendingModerationCount = \App\Models\Post::where('moderation_status', 'pending')->count();
                        $pendingBannersCount = \App\Models\Banner::where('moderation_status', 'pending')->count();
                        $openReportsCount = \App\Models\Report::where('status', 'open')->count();
                    }
                }

                // Katalog menu: per category, the most common breeds and cities among listings on sale
                try {
                    $catalog = \App\Support\Catalog::stats();
                } catch (\Throwable $e) {
                    $catalog = [];
                }

                try {
                    $navCategories = \App\Models\Category::ordered()->get();
                } catch (\Throwable $e) {
                    $navCategories = collect();
                }

                return [
                    'globalUnreadCount' => $unread,
                    'pendingRequestsCount' => $pendingRequestsCount,
                    'pendingModerationCount' => $pendingModerationCount,
                    'pendingBannersCount' => $pendingBannersCount,
                    'openReportsCount' => $openReportsCount,
                    'catalogData' => $catalog,
                    'navCategories' => $navCategories,
                    'favoritesCount' => $favoritesCount,
                ];
            }));
        });
    }
}
