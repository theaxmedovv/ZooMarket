<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $seller;
    protected User $buyer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->syncRoles(['admin']);

        $this->seller = User::factory()->create();
        $this->seller->syncRoles(['user']);

        $this->buyer = User::factory()->create();
        $this->buyer->syncRoles(['user']);

        $this->category = Category::create(['name' => 'Itlar']);

        // No API key: the AI step is skipped and every new listing lands in the admin queue
        Config::set('services.groq.key', null);
    }

    private function createListing(string $title = 'Labrador kuchukcha'): Post
    {
        $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => $title,
            'category_id' => $this->category->id,
            'breed' => 'Labrador',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '2 oylik',
            'description' => 'Sog\'lom, emlangan kuchukcha.',
            'price' => 300000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        return Post::where('title', $title)->firstOrFail();
    }

    public function test_new_listing_is_visible_only_to_its_seller_until_admin_approves(): void
    {
        $post = $this->createListing();

        $this->assertSame('pending', $post->moderation_status);

        $this->actingAs($this->seller)->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('tekshiruvda. Biroz vaqtdan keyin tasdiqlanadi', false);

        $this->actingAs($this->buyer)->get(route('posts.show', $post))->assertNotFound();
        $this->actingAs($this->buyer)->get(route('home'))->assertDontSee('Labrador kuchukcha');

        $this->actingAs($this->admin)->get(route('moderation.index'))
            ->assertOk()
            ->assertSee('Labrador kuchukcha');

        $this->actingAs($this->admin)->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Tasdiqlash')
            ->assertSee('Rad etish');
    }

    public function test_admin_approval_makes_listing_public(): void
    {
        $post = $this->createListing();

        $this->actingAs($this->admin)->post(route('moderation.approve', $post))
            ->assertRedirect()
            ->assertSessionHas('success');

        $post->refresh();
        $this->assertSame('approved', $post->moderation_status);
        $this->assertSame($this->admin->id, $post->moderated_by);

        $this->actingAs($this->buyer)->get(route('home'))->assertSee('Labrador kuchukcha');
        $this->actingAs($this->buyer)->get(route('posts.show', $post))->assertOk();
    }

    public function test_admin_rejection_shows_reason_to_seller_and_hides_listing(): void
    {
        $post = $this->createListing();
        $reason = 'Tavsifda boshqa saytga reklama havolasi bor, uni olib tashlang.';

        $this->actingAs($this->admin)->post(route('moderation.reject', $post), ['reason' => $reason])
            ->assertRedirect()
            ->assertSessionHas('success');

        $post->refresh();
        $this->assertSame('rejected', $post->moderation_status);
        $this->assertSame($reason, $post->moderation_reason);

        $this->actingAs($this->seller)->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('admin tomonidan rad etildi', false)
            ->assertSee($reason);

        $this->actingAs($this->buyer)->get(route('posts.show', $post))->assertNotFound();
    }

    public function test_rejection_requires_a_reason(): void
    {
        $post = $this->createListing();

        $this->actingAs($this->admin)->post(route('moderation.reject', $post), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending', $post->fresh()->moderation_status);
    }

    public function test_seller_edit_sends_approved_listing_back_for_review(): void
    {
        $post = $this->createListing();
        $this->actingAs($this->admin)->post(route('moderation.approve', $post));

        $this->actingAs($this->seller)->put(route('posts.update', $post), [
            'title' => 'Labrador kuchukcha',
            'category_id' => $this->category->id,
            'breed' => 'Labrador',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '3 oylik',
            'description' => 'Yangilangan tavsif.',
            'price' => 350000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        $this->assertSame('pending', $post->fresh()->moderation_status);
        $this->actingAs($this->buyer)->get(route('posts.show', $post))->assertNotFound();
    }

    public function test_only_admin_can_moderate(): void
    {
        $post = $this->createListing();

        foreach ([$this->seller, $this->buyer] as $user) {
            $this->actingAs($user)->get(route('moderation.index'))->assertForbidden();
            $this->actingAs($user)->post(route('moderation.approve', $post))->assertForbidden();
        }

        $this->assertSame('pending', $post->fresh()->moderation_status);
    }

    public function test_admin_is_sent_to_moderation_queue_after_login(): void
    {
        auth()->logout();

        $this->post(route('login.post'), ['email' => 'admin@test.com', 'password' => 'password'])
            ->assertRedirect(route('moderation.index'));
    }

    public function test_admin_create_command_creates_admin_account(): void
    {
        $this->artisan('admin:create', ['email' => 'boss@test.com', '--password' => 'secret-pass'])
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'boss@test.com')->firstOrFail()->hasRole('admin'));
    }
}
