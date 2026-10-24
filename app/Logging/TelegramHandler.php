<?php

namespace App\Logging;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * Sends log records to a Telegram chat or channel through the Bot API.
 *
 * It must never make things worse: a missing token turns it off, an unreachable Telegram is swallowed
 * (logging the failure would log again), and the same message is only sent once a minute so one
 * broken page cannot flood the channel.
 */
class TelegramHandler extends AbstractProcessingHandler
{
    /** Telegram refuses messages longer than 4096 characters. */
    private const LIMIT = 4000;

    public function __construct(
        private readonly ?string $token,
        private readonly ?string $chatId,
        int|string|Level $level = Level::Error,
        private readonly int $throttleSeconds = 60,
    ) {
        parent::__construct($level);
    }

    protected function write(LogRecord $record): void
    {
        // Also goes into the periodic digest (telegram:digest), whether or not it is sent right away
        \App\Support\Activity::record('error', mb_substr($record->message, 0, 300));

        if (! $this->token || ! $this->chatId) {
            return;
        }

        try {
            if ($this->isRepeat($record)) {
                return;
            }

            Http::asJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                    'chat_id' => $this->chatId,
                    'text' => $this->format($record),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);
        } catch (Throwable) {
            // Telegram down or misconfigured: nothing sensible to do, and logging it here would recurse
        }
    }

    /**
     * The same error (same text, same place) is sent at most once per throttle window.
     */
    private function isRepeat(LogRecord $record): bool
    {
        if ($this->throttleSeconds <= 0) {
            return false;
        }

        $exception = $record->context['exception'] ?? null;
        $where = $exception instanceof Throwable ? $exception->getFile() . ':' . $exception->getLine() : '';
        $key = 'telegram-log:' . md5($record->level->getName() . $record->message . $where);

        try {
            return ! Cache::add($key, 1, $this->throttleSeconds);
        } catch (Throwable) {
            return false; // cache unavailable (maybe that is the very error): better a duplicate than silence
        }
    }

    private function format(LogRecord $record): string
    {
        $icon = match (true) {
            $record->level->value >= Level::Critical->value => '🔥',
            $record->level->value >= Level::Error->value => '🚨',
            $record->level->value >= Level::Warning->value => '⚠️',
            default => 'ℹ️',
        };

        $lines = [
            sprintf('%s <b>%s</b> · %s · %s', $icon, e($record->level->getName()), e((string) config('app.name')), e((string) config('app.env'))),
            '<b>' . e(mb_substr($record->message, 0, 600)) . '</b>',
        ];

        $exception = $record->context['exception'] ?? null;
        if ($exception instanceof Throwable) {
            $lines[] = e(get_class($exception)) . ' — ' . e(mb_substr($exception->getMessage(), 0, 400));
            $lines[] = '<code>' . e($this->relative($exception->getFile()) . ':' . $exception->getLine()) . '</code>';
        }

        if (app()->bound('request') && ! app()->runningInConsole()) {
            $request = request();
            $lines[] = e($request->method() . ' ' . $request->fullUrl());
            if ($request->user()) {
                $lines[] = 'Foydalanuvchi: #' . $request->user()->getAuthIdentifier();
            }
        } elseif (app()->runningInConsole()) {
            $lines[] = 'Konsol: ' . e(implode(' ', array_slice($_SERVER['argv'] ?? [], 0, 4)));
        }

        $lines[] = e($record->datetime->format('Y-m-d H:i:s'));

        if ($exception instanceof Throwable) {
            $trace = collect(explode("\n", $exception->getTraceAsString()))
                ->reject(fn ($line) => str_contains($line, '/vendor/') || str_contains($line, '\\vendor\\'))
                ->take(8)
                ->map(fn ($line) => $this->relative($line))
                ->implode("\n");

            if ($trace !== '') {
                $lines[] = "<pre>" . e($trace) . "</pre>";
            }
        }

        $text = implode("\n", $lines);

        // Cut on a line boundary so an HTML tag is never left open
        while (mb_strlen($text) > self::LIMIT && count($lines) > 3) {
            array_pop($lines);
            $text = implode("\n", $lines);
        }

        return mb_substr($text, 0, self::LIMIT);
    }

    private function relative(string $path): string
    {
        return str_replace([base_path() . DIRECTORY_SEPARATOR, base_path() . '/'], '', $path);
    }
}
