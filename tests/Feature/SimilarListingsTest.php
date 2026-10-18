<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimilarListingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Category $cats;
    protected Category $dogs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');
        $this->cats = Category::create(['name' => 'Cats']);
        $this->dogs = Category::create(['name' => 'Dogs']);
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $this->owner->id,
            'category_id' => $this->cats->id,
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
            'location' => 'Chilonzor',
            'status' => 'active',
            'moderation_status' => 'approved',
        ], $overrides));
    }

    private function similarSection(Post $post): string
    {
        $html = $this->get(route('posts.show', $post))->assertOk()->getContent();
        $start = strpos($html, 'id="oxshash"');

        return $start === false ? '' : substr($html, $start, strpos($html, '</section>', $start) - $start);
    }

    public function test_listing_page_shows_similar_listings_from_the_same_category(): void
    {
        $post = $this->makePost(['title' => 'Asosiy mushuk']);
        $this->makePost(['title' => 'Boshqa mushuk']);
        $this->makePost(['title' => 'Bir it', 'category_id' => $this->dogs->id]);

        $this->get(route('posts.show', $post))->assertOk()->assertSee("O'xshash e'lonlar", false);
        $section = $this->similarSection($post);

        $this->assertStringContainsString('Boshqa mushuk', $section);
        $this->assertStringNotContainsString('Bir it', $section);
        $this->assertStringNotContainsString('Asosiy mushuk', $section, 'The listing itself is left out');
        $this->assertStringContainsString('lg:grid-cols-4', $section);
    }

    public function test_same_breed_comes_first_and_at_most_eight_are_shown(): void
    {
        $post = $this->makePost(['title' => 'Asosiy', 'breed' => 'Britan']);
        $this->makePost(['title' => 'Eski Britan', 'breed' => 'Britan', 'created_at' => now()->subDays(5)]);
        foreach (range(1, 9) as $i) {
            $this->makePost(['title' => "Sfinks {$i}", 'breed' => 'Sfinks']);
        }

        $section = $this->similarSection($post);

        $this->assertSame(8, substr_count($section, '<article '));
        // The older Britan still outranks the newer Sfinks listings
        $this->assertStringContainsString('Eski Britan', $section);
    }

    public function test_hidden_listings_are_not_suggested(): void
    {
        $post = $this->makePost();
        $this->makePost(['title' => 'Sotilgan mushuk', 'status' => 'sold']);
        $this->makePost(['title' => 'Tekshiruvdagi mushuk', 'moderation_status' => 'pending']);

        $this->assertSame('', $this->similarSection($post), 'No section when there is nothing to suggest');
        $this->get(route('posts.show', $post))->assertDontSee('Sotilgan mushuk')->assertDontSee('Tekshiruvdagi mushuk');
    }
}
