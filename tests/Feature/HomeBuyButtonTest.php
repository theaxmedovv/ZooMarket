<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Listing cards on the home page carry a "Sotib olish" button that opens the listing's buy dialog (#buy).
 */
class HomeBuyButtonTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        $this->post = Post::create([
            'user_id' => $this->owner->id,
            'category_id' => Category::create(['name' => 'Cats'])->id,
            'title' => 'Mushukcha',
            'content' => 'Chiroyli mushukcha',
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

    private function buyLink(): string
    {
        return 'href="' . route('posts.show', $this->post) . '#buy"';
    }

    public function test_user_sees_buy_button_on_someone_elses_listing(): void
    {
        $this->actingAs($this->user)->get(route('home'))
            ->assertOk()
            ->assertSee($this->buyLink(), false)
            ->assertSee('Sotib olish');
    }

    public function test_guest_sees_buy_button(): void
    {
        $this->get(route('home'))->assertOk()->assertSee($this->buyLink(), false);
    }

    public function test_owner_does_not_see_buy_button_on_own_listing(): void
    {
        $this->actingAs($this->owner)->get(route('home'))
            ->assertOk()
            ->assertSee('Mushukcha')
            ->assertDontSee($this->buyLink(), false);
    }

    public function test_fully_reserved_listing_has_no_buy_button(): void
    {
        $this->post->update(['status' => 'reserved', 'quantity' => 0, 'male_quantity' => 0]);

        $this->actingAs($this->user)->get(route('home'))
            ->assertOk()
            ->assertDontSee($this->buyLink(), false);
    }

    public function test_buy_link_lands_on_listing_with_buy_dialog(): void
    {
        $this->actingAs($this->user)->get(route('posts.show', $this->post))
            ->assertOk()
            ->assertSee('id="buyModalDetail"', false);
    }
}
