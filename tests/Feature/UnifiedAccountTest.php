<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * There is no separate seller or buyer account: every user can both post listings and buy.
 */
class UnifiedAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $alice;
    protected User $bob;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->alice = User::factory()->create(['name' => 'Alice']);
        $this->alice->assignRole('user');

        $this->bob = User::factory()->create(['name' => 'Bob']);
        $this->bob->assignRole('user');

        $this->category = Category::create(['name' => 'Cats']);
    }

    private function makeListing(User $owner): Post
    {
        return Post::create([
            'user_id' => $owner->id,
            'category_id' => $this->category->id,
            'title' => "Mushukcha",
            'content' => "Chiroyli mushukcha",
            'breed' => 'Britan',
            'quantity' => 2,
            'gender' => 'male',
            'male_quantity' => 2,
            'female_quantity' => 0,
            'age' => '3 oy',
            'price' => 100000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'approved',
        ]);
    }

    public function test_register_creates_one_account_that_can_sell_and_buy(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Vali',
            'email' => 'vali@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'vali@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->can('create posts'));
        $this->assertSame(['user'], $user->getRoleNames()->all());
    }

    public function test_register_page_has_no_role_choice(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="role"', false)
            ->assertDontSee('Xaridor');
    }

    public function test_the_same_user_can_post_a_listing_and_buy_from_others(): void
    {
        $this->actingAs($this->alice)->get(route('posts.create'))->assertOk();

        $bobsListing = $this->makeListing($this->bob);

        $this->actingAs($this->alice)->post(route('purchase-requests.store'), [
            'animal_id' => $bobsListing->id,
            'male_quantity' => 1,
            'female_quantity' => 0,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', ['user_id' => $this->alice->id, 'animal_id' => $bobsListing->id]);
    }

    public function test_user_cannot_buy_their_own_listing(): void
    {
        $listing = $this->makeListing($this->alice);

        $this->actingAs($this->alice)->post(route('purchase-requests.store'), [
            'animal_id' => $listing->id,
            'male_quantity' => 1,
            'female_quantity' => 0,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(0, PurchaseRequest::count());
        $this->assertSame(2, $listing->fresh()->male_quantity);
    }

    public function test_own_listing_shows_edit_instead_of_buy_button(): void
    {
        $listing = $this->makeListing($this->alice);

        $this->actingAs($this->alice)->get(route('posts.show', $listing))
            ->assertOk()
            ->assertSee(route('posts.edit', $listing), false)
            ->assertDontSee('data-dialog-open="buyModalDetail"', false);

        $this->actingAs($this->bob)->get(route('posts.show', $listing))
            ->assertOk()
            ->assertDontSee(route('posts.edit', $listing), false)
            ->assertSee('data-dialog-open="buyModalDetail"', false);
    }

    public function test_user_cannot_edit_or_delete_someone_elses_listing(): void
    {
        $listing = $this->makeListing($this->bob);

        $this->actingAs($this->alice)->get(route('posts.edit', $listing))->assertForbidden();
        $this->actingAs($this->alice)->put(route('posts.update', $listing), ['title' => 'Hacked'])->assertForbidden();
        $this->actingAs($this->alice)->delete(route('posts.destroy', $listing))->assertForbidden();

        $this->assertSame('Mushukcha', $listing->fresh()->title);
        $this->assertNull($listing->fresh()->deleted_at);
    }

    public function test_my_listings_shows_only_own_listings_in_every_moderation_state(): void
    {
        $mine = $this->makeListing($this->alice);
        $mine->update(['title' => 'Mening tekshiruvdagi e\'lonim', 'moderation_status' => 'pending']);
        $other = $this->makeListing($this->bob);
        $other->update(['title' => 'Bobning e\'loni']);

        $this->actingAs($this->alice)->get(route('posts.mine'))
            ->assertOk()
            ->assertSee("Mening e&#039;lonlarim", false)
            ->assertSee("Mening tekshiruvdagi e&#039;lonim", false)
            ->assertDontSee("Bobning e&#039;loni", false);

        // The storefront shows only approved listings, including other people's
        $this->actingAs($this->alice)->get(route('home'))
            ->assertOk()
            ->assertSee("Bobning e&#039;loni", false)
            ->assertDontSee("Mening tekshiruvdagi e&#039;lonim", false);
    }

    public function test_old_buyer_profile_url_redirects_to_the_single_profile(): void
    {
        $this->actingAs($this->alice)->get('/user/profile')->assertRedirect(route('profile.show'));

        $this->actingAs($this->alice)->get(route('profile.show'))
            ->assertOk()
            ->assertSee("Mening e&#039;lonlarim", false)
            ->assertDontSee("Saqlangan e&#039;lonlar", false);
    }

    public function test_migration_moves_existing_sellers_to_the_single_user_role(): void
    {
        $seller = Role::create(['name' => 'seller', 'guard_name' => 'web']);
        Role::findByName('user', 'web')->syncPermissions(['read posts']);
        $oldSeller = User::factory()->create();
        $oldSeller->assignRole($seller);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $migration = require database_path('migrations/2026_10_17_000001_merge_seller_role_into_user.php');
        $migration->up();

        $oldSeller = $oldSeller->fresh();
        $this->assertSame(['user'], $oldSeller->getRoleNames()->all());
        $this->assertTrue($oldSeller->can('create posts'));
        $this->assertTrue($this->bob->fresh()->can('create posts'));
        $this->assertNull(Role::where('name', 'seller')->first());
    }
}
