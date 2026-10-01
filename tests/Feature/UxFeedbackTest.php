<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * User feedback: in-place likes, toasts for every redirect message, styled pagination.
 */
class UxFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $buyer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['create posts', 'read posts', 'edit posts', 'delete posts'];
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web'])->syncPermissions($permissions);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'])->syncPermissions(['read posts']);

        $this->seller = User::factory()->create();
        $this->seller->assignRole('seller');
        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('user');
        $this->category = Category::firstOrCreate(['name' => 'Mushuk']);
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Britan mushugi',
            'content' => 'Sog\'lom',
            'quantity' => 1,
            'gender' => 'male',
            'male_quantity' => 1,
            'female_quantity' => 0,
            'price' => 900000,
            'currency' => 'UZS',
            'status' => 'active',
            'moderation_status' => 'approved',
        ], $overrides));
    }

    public function test_like_toggles_in_place_with_json(): void
    {
        $post = $this->makePost();

        $this->actingAs($this->buyer)->postJson(route('posts.like', $post))
            ->assertOk()
            ->assertJson(['liked' => true, 'likes' => 1, 'favorites' => 1]);

        $this->actingAs($this->buyer)->postJson(route('posts.like', $post))
            ->assertOk()
            ->assertJson(['liked' => false, 'likes' => 0, 'favorites' => 0]);
    }

    public function test_like_without_javascript_still_redirects_back(): void
    {
        $post = $this->makePost();

        $this->actingAs($this->buyer)->from(route('posts.index'))->post(route('posts.like', $post))
            ->assertRedirect(route('posts.index'))
            ->assertSessionHas('success');
    }

    public function test_failed_purchase_request_is_shown_to_the_buyer(): void
    {
        $post = $this->makePost();

        // Asks for 3 when only 1 is available: the error used to be lost on the listing page.
        $this->actingAs($this->buyer)
            ->from(route('posts.show', $post))
            ->followingRedirects()
            ->post(route('purchase-requests.store'), ['animal_id' => $post->id, 'gender' => 'male', 'quantity' => 3])
            ->assertOk()
            ->assertSee('data-toast="error"', false)
            ->assertSee('Hozirda mavjud: 1 ta', false);
    }

    public function test_success_message_is_shown_once_as_a_toast(): void
    {
        $post = $this->makePost();

        $html = $this->actingAs($this->buyer)
            ->from(route('user.purchase-requests.index'))
            ->followingRedirects()
            ->post(route('purchase-requests.store'), ['animal_id' => $post->id, 'gender' => 'male', 'quantity' => 1])
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, "muvaffaqiyatli yuborildi"));
        $this->assertStringContainsString('data-toast="success"', $html);
    }

    public function test_header_shows_favorites_count_for_buyers(): void
    {
        $post = $this->makePost();
        $this->buyer->likedPosts()->attach($post->id);

        $this->actingAs($this->buyer)->get(route('posts.index'))
            ->assertOk()
            ->assertSee('#saved', false)
            ->assertSeeInOrder(['data-favorites-count', '>1<'], false);
    }

    public function test_listing_pagination_uses_site_markup(): void
    {
        foreach (range(1, 13) as $i) {
            $this->makePost(['title' => "Mushuk {$i}"]);
        }

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('aria-label="Sahifalar"', false)
            ->assertSee('rel="next"', false);
    }
}
