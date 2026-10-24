<?php

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;

/**
 * The "telegram" log channel (config/logging.php): errors go to the Telegram chat from .env.
 */
class CreateTelegramLogger
{
    public function __invoke(array $config): Logger
    {
        return new Logger('telegram', [
            new TelegramHandler(
                $config['token'] ?? null,
                $config['chat_id'] ?? null,
                Level::fromName(ucfirst(strtolower((string) ($config['level'] ?? 'error')))),
                (int) ($config['throttle'] ?? 60),
            ),
        ]);
    }
}
