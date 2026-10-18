<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $seller;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Bosh admin']);
        $this->admin->syncRoles(['admin']);
        $this->seller = User::factory()->create(['name' => 'Sardor']);
        $this->seller->assignRole('user');
        $this->category = Category::create(['name' => 'Cats']);
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Mushukcha',
            'content' => 'x',
            'breed' => 'Britan',
            'quantity' => 1,
            'gender' => 'male',
            'male_quantity' => 1,
            'female_quantity' => 0,
            'age' => '3 oy',
            'price' => 100000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Chilonzor',
            'status' => 'active',
            'moderation_status' => 'approved',
        ], $overrides));
    }

    public function test_admin_profile_is_a_dashboard_with_counts(): void
    {
        $this->makePost(['title' => 'Kutayotgan 1', 'moderation_status' => 'pending']);
        $this->makePost(['title' => 'Kutayotgan 2', 'moderation_status' => 'pending']);
        $this->makePost(['title' => 'Sotuvda']);
        $this->makePost(['title' => 'Sotilgan', 'status' => 'sold']);
        Banner::create(['title' => 'Banner', 'color' => '#212844', 'created_by' => $this->seller->id, 'moderation_status' => 'pending']);

        $html = $this->actingAs($this->admin)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Bosh admin')
            ->assertSee('Kutilayotgan e')
            ->assertSee('Tekshiruv navbati')
            ->assertSee('Oxirgi qarorlaringiz')
            ->getContent();

        // Pending posts (2), pending banners (1), on sale (1), users (1): the cards link to the right place
        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('moderation.index'), '#') . '"[^>]*>.*?>2</div>#s', $html);
        $this->assertMatchesRegularExpression('#status=pending"[^>]*>.*?>1</div>#s', $html);
        $this->assertStringContainsString('Sotilgan e', $html);
        $this->assertStringContainsString('Eng eskisi', $html);
    }

    public function test_queue_lists_the_oldest_pending_listings_first(): void
    {
        $this->makePost(['title' => 'Yangi kelgan', 'moderation_status' => 'pending']);
        $old = $this->makePost(['title' => 'Eng eski', 'moderation_status' => 'pending']);
        // updated_at isn't mass assignable; a plain query update leaves it as written
        Post::whereKey($old->id)->update(['updated_at' => now()->subDays(2)]);
        $this->makePost(['title' => 'Tasdiqlangan', 'moderation_status' => 'approved']);

        $html = $this->actingAs($this->admin)->get(route('profile.show'))->getContent();
        $queue = substr($html, strpos($html, 'Tekshiruv navbati'), strpos($html, 'Oxirgi qarorlaringiz') - strpos($html, 'Tekshiruv navbati'));

        $this->assertLessThan(strpos($queue, 'Yangi kelgan'), strpos($queue, 'Eng eski'));
        $this->assertStringNotContainsString('Tasdiqlangan', $queue);
    }

    public function test_empty_queue_says_so(): void
    {
        $this->actingAs($this->admin)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Navbat bo', false)
            ->assertSee('Hali qaror qabul qilmagansiz');
    }

    public function test_latest_decisions_show_only_this_admins_with_their_outcome(): void
    {
        $other = User::factory()->create();
        $other->syncRoles(['admin']);
        $mine = $this->makePost(['title' => 'Mening rad etganim', 'moderation_status' => 'rejected', 'moderation_reason' => 'Rasm sifatsiz', 'moderated_by' => $this->admin->id, 'moderated_at' => now()]);
        $this->makePost(['title' => 'Boshqa admin qarori', 'moderated_by' => $other->id, 'moderated_at' => now()]);

        $this->actingAs($this->admin)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Mening rad etganim')
            ->assertSee('Rad etilgan')
            ->assertSee('Rasm sifatsiz')
            ->assertDontSee('Boshqa admin qarori');
    }

    public function test_regular_users_do_not_get_the_admin_dashboard(): void
    {
        $this->actingAs($this->seller)->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee('Tekshiruv navbati')
            ->assertDontSee('Oxirgi qarorlaringiz')
            ->assertSee('Mening e');
    }

    public function test_settings_show_the_current_photo_and_header_uses_it(): void
    {
        $this->actingAs($this->admin)->post(route('admin.profile.update'), [
            'name' => 'Bosh admin',
            'avatar' => UploadedFile::fake()->image('me.png', 200, 200),
        ])->assertSessionHasNoErrors();

        $this->admin->refresh();
        $this->assertNotNull($this->admin->avatar);

        $html = $this->actingAs($this->admin)->get(route('profile.show'))->getContent();

        $this->assertStringContainsString('data-avatar-input', $html);
        // The settings preview and the header button both show the uploaded photo, not the initial
        $this->assertGreaterThanOrEqual(3, substr_count($html, $this->admin->avatarUrl()));
        $this->assertStringNotContainsString('data-avatar-initial', $html);
    }

    public function test_settings_without_a_photo_show_the_initial(): void
    {
        $this->actingAs($this->admin)->get(route('profile.show'))->assertOk()->assertSee('data-avatar-initial', false);
    }
}
