<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\DatabaseImage;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->syncRoles(['admin']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'eyebrow' => 'Faqat shu hafta',
            'title' => 'Itlar uchun ozuqa',
            'offer' => 'Barchasiga **20% chegirma**',
            'link' => '/?sort=latest',
            'button_text' => 'Xarid qilish',
            'color' => '#212844',
            'sort_order' => 1,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_banner_appears_in_home_hero(): void
    {
        $this->actingAs($this->admin)
            ->post(route('banners.store'), $this->payload())
            ->assertRedirect(route('banners.index'));

        $banner = Banner::firstOrFail();
        $this->assertSame($this->admin->id, $banner->created_by);

        auth()->logout();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Itlar uchun ozuqa')
            ->assertSee('Barchasiga <b>20% chegirma</b>', false)
            ->assertSee('Xarid qilish')
            ->assertSee('id="hero"', false);
    }

    public function test_home_has_no_hero_without_approved_banners(): void
    {
        $owner = $this->user();
        Banner::create($this->payload(['title' => 'Kutilayotgan banner']) + ['created_by' => $owner->id, 'moderation_status' => 'pending']);

        // No built-in slides any more: nothing approved, nothing to show
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="hero"', false)
            ->assertDontSee("Sodiq do'stlar")
            ->assertDontSee('Kutilayotgan banner')
            ->assertSee('Mashhurlar');
    }

    public function test_hidden_banner_is_not_shown_on_home(): void
    {
        $banner = Banner::create($this->payload(['is_active' => false]));

        $this->get(route('home'))->assertOk()->assertDontSee('Itlar uchun ozuqa');

        $this->actingAs($this->admin)->patch(route('banners.toggle', $banner))->assertRedirect();
        $this->assertTrue($banner->fresh()->is_active);

        auth()->logout();
        $this->get(route('home'))->assertSee('Itlar uchun ozuqa');
    }

    public function test_offer_text_is_escaped(): void
    {
        Banner::create($this->payload(['offer' => '<script>alert(1)</script> **ok**']));

        $this->get(route('home'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt; <b>ok</b>', false);
    }

    public function test_unsafe_link_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('banners.store'), $this->payload(['link' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('link');

        $this->assertDatabaseCount('banners', 0);
    }

    public function test_banner_image_is_stored_replaced_and_removed(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), $this->payload([
            'image' => UploadedFile::fake()->image('promo.jpg', 1600, 630),
        ]));

        $banner = Banner::firstOrFail();
        $this->assertStringStartsWith('banners/', $banner->image);
        $this->assertDatabaseHas('database_images', ['path' => $banner->image]);

        $old = $banner->image;
        $this->actingAs($this->admin)->put(route('banners.update', $banner), $this->payload([
            'image' => UploadedFile::fake()->image('new.png', 1600, 630),
        ]))->assertRedirect(route('banners.index'));

        $banner->refresh();
        $this->assertNotSame($old, $banner->image);
        $this->assertDatabaseMissing('database_images', ['path' => $old]);

        $this->actingAs($this->admin)->delete(route('banners.destroy', $banner))->assertRedirect(route('banners.index'));
        $this->assertDatabaseCount('banners', 0);
        $this->assertSame(0, DatabaseImage::count());
    }

    private function user(string $name = 'Ali'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->syncRoles(['user']);

        return $user;
    }

    public function test_any_user_can_add_a_banner_but_it_waits_for_the_admin(): void
    {
        $owner = $this->user();

        $this->actingAs($owner)->get(route('banners.create'))->assertOk();
        $this->actingAs($owner)->post(route('banners.store'), $this->payload(['sort_order' => 5]))
            ->assertRedirect(route('banners.index'))
            ->assertSessionHas('info');

        $banner = Banner::firstOrFail();
        $this->assertSame($owner->id, $banner->created_by);
        $this->assertSame('pending', $banner->moderation_status);
        $this->assertSame(0, $banner->sort_order, 'Only the admin sets the order');

        // Not on the home page yet, for anyone
        auth()->logout();
        $this->get(route('home'))->assertOk()->assertDontSee('Itlar uchun ozuqa');
    }

    public function test_admin_banner_needs_no_review(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), $this->payload());

        $banner = Banner::firstOrFail();
        $this->assertSame('approved', $banner->moderation_status);
        $this->assertSame($this->admin->id, $banner->moderated_by);
    }

    public function test_admin_approval_puts_the_banner_on_the_home_page(): void
    {
        $owner = $this->user();
        $this->actingAs($owner)->post(route('banners.store'), $this->payload());
        $banner = Banner::firstOrFail();

        $this->actingAs($this->admin)->post(route('banners.approve', $banner))->assertRedirect();

        $banner->refresh();
        $this->assertSame('approved', $banner->moderation_status);
        $this->assertSame($this->admin->id, $banner->moderated_by);

        auth()->logout();
        $this->get(route('home'))->assertOk()->assertSee('Itlar uchun ozuqa');
    }

    public function test_rejection_needs_a_reason_and_the_owner_sees_it(): void
    {
        $owner = $this->user();
        $this->actingAs($owner)->post(route('banners.store'), $this->payload());
        $banner = Banner::firstOrFail();

        $this->actingAs($this->admin)->post(route('banners.reject', $banner), ['reason' => ''])->assertSessionHasErrors('reason');
        $this->assertSame('pending', $banner->fresh()->moderation_status);

        $this->actingAs($this->admin)->post(route('banners.reject', $banner), ['reason' => 'Rasm sifatsiz'])->assertRedirect();
        $this->assertSame('rejected', $banner->fresh()->moderation_status);

        $this->actingAs($owner)->get(route('banners.index'))->assertOk()->assertSee('Rasm sifatsiz')->assertSee('Rad etilgan');

        auth()->logout();
        $this->get(route('home'))->assertDontSee('Itlar uchun ozuqa');
    }

    public function test_editing_an_approved_banner_sends_it_back_for_review(): void
    {
        $owner = $this->user();
        $banner = Banner::create($this->payload() + ['created_by' => $owner->id]);
        $this->assertSame('approved', $banner->fresh()->moderation_status);

        $this->actingAs($owner)->put(route('banners.update', $banner), $this->payload(['title' => 'Yangi sarlavha']))
            ->assertRedirect(route('banners.index'));

        $banner->refresh();
        $this->assertSame('pending', $banner->moderation_status);
        $this->assertNull($banner->moderated_by);

        auth()->logout();
        $this->get(route('home'))->assertDontSee('Yangi sarlavha');
    }

    public function test_admin_edit_keeps_the_banner_approved(): void
    {
        $owner = $this->user();
        $banner = Banner::create($this->payload() + ['created_by' => $owner->id]);

        $this->actingAs($this->admin)->put(route('banners.update', $banner), $this->payload(['title' => 'Admin tahriri', 'sort_order' => 3]));

        $banner->refresh();
        $this->assertSame('approved', $banner->moderation_status);
        $this->assertSame(3, $banner->sort_order);
    }

    public function test_user_only_sees_and_manages_their_own_banners(): void
    {
        $alice = $this->user('Alice');
        $bob = $this->user('Bob');
        $alices = Banner::create($this->payload(['title' => 'Alisaning banneri']) + ['created_by' => $alice->id]);
        Banner::create($this->payload(['title' => 'Bobning banneri']) + ['created_by' => $bob->id]);

        $this->actingAs($alice)->get(route('banners.index'))
            ->assertOk()
            ->assertSee('Alisaning banneri')
            ->assertDontSee('Bobning banneri');

        $bobs = Banner::where('created_by', $bob->id)->first();
        $this->actingAs($alice)->get(route('banners.edit', $bobs))->assertForbidden();
        $this->actingAs($alice)->put(route('banners.update', $bobs), $this->payload())->assertForbidden();
        $this->actingAs($alice)->patch(route('banners.toggle', $bobs))->assertForbidden();
        $this->actingAs($alice)->delete(route('banners.destroy', $bobs))->assertForbidden();
        $this->assertDatabaseCount('banners', 2);

        $this->actingAs($alice)->delete(route('banners.destroy', $alices))->assertRedirect(route('banners.index'));
        $this->assertDatabaseCount('banners', 1);
    }

    public function test_only_the_admin_can_approve_or_reject(): void
    {
        $owner = $this->user();
        $banner = Banner::create($this->payload() + ['created_by' => $owner->id, 'moderation_status' => 'pending']);

        $this->actingAs($owner)->post(route('banners.approve', $banner))->assertForbidden();
        $this->actingAs($owner)->post(route('banners.reject', $banner), ['reason' => 'Mening bannerim'])->assertForbidden();
        auth()->logout();
        $this->post(route('banners.approve', $banner))->assertRedirect(route('login'));

        $this->assertSame('pending', $banner->fresh()->moderation_status);
    }

    public function test_admin_sees_every_banner_with_review_controls(): void
    {
        $owner = $this->user('Valijon');
        Banner::create($this->payload(['title' => 'Kutilayotgan banner']) + ['created_by' => $owner->id, 'moderation_status' => 'pending']);

        $this->actingAs($this->admin)->get(route('banners.index'))
            ->assertOk()
            ->assertSee('Kutilayotgan banner')
            ->assertSee('Valijon')
            ->assertSee('Tasdiqlash')
            ->assertSee('Rad etish');

        $this->actingAs($this->admin)->get(route('banners.index', ['status' => 'approved']))->assertDontSee('Kutilayotgan banner');
    }

    public function test_admin_header_shows_pending_banner_count(): void
    {
        $owner = $this->user();
        Banner::create($this->payload() + ['created_by' => $owner->id, 'moderation_status' => 'pending']);
        Banner::create($this->payload() + ['created_by' => $owner->id, 'moderation_status' => 'pending']);

        $html = $this->actingAs($this->admin)->get(route('banners.index'))->getContent();
        $this->assertMatchesRegularExpression('#Bannerlar</span>\s*<em[^>]*>2</em>#', $html);
    }

    public function test_banner_can_be_just_a_picture(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), [
            'color' => '#212844',
            'link' => '/?category_id=1',
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('poster.jpg', 1600, 533),
        ])->assertSessionHasNoErrors()->assertRedirect(route('banners.index'));

        $banner = Banner::firstOrFail();
        $this->assertNull($banner->title);
        $this->assertTrue($banner->isImageOnly());

        auth()->logout();
        $html = $this->get(route('home'))->assertOk()->assertSee('id="hero"', false)->getContent();

        // Shown as it is: the picture, one link over the whole banner, no heading, no shade, no button
        $this->assertStringContainsString($banner->imageUrl(), $html);
        $this->assertStringContainsString('aria-label="Batafsil"', $html);
        $hero = substr($html, strpos($html, 'id="hero"'), strpos($html, '</section>', strpos($html, 'id="hero"')) - strpos($html, 'id="hero"'));
        $this->assertStringNotContainsString('<h2', $hero);
        $this->assertStringNotContainsString('from-black/75', $hero);
    }

    public function test_banner_without_text_and_picture_is_refused(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), ['color' => '#212844', 'title' => '', 'offer' => 'Faqat taklif'])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('banners', 0);
    }

    public function test_picture_banner_cannot_lose_its_picture_while_having_no_text(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), ['color' => '#212844', 'image' => UploadedFile::fake()->image('p.jpg', 1600, 533)]);
        $banner = Banner::firstOrFail();

        $this->actingAs($this->admin)->put(route('banners.update', $banner), ['color' => '#212844', 'remove_image' => 1])->assertSessionHasErrors('title');
        $this->assertNotNull($banner->fresh()->image);

        // With a title the picture may go
        $this->actingAs($this->admin)->put(route('banners.update', $banner), ['color' => '#212844', 'title' => 'Endi matnli', 'remove_image' => 1])->assertSessionHasNoErrors();
        $this->assertNull($banner->fresh()->image);
    }

    public function test_text_banners_still_get_a_heading_and_picture_ones_with_text_keep_the_shade(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), $this->payload([
            'image' => UploadedFile::fake()->image('p.jpg', 1600, 533),
        ]));

        auth()->logout();
        $hero = $this->get(route('home'))->getContent();
        $hero = substr($hero, strpos($hero, 'id="hero"'), strpos($hero, '</section>', strpos($hero, 'id="hero"')) - strpos($hero, 'id="hero"'));

        $this->assertStringContainsString('<h2', $hero);
        $this->assertStringContainsString('from-black/75', $hero);
    }

    public function test_picture_only_banner_is_listed_with_a_name_and_can_be_reviewed(): void
    {
        $this->actingAs($this->admin)->post(route('banners.store'), ['color' => '#212844', 'image' => UploadedFile::fake()->image('p.jpg', 1600, 533)])
            ->assertSessionHasNoErrors();
        $banner = Banner::firstOrFail();

        $this->actingAs($this->admin)->get(route('banners.index'))->assertOk()->assertSee('Rasmli banner #' . $banner->id)->assertSee('Faqat rasm');

        // The review message names it too
        $banner->update(['moderation_status' => 'pending']);
        $this->actingAs($this->admin)->post(route('banners.approve', $banner))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Rasmli banner #' . $banner->id));
    }

    public function test_only_the_admin_can_place_a_picture_only_banner(): void
    {
        $owner = $this->user();
        $picture = UploadedFile::fake()->image('p.jpg', 1600, 533);

        // A user must write a title, whatever the picture
        $this->actingAs($owner)->post(route('banners.store'), ['color' => '#212844', 'image' => $picture])->assertSessionHasErrors('title');
        $this->assertDatabaseCount('banners', 0);

        // With a title and a picture it is fine
        $this->actingAs($owner)->post(route('banners.store'), ['color' => '#212844', 'title' => 'Mening bannerim', 'image' => UploadedFile::fake()->image('q.jpg', 1600, 533)])
            ->assertSessionHasNoErrors();
        $banner = Banner::firstOrFail();
        $this->assertFalse($banner->isImageOnly());

        // Editing cannot blank the title either
        $this->actingAs($owner)->put(route('banners.update', $banner), ['color' => '#212844', 'title' => ''])->assertSessionHasErrors('title');
        $this->assertSame('Mening bannerim', $banner->fresh()->title);

        // The form says so: title is marked required for users, optional for the admin
        $this->actingAs($owner)->get(route('banners.create'))->assertOk()->assertDontSee('faqat rasm qo&#039;ysangiz', false);
        $this->actingAs($this->admin)->get(route('banners.create'))->assertOk()->assertSee("faqat rasm qo'ysangiz", false);
    }

    public function test_guests_cannot_reach_banner_pages(): void
    {
        $this->get(route('banners.index'))->assertRedirect(route('login'));
        $this->post(route('banners.store'), $this->payload())->assertRedirect(route('login'));
        $this->assertDatabaseCount('banners', 0);
    }
}
