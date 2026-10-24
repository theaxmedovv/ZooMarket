<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\TelegramChannelService;
use Illuminate\Console\Command;

class TelegramChannelPost extends Command
{
    protected $signature = 'telegram:channel-post {post : E\'lon ID raqami}';

    protected $description = "E'lonni Telegram kanalga qo'lda yuboradi (sinash yoki qayta yuborish uchun)";

    public function handle(TelegramChannelService $channel): int
    {
        if (! TelegramChannelService::enabled()) {
            $this->error(".env da TELEGRAM_CHANNEL_BOT_TOKEN va TELEGRAM_CHANNEL_ID to'ldirilmagan.");

            return self::FAILURE;
        }

        $post = Post::find($this->argument('post'));

        if (! $post) {
            $this->error("Bunday e'lon yo'q.");

            return self::FAILURE;
        }

        if (! $channel->publish($post)) {
            $this->error("Yuborilmadi. Sababi storage/logs/laravel.log da (token, kanal ID yoki botning adminligini tekshiring).");

            return self::FAILURE;
        }

        $this->info("E'lon #{$post->id} kanalga yuborildi.");

        return self::SUCCESS;
    }
}
