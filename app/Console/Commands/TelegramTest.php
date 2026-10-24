<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramTest extends Command
{
    protected $signature = 'telegram:test';

    protected $description = "Telegram log sozlamasini tekshiradi: kanalga sinov xabarini yuboradi va Telegram javobini ko'rsatadi";

    public function handle(): int
    {
        $config = config('logging.channels.telegram');
        $token = $config['token'] ?? null;
        $chat = $config['chat_id'] ?? null;

        if (! $token || ! $chat) {
            $this->error('.env da TELEGRAM_BOT_TOKEN va TELEGRAM_CHAT_ID to\'ldirilmagan.');

            return self::FAILURE;
        }

        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chat,
            'text' => '✅ ' . config('app.name') . ' (' . config('app.env') . "): log kanali ishlayapti.",
        ]);

        if ($response->successful()) {
            $this->info('Xabar yuborildi. Telegramni tekshiring.');
        } else {
            $this->error('Telegram rad etdi: ' . ($response->json('description') ?? $response->status()));
            $this->line("Odatiy sabablar: token noto'g'ri; bot kanalga admin qilib qo'shilmagan; chat id noto'g'ri (kanal uchun -100 bilan boshlanadi).");

            return self::FAILURE;
        }

        if (! in_array('telegram', (array) config('logging.channels.stack.channels'), true) && config('logging.default') !== 'telegram') {
            $this->warn('Diqqat: xatolar hali Telegramga yuborilmaydi. .env da LOG_STACK=single,telegram qiling.');
        }

        return self::SUCCESS;
    }
}
