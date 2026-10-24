<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Listings past their end date move to the archive
Schedule::command('posts:expire')->daily();

// Who logged in/out, posted, errored: sent to Telegram every few hours (needs `php artisan schedule:work` or a cron)
Schedule::command('telegram:digest')->cron('0 */' . max(1, (int) env('TELEGRAM_DIGEST_HOURS', 3)) . ' * * *');
