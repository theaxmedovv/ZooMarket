<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The home page, /posts and the favourites page all show the same listing card (partials/post-card).
 */
class SharedListingCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['password' => 'password123']);
        $this->user->assignRole('user');

        $this->owner = User::factory()->create(['name' => 'Sardor']);
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
            'region' => 'Toshkent shahri',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'approved',
        ]);
        $this->user->likedPosts()->attach($this->post->id);
    }

    public function test_home_posts_and_favorites_render_the_same_card(): void
    {
        $this->actingAs($this->user);

        foreach ([route('home'), route('home'), route('favorites.index')] as $url) {
            $this->get($url)
                ->assertOk()
                // Pieces of the /posts card: author, breed chip, stock pill, Batafsil + Xarid buttons
                ->assertSee('Sardor')
                ->assertSee('title="Turi"', false)
                ->assertSee('2 ta')
                ->assertSee('Batafsil')
                ->assertSee('href="' . route('posts.show', $this->post) . '#buy"', false)
                ->assertSee('aria-label="Yoqtirish"', false);
        }
    }

    public function test_owner_gets_edit_menu_instead_of_buy_and_like_on_the_home_page(): void
    {
        $this->actingAs($this->owner)->get(route('home'))
            ->assertOk()
            ->assertSee(route('posts.edit', $this->post), false)
            ->assertDontSee('#buy"', false)
            ->assertDontSee('aria-label="Yoqtirish"', false);
    }

    public function test_home_grids_are_responsive_and_reach_four_cards_per_row(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('lg:grid-cols-3 xl:grid-cols-4', false)
            ->assertDontSee('xl:grid-cols-5', false);
    }

    public function test_login_goes_to_the_home_page(): void
    {
        $this->post(route('login.post'), ['email' => $this->user->email, 'password' => 'password123'])
            ->assertRedirect(route('home'));
    }
}
