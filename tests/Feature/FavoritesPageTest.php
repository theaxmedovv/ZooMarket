<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        $this->category = Category::create(['name' => 'Cats']);
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $this->owner->id,
            'category_id' => $this->category->id,
            'title' => 'Mushukcha',
            'content' => 'Chiroyli mushukcha',
            'breed' => 'Britan',
            'quantity' => 1,
            'gender' => 'male',
            'male_quantity' => 1,
            'female_quantity' => 0,
            'age' => '3 oy',
            'price' => 100000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'approved',
        ], $overrides));
    }

    public function test_favorites_page_lists_saved_listings(): void
    {
        $saved = $this->makePost(['title' => 'Saqlangan mushuk']);
        $this->makePost(['title' => 'Boshqa mushuk']);
        $this->user->likedPosts()->attach($saved->id);

        $this->actingAs($this->user)->get(route('favorites.index'))
            ->assertOk()
            ->assertSee('Sevimlilar')
            ->assertSee('Saqlangan mushuk')
            ->assertDontSee('Boshqa mushuk')
            ->assertSee('data-favorites-grid', false)
            ->assertSee(route('posts.like', $saved), false);
    }

    public function test_sold_or_unapproved_saved_listings_are_left_out_of_page_and_counter(): void
    {
        $open = $this->makePost(['title' => 'Sotuvdagi mushuk']);
        $sold = $this->makePost(['title' => 'Sotilgan mushuk', 'status' => 'sold']);
        $pending = $this->makePost(['title' => 'Tekshiruvdagi mushuk', 'moderation_status' => 'pending']);
        $this->user->likedPosts()->attach([$open->id, $sold->id, $pending->id]);

        $this->actingAs($this->user)->get(route('favorites.index'))
            ->assertOk()
            ->assertSee('1 ta saqlangan e&#039;lon', false)
            ->assertSee('Sotuvdagi mushuk')
            ->assertDontSee('Sotilgan mushuk')
            ->assertDontSee('Tekshiruvdagi mushuk')
            ->assertSeeInOrder(['data-favorites-count', '>1<'], false);
    }

    public function test_empty_favorites_page_shows_empty_state(): void
    {
        $this->actingAs($this->user)->get(route('favorites.index'))
            ->assertOk()
            ->assertSee('Hozircha saqlangan e&#039;lon yo&#039;q', false);
    }

    public function test_favorites_page_requires_login(): void
    {
        $this->get(route('favorites.index'))->assertRedirect(route('login'));
    }

    public function test_profile_no_longer_lists_saved_listings(): void
    {
        $saved = $this->makePost(['title' => 'Saqlangan mushuk']);
        $this->user->likedPosts()->attach($saved->id);

        $this->actingAs($this->user)->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee('Saqlangan mushuk')
            ->assertDontSee('id="saved"', false);
    }
}
