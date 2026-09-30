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

    public function test_header_search_form_carries_the_filters(): void
    {
        $category = Category::firstWhere('name', 'Mushuk');

        $html = $this->get('/posts?' . http_build_query([
            'q' => 'britan', 'category_id' => $category->id, 'gender' => 'female', 'price_min' => 100, 'location' => 'Toshkent',
        ]))->assertOk()->getContent();

        // One form handles search and filters; the old in-page filter bar is gone.
        $this->assertSame(1, substr_count($html, 'id="zmSearchForm"'));
        $this->assertStringNotContainsString('search-filter-hero', $html);
        $this->assertStringContainsString('id="zmFilterPanel"', $html);

        // Current values are pre-filled and the active-filter count is shown.
        $this->assertMatchesRegularExpression('/name="q" value="britan"/', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $category->id . '" selected>/', $html);
        $this->assertMatchesRegularExpression('/name="gender" value="female" checked/', $html);
        $this->assertStringContainsString('value="Toshkent"', $html);
        $this->assertStringContainsString('4 ta filtr faol', $html);
    }

    public function test_sellers_get_search_without_filter_panel(): void
    {
        $this->actingAs($this->seller)->get('/posts')
            ->assertOk()
            ->assertSee('id="zmSearchForm"', false)
            ->assertDontSee('id="zmFilterPanel"', false);
    }

    public function test_buyer_pages_render_with_site_chrome(): void
    {
        $this->actingAs($this->buyer);

        foreach (['/', '/posts', "/posts/{$this->post->id}", '/user/profile', '/user/purchase-requests', '/chats', "/chats/{$this->chat->id}"] as $url) {
            $this->get($url)->assertOk()->assertSee('zm-header', false)->assertSee('aria-label="Buyurtmalar"', false);
        }
    }

    /**
     * Each destination has exactly one entry point in the site chrome (header + tab row).
     * Guards against the old pile-up: 3-4 "new listing" links, 5 "requests" links, /admin links.
     */
    public function test_navigation_has_no_duplicate_entry_points(): void
    {
        $count = fn (string $html, string $path) => preg_match_all('#href="[^"]*' . preg_quote($path, '#') . '(\?[^"]*)?"#', $html);
        // Split a page into site chrome (header, tab row, footer) and page content (<main>).
        $split = function (string $html): array {
            preg_match('#<main>(.*)</main>#s', $html, $m);
            return [str_replace($m[0] ?? '', '', $html), $m[1] ?? ''];
        };

        $this->actingAs($this->seller);
        foreach (['/posts', "/posts/{$this->post->id}", '/profile', '/admin/purchase-requests', '/admin/archive', '/chats'] as $url) {
            [$chrome, $main] = $split($this->get($url)->assertOk()->getContent());
            $this->assertSame(1, $count($chrome, '/posts/create'), "{$url}: 'Yangi e'lon' must appear once in the chrome");
            $this->assertSame(1, $count($chrome, '/admin/purchase-requests'), "{$url}: 'So'rovlar' must appear once in the chrome");
            $this->assertSame(1, $count($chrome, '/admin/archive'), "{$url}: 'Arxiv' must appear once in the chrome");
            $this->assertSame(0, $count($main, '/posts/create'), "{$url}: no extra 'Yangi e'lon' buttons in the page");
            $this->assertSame(0, preg_match('#href="[^"]*/admin"#', $chrome . $main), "{$url}: no links to the /admin redirect");
        }
        // Tab pages don't repeat the tab row as buttons.
        [, $main] = $split($this->get('/admin/purchase-requests')->getContent());
        $this->assertSame(0, $count($main, '/admin/archive'));
        [, $main] = $split($this->get('/admin/archive')->getContent());
        $this->assertSame(0, $count($main, '/admin/purchase-requests'));

        $this->actingAs($this->buyer);
        foreach (['/posts', '/user/profile', '/user/purchase-requests', '/chats'] as $url) {
            [$chrome] = $split($this->get($url)->assertOk()->getContent());
            $this->assertSame(1, $count($chrome, '/user/purchase-requests'), "{$url}: 'Buyurtmalar' must appear once in the chrome");
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
