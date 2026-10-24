<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DatabaseImage;
use App\Models\Post;
use App\Models\User;
use App\Services\TelegramChannelService;
use App\Support\ChannelPostText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Approved listings are posted to the public channel as an album with a caption holding every detail;
 * when that does not fit a caption the details follow as a reply.
 */
class TelegramChannelTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram_channel.token' => '999:CHANNEL', 'services.telegram_channel.chat_id' => '-100777']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => [['message_id' => 77]]])]);
        $this->seller = User::factory()->create(['name' => 'Ali Valiyev']);
        $this->seller->forceFill(['phone' => '+998901234567', 'telegram_username' => '@ali_zoo'])->save();
    }

    private function photo(string $path, int $w, int $h, array $rgb): void
    {
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, ...$rgb));
        ob_start();
        imagejpeg($im);
        DatabaseImage::create(['path' => $path, 'original_name' => $path, 'mime_type' => 'image/jpeg', 'size' => 1, 'data' => ob_get_clean()]);
    }

    private function listing(array $images, array $over = [], string $category = 'Mushuk'): Post
    {
        return Post::create($over + [
            'user_id' => $this->seller->id,
            'category_id' => Category::firstOrCreate(['name' => $category])->id,
            'title' => 'Britan <mushukcha>',
            'content' => 'x',
            'description' => "Sog'lom, emlangan.",
            'breed' => 'British Shorthair',
            'quantity' => 2,
            'gender' => 'mixed',
            'male_quantity' => 1,
            'female_quantity' => 1,
            'age' => '3 oy',
            'color' => 'Kulrang',
            'details' => ['vaccinated' => 'Ha', 'neutered' => "Yo'q", 'pedigree' => 'Bor'],
            'price' => 1500000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Chilonzor',
            'status' => 'active',
            'moderation_status' => 'pending',
            'images' => $images,
            'image' => $images[0] ?? null,
        ]);
    }

    private function decoded(string $html): string
    {
        return html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
    }

    private function mediaItems(Request $r): array
    {
        return json_decode(collect($r->data())->firstWhere('name', 'media')['contents'] ?? '[]', true) ?: [];
    }

    public function test_approving_posts_an_album_with_the_caption_on_the_first_photo(): void
    {
        $this->photo('a.jpg', 900, 600, [200, 50, 50]);
        $this->photo('b.jpg', 400, 900, [50, 200, 50]);
        $this->photo('c.jpg', 600, 600, [50, 50, 200]);
        $post = $this->listing(['a.jpg', 'b.jpg', 'c.jpg']);

        $post->update(['moderation_status' => 'approved']);

        Http::assertSentCount(1);
        Http::assertSent(function (Request $r) use ($post) {
            $items = $this->mediaItems($r);
            $caption = $this->decoded($items[0]['caption'] ?? '');

            return str_contains($r->url(), 'bot999:CHANNEL/sendMediaGroup')
                && count($items) === 3
                && $items[0]['parse_mode'] === 'HTML'
                && ! isset($items[1]['caption'])
                && str_contains($caption, '🐈 <b>Britaniya kalta junli mushukchalari</b>')
                && str_contains($caption, '💰 <b>1 500 000 so\'m</b>')
                && str_contains($caption, '📍 Toshkent shahri, Chilonzor')
                && str_contains($caption, '<b>Jinsi:</b> 1 erkak, 1 urg\'ochi')
                && str_contains($caption, '<b>Emlangan:</b> Ha')
                && str_contains($caption, '<b>Nasabnomasi:</b> Bor')
                && str_contains($caption, '<b>E\'lon nomi:</b> Britan <mushukcha>')
                && str_contains($caption, route('posts.show', $post))
                && str_contains($caption, route('sellers.show', $this->seller))
                && str_contains($caption, '#mushuk #britaniyakaltajunli #toshkent #sotiladi')
                && str_contains($caption, "№ {$post->id}")
                && collect($r->data())->whereIn('name', ['photo0', 'photo1', 'photo2'])->count() === 3;
        });
        $this->assertNotNull($post->fresh()->telegram_posted_at);
    }

    public function test_a_single_photo_goes_as_one_picture(): void
    {
        $this->photo('a.jpg', 900, 600, [200, 50, 50]);

        $this->listing(['a.jpg'])->update(['moderation_status' => 'approved']);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'bot999:CHANNEL/sendPhoto')
            && collect($r->data())->contains(fn ($p) => ($p['name'] ?? '') === 'photo'));
    }

    public function test_only_the_first_ten_photos_are_sent(): void
    {
        $paths = [];
        foreach (range(1, 12) as $i) {
            $this->photo("p$i.jpg", 300, 300, [10 * $i, 10, 10]);
            $paths[] = "p$i.jpg";
        }

        $this->listing($paths)->update(['moderation_status' => 'approved']);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'sendMediaGroup') && count($this->mediaItems($r)) === 10);
    }

    public function test_every_filled_detail_is_in_the_post(): void
    {
        $post = $this->listing([], [
            'details' => ['vaccinated' => 'Ha', 'litter_trained' => 'Ha', 'neutered' => "Yo'q", 'pedigree' => 'Bor'],
            'is_negotiable' => true,
            'description' => 'Juda aqlli mushukcha.',
        ]);
        $post->forceFill(['created_at' => '2026-10-24 10:00:00'])->saveQuietly();

        $text = $this->decoded((new TelegramChannelService())->buildFullText($post->fresh()));

        foreach ([
            '<b>Kategoriya:</b> Mushuk', '<b>Zoti:</b> Britaniya kalta junli', '<b>Yoshi:</b> 3 oy',
            '<b>Jinsi:</b> 1 erkak, 1 urg\'ochi', '<b>Soni:</b> 2 ta', '<b>Rangi:</b> Kulrang',
            '<b>Emlangan:</b> Ha', '<b>Tuvakka o\'rgangan:</b> Ha', '<b>Sterilizatsiya qilingan:</b> Yo\'q', '<b>Nasabnomasi:</b> Bor',
            '💰 <b>1 500 000 so\'m</b> (kelishiladi)', '📝 Juda aqlli mushukcha.',
            '👤 <b>Ali Valiyev</b>', '📞 +998 90 123 45 67', '✈️ @ali_zoo',
            '👉 E\'lonni ko\'rish', '✉️ Sotuvchiga yozish', "№ {$post->id} · 24-oktabr, 2026",
        ] as $expected) {
            $this->assertStringContainsString($expected, $text);
        }
        $this->assertSame(2, substr_count($text, '<blockquote>'));
    }

    public function test_empty_fields_and_empty_blocks_are_left_out(): void
    {
        $post = $this->listing([], [
            'title' => 'Boshqa', 'breed' => null, 'quantity' => 1, 'gender' => 'male', 'male_quantity' => 0, 'female_quantity' => 0,
            'age' => null, 'color' => null, 'details' => null, 'description' => '', 'price' => null, 'is_negotiable' => false,
            'region' => null, 'location' => null,
        ], 'Boshqa');

        $text = $this->decoded((new TelegramChannelService())->buildFullText($post));

        $this->assertStringContainsString('🐾 <b>Boshqa</b>', $text);
        $this->assertStringContainsString('💰 <b>Kelishiladi</b>', $text);
        $this->assertStringNotContainsString('📍', $text);
        $this->assertStringNotContainsString('📝', $text);
        foreach (['Yoshi', 'Rangi', 'Zoti', 'Turi', 'Emlangan'] as $label) {
            $this->assertStringNotContainsString("<b>$label:</b>", $text);
        }
        $this->assertSame(1, substr_count($text, '<blockquote>'));
        $this->assertStringNotContainsString("\n\n\n", $text);
    }

    public function test_price_in_dollars_and_without_a_price(): void
    {
        $service = new TelegramChannelService();

        $usd = $this->listing([], ['price' => 1500, 'currency' => 'USD']);
        $this->assertStringContainsString('💰 <b>$1 500</b>', $service->buildCaption($usd));

        $none = $this->listing([], ['price' => null, 'is_negotiable' => true]);
        $this->assertStringContainsString('💰 <b>Kelishiladi</b>', $service->buildCaption($none));
    }

    public function test_category_details_missing_from_the_template_are_included_under_their_site_names(): void
    {
        $post = $this->listing([], [
            'breed' => 'Rex',
            'details' => ['purpose' => 'Uy hayvoni', 'weight_kg' => 2.5, 'vaccinated' => 'Ha'],
        ], 'Quyon');

        $text = $this->decoded((new TelegramChannelService())->buildFullText($post));

        $this->assertStringContainsString("<b>Og'irligi:</b> 2.5 kg", $text);
        $this->assertStringContainsString("<b>Yo'nalishi:</b> Uy hayvoni", $text);
        $this->assertStringContainsString('<b>Emlangan:</b> Ha', $text);
        $this->assertStringContainsString('🐾 <b>Rex bolalari</b>', $text);
    }

    public function test_users_text_is_html_escaped(): void
    {
        $post = $this->listing([], ['description' => '<script>alert(1)</script> & co', 'breed' => '<i>x</i>']);

        $text = (new TelegramChannelService())->buildFullText($post);

        $this->assertStringNotContainsString('<script>', $text);
        $this->assertStringNotContainsString('<i>', $text);
        $this->assertStringContainsString('&lt;script&gt;', $text);
    }

    public function test_a_long_text_goes_as_a_short_caption_and_a_full_reply(): void
    {
        $this->photo('a.jpg', 900, 600, [200, 50, 50]);
        $this->photo('b.jpg', 900, 600, [50, 200, 50]);
        $long = str_repeat("Juda uzun tavsif matni. ", 80);
        $post = $this->listing(['a.jpg', 'b.jpg'], ['description' => $long]);

        $post->update(['moderation_status' => 'approved']);

        Http::assertSentCount(2);
        Http::assertSent(function (Request $r) {
            if (! str_contains($r->url(), 'sendMediaGroup')) {
                return false;
            }
            $caption = $this->decoded($this->mediaItems($r)[0]['caption']);

            return str_contains($caption, "Batafsil ma'lumot pastda 👇")
                && str_contains($caption, '💰 <b>1 500 000 so\'m</b>')
                && ChannelPostText::visibleLength($caption) <= 1024
                && ! str_contains($caption, 'Juda uzun');
        });
        Http::assertSent(function (Request $r) use ($post) {
            if (! str_contains($r->url(), 'sendMessage')) {
                return false;
            }
            $data = $r->data();
            $buttons = $data['reply_markup']['inline_keyboard'][0] ?? [];

            return $data['reply_parameters']['message_id'] === 77
                && $data['disable_web_page_preview'] === true
                && str_contains($data['text'], 'Juda uzun tavsif')
                && str_contains($data['text'], '<b>Emlangan:</b> Ha')
                && count($buttons) === 2
                && $buttons[0]['url'] === route('posts.show', $post)
                && $buttons[1]['url'] === route('sellers.show', $this->seller);
        });
    }

    public function test_a_description_too_long_for_a_message_is_cut(): void
    {
        $post = $this->listing([], ['description' => str_repeat('Uzun matn. ', 600)]);

        $text = (new TelegramChannelService())->buildFullText($post);

        $this->assertLessThanOrEqual(4096, ChannelPostText::visibleLength($text));
        $this->assertStringContainsString('… davomi saytda', $text);
        $this->assertStringContainsString('№ ' . $post->id, $text);
    }

    public function test_album_photos_are_16_9_first_and_4_3_after_it(): void
    {
        $this->photo('a.jpg', 900, 600, [200, 50, 50]);
        $this->photo('b.jpg', 400, 900, [50, 200, 50]);
        $this->photo('c.jpg', 600, 600, [50, 50, 200]);

        $photos = (new TelegramChannelService())->preparePhotos($this->listing(['a.jpg', 'b.jpg', 'c.jpg']));

        $this->assertSame([[1280, 720], [1024, 768], [1024, 768]], array_map(fn ($b) => array_slice(getimagesizefromstring($b), 0, 2), $photos));

        if ($dir = env('COLLAGE_PREVIEW_DIR')) {
            foreach ($photos as $i => $bytes) {
                file_put_contents("$dir/photo$i.jpg", $bytes);
            }
        }
    }

    public function test_phone_numbers_are_formatted(): void
    {
        $this->assertSame('+998 90 123 45 67', ChannelPostText::phone('+998901234567'));
        $this->assertSame('+998 90 123 45 67', ChannelPostText::phone('90 123-45-67'));
        $this->assertSame('+998 90 123 45 67', ChannelPostText::phone('998901234567'));
        $this->assertNull(ChannelPostText::phone(null));
        $this->assertNull(ChannelPostText::phone(''));
    }

    public function test_a_listing_is_posted_only_once(): void
    {
        $this->photo('a.jpg', 300, 300, [10, 10, 10]);
        $post = $this->listing(['a.jpg']);

        $post->update(['moderation_status' => 'approved']);
        $post->update(['moderation_status' => 'pending']);
        $post->update(['moderation_status' => 'approved']);

        Http::assertSentCount(1);
    }

    public function test_listing_without_photos_goes_as_text(): void
    {
        $post = $this->listing([]);

        $post->update(['moderation_status' => 'approved']);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/sendMessage') && str_contains($r->data()['text'], '<blockquote>'));
    }

    public function test_nothing_is_sent_when_the_channel_is_not_configured(): void
    {
        config(['services.telegram_channel.token' => null]);
        $this->photo('a.jpg', 300, 300, [10, 10, 10]);

        $this->listing(['a.jpg'])->update(['moderation_status' => 'approved']);

        Http::assertNothingSent();
    }

    public function test_telegram_failure_does_not_break_approval(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);
        $this->photo('a.jpg', 300, 300, [10, 10, 10]);
        $post = $this->listing(['a.jpg']);

        $post->update(['moderation_status' => 'approved']);

        $this->assertSame('approved', $post->fresh()->moderation_status);
        $this->assertNull($post->fresh()->telegram_posted_at);
    }
}
