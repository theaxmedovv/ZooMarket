<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        Category::create([
            'name' => 'Big Cats',
        ]);
    }

    /**
     * Test that home page redirects to posts page
     */
    public function test_home_redirects_to_posts(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('posts.index'));
    }

    /**
     * Test that posts page loads successfully
     */
    public function test_posts_page_loads(): void
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
    }

    /**
     * Test that posts page with query filters and sort works
     */
    public function test_posts_page_with_filters_and_sort(): void
    {
        $response = $this->get('/posts?gender=male&price_min=10&price_max=500&sort=price_asc');

        $response->assertStatus(200);
        $response->assertSee('Filtrlar');
        $response->assertSee('Narx: arzonroq');
    }

    public function test_seller_approval_creates_chat_and_return_listing_reactivates_post(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $buyer = User::factory()->create();
        $buyer->assignRole('user');

        $post = Post::create([
            'title' => 'Test animal',
            'category_id' => 1,
            'breed' => 'Labrador',
            'gender' => 'male',
            'age' => '2 years',
            'color' => 'Black',
            'description' => 'Healthy pet',
            'price' => 2500,
            'currency' => 'UZS',
            'is_negotiable' => true,
            'location' => 'Tashkent',
            'status' => 'active',
            'content' => 'Healthy pet',
            'user_id' => $seller->id,
        ]);

        $request = PurchaseRequest::create([
            'user_id' => $buyer->id,
            'animal_id' => $post->id,
            'status' => 'pending',
        ]);

        $this->actingAs($seller)
            ->post(route('admin.purchase-requests.approve', $request))
            ->assertRedirect();

        $request->refresh();
        $post->refresh();

        $this->assertSame('approved', $request->status);
        $this->assertSame('active', $post->status);
        $this->assertDatabaseHas('chats', [
            'purchase_request_id' => $request->id,
            'post_id' => $post->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->post(route('admin.purchase-requests.mark-sold', $request))
            ->assertRedirect();

        $request->refresh();
        $post->refresh();

        $this->assertSame('sold', $request->status);
        $this->assertSame('sold', $post->status);

        $this->actingAs($seller)
            ->post(route('admin.purchase-requests.return-listing', $request))
            ->assertRedirect();

        $post->refresh();
        $request->refresh();

        $this->assertSame('active', $post->status);
        $this->assertSame('rejected', $request->status);
    }
}
