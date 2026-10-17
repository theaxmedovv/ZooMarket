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
            // Built-in slides still rotate after the admin's ones
            ->assertSee("Sodiq do'stlar.");
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

    public function test_only_admin_can_manage_banners(): void
    {
        $seller = User::factory()->create();
        $seller->syncRoles(['user']);
        $buyer = User::factory()->create();
        $buyer->syncRoles(['user']);

        foreach ([$seller, $buyer] as $user) {
            $this->actingAs($user)->get(route('banners.index'))->assertForbidden();
            $this->actingAs($user)->post(route('banners.store'), $this->payload())->assertForbidden();
        }

        $this->assertDatabaseCount('banners', 0);

        $this->actingAs($this->admin)->get(route('banners.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('banners.create'))->assertOk();
    }
}
