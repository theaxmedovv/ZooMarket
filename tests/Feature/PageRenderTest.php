<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chat;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Smoke test: every page renders with the shared site header/footer for each role.
 */
class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $buyer;
    private Post $post;
    private Chat $chat;

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

        $category = Category::firstOrCreate(['name' => 'Mushuk']);

        $this->post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Britan mushugi',
            'content' => 'Sog\'lom mushukcha',
            'breed' => 'Britan',
            'quantity' => 2,
            'gender' => 'mixed',
            'male_quantity' => 1,
            'female_quantity' => 1,
            'price' => 900000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'approved',
        ]);

        $request = PurchaseRequest::create([
            'user_id' => $this->buyer->id,
            'animal_id' => $this->post->id,
            'gender' => 'male',
            'quantity' => 1,
            'status' => 'approved',
        ]);

        $this->chat = Chat::create([
            'purchase_request_id' => $request->id,
            'post_id' => $this->post->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ]);
    }

    public function test_guest_pages_render_with_site_chrome(): void
    {
        foreach (['/', '/posts', "/posts/{$this->post->id}", '/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee('zm-header', false)->assertSee('zm-footer', false);
        }
    }

    public function test_buyer_pages_render_with_site_chrome(): void
    {
        $this->actingAs($this->buyer);

        foreach (['/', '/posts', "/posts/{$this->post->id}", '/user/profile', '/user/purchase-requests', '/chats', "/chats/{$this->chat->id}"] as $url) {
            $this->get($url)->assertOk()->assertSee('zm-header', false)->assertSee('Buyurtmalarim');
        }
    }

    public function test_seller_pages_render_with_site_chrome(): void
    {
        $this->actingAs($this->seller);

        foreach ([
            '/posts', '/posts/create', "/posts/{$this->post->id}", "/posts/{$this->post->id}/edit",
            '/profile', '/admin/purchase-requests', '/admin/archive', '/chats', "/chats/{$this->chat->id}",
        ] as $url) {
            $response = $this->get($url);
            $this->assertContains($response->status(), [200], "{$url} returned {$response->status()}");
            $response->assertSee('zm-header', false)->assertSee("Yangi e'lon", false);
        }
    }
}
