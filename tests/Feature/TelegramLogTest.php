<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

/**
 * The "telegram" log channel: errors reach the chat, quietly and without flooding it.
 */
class TelegramLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'logging.channels.telegram.token' => '123:TEST',
            'logging.channels.telegram.chat_id' => '-100555',
            'logging.channels.telegram.level' => 'error',
            'logging.channels.telegram.throttle' => 60,
        ]);
        Log::forgetChannel('telegram');
        Cache::flush();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    }

    public function test_an_error_is_sent_to_the_chat(): void
    {
        Log::channel('telegram')->error('Baza ishlamadi');

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.telegram.org/bot123:TEST/sendMessage'
                && $request['chat_id'] === '-100555'
                && $request['parse_mode'] === 'HTML'
                && str_contains($request['text'], 'Baza ishlamadi')
                && str_contains($request['text'], 'ERROR');
        });
    }

    public function test_an_exception_adds_its_class_message_and_place(): void
    {
        Log::channel('telegram')->error('Sahifa yiqildi', ['exception' => new RuntimeException('Jadval topilmadi')]);

        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'RuntimeException')
            && str_contains($request['text'], 'Jadval topilmadi')
            && str_contains($request['text'], 'TelegramLogTest.php'));
    }

    public function test_html_in_the_message_is_escaped(): void
    {
        Log::channel('telegram')->error('<script>alert(1)</script> & <b>x');

        Http::assertSent(fn (Request $request) => str_contains($request['text'], '&lt;script&gt;')
            && ! str_contains($request['text'], '<script>'));
    }

    public function test_messages_below_the_level_are_not_sent(): void
    {
        Log::channel('telegram')->info('Oddiy xabar');
        Log::channel('telegram')->warning('Ogohlantirish');

        Http::assertNothingSent();
    }

    public function test_the_level_comes_from_config(): void
    {
        config(['logging.channels.telegram.level' => 'warning']);
        Log::forgetChannel('telegram');

        Log::channel('telegram')->warning('Ogohlantirish');

        Http::assertSentCount(1);
    }

    public function test_the_same_error_is_sent_once_per_window_but_other_errors_still_go(): void
    {
        Log::channel('telegram')->error('Bir xil xato');
        Log::channel('telegram')->error('Bir xil xato');
        Log::channel('telegram')->error('Boshqa xato');

        Http::assertSentCount(2);
    }

    public function test_throttling_can_be_turned_off(): void
    {
        config(['logging.channels.telegram.throttle' => 0]);
        Log::forgetChannel('telegram');

        Log::channel('telegram')->error('Bir xil xato');
        Log::channel('telegram')->error('Bir xil xato');

        Http::assertSentCount(2);
    }

    public function test_without_a_token_or_chat_nothing_is_sent(): void
    {
        config(['logging.channels.telegram.token' => null]);
        Log::forgetChannel('telegram');
        Log::channel('telegram')->error('Token yo\'q');

        config(['logging.channels.telegram.token' => '123:TEST', 'logging.channels.telegram.chat_id' => '']);
        Log::forgetChannel('telegram');
        Log::channel('telegram')->error('Chat yo\'q');

        Http::assertNothingSent();
    }

    public function test_a_telegram_outage_never_breaks_the_request(): void
    {
        Http::fake(['api.telegram.org/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout')]);

        Log::channel('telegram')->error('Telegram ishlamayapti');

        $this->assertTrue(true); // reaching this line is the assertion: nothing was thrown
    }

    public function test_a_very_long_message_fits_telegrams_limit(): void
    {
        Log::channel('telegram')->error(str_repeat('x', 10000), ['exception' => new RuntimeException(str_repeat('y', 10000))]);

        Http::assertSent(fn (Request $request) => mb_strlen($request['text']) <= 4096);
    }

    public function test_it_works_through_the_stack_the_app_logs_to(): void
    {
        config(['logging.channels.stack.channels' => ['telegram'], 'logging.default' => 'stack']);
        Log::forgetChannel('stack');
        Log::forgetChannel('telegram');

        Log::error('Umumiy log orqali');

        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'Umumiy log orqali'));
    }
}
