<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramDigest extends Command
{
    protected $signature = 'telegram:digest';

    protected $description = "Oxirgi xabardan beri bo'lgan harakatlarni (kirish, chiqish, e'lon, xatolar) Telegramga yuboradi";

    private const LABELS = [
        'login' => '🟢 Kirdi',
        'logout' => '⚪ Chiqdi',
        'register' => "🆕 Ro'yxatdan o'tdi",
        'login_failed' => '🔴 Kirish muvaffaqiyatsiz',
        'post_created' => "📢 E'lon qo'ydi",
        'request_sent' => "🛒 So'rov yubordi",
        'report_sent' => '🚩 Shikoyat qildi',
        'error' => '🚨 Xato',
    ];

    public function handle(): int
    {
        $token = config('logging.channels.telegram.token');
        $chat = config('logging.channels.telegram.chat_id');

        if (! $token || ! $chat) {
            $this->error(".env da TELEGRAM_BOT_TOKEN va TELEGRAM_CHAT_ID to'ldirilmagan.");

            return self::FAILURE;
        }

        $logs = ActivityLog::whereNull('sent_at')->orderBy('id')->limit(500)->get();

        if ($logs->isEmpty()) {
            $this->info("Yangi harakat yo'q.");

            return self::SUCCESS;
        }

        $summary = $logs->countBy('event')
            ->map(fn ($n, $event) => (self::LABELS[$event] ?? $event) . ": {$n}")
            ->implode("\n");

        $lines = $logs->map(fn ($l) => sprintf(
            '%s %s%s%s',
            $l->created_at->format('H:i'),
            self::LABELS[$l->event] ?? $l->event,
            $l->who ? ' — ' . $l->who : '',
            $l->description ? ' — ' . $l->description : '',
        ));

        $header = '📊 ' . config('app.name') . ' · ' . $logs->first()->created_at->format('d.m H:i')
            . ' – ' . $logs->last()->created_at->format('H:i') . "\n\n" . $summary . "\n";

        foreach ($this->chunks($header, $lines->all()) as $text) {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chat,
                'text' => $text,
                'disable_web_page_preview' => true,
            ]);

            if (! $response->successful()) {
                $this->error('Telegram rad etdi: ' . ($response->json('description') ?? $response->status()));

                return self::FAILURE; // rows stay unsent and go out next time
            }
        }

        ActivityLog::whereIn('id', $logs->pluck('id'))->update(['sent_at' => now()]);
        $this->info($logs->count() . ' ta harakat yuborildi.');

        return self::SUCCESS;
    }

    /**
     * Telegram messages are limited to 4096 characters, so long digests are split on line boundaries.
     *
     * @param  string[]  $lines
     * @return string[]
     */
    private function chunks(string $header, array $lines): array
    {
        $chunks = [];
        $current = $header;

        foreach ($lines as $line) {
            $line = mb_substr($line, 0, 500);
            if (mb_strlen($current) + mb_strlen($line) + 1 > 3900) {
                $chunks[] = $current;
                $current = '';
            }
            $current .= "\n" . $line;
        }

        $chunks[] = $current;

        return $chunks;
    }
}
