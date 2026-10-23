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
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'])->syncPermissions($permissions);

        $this->seller = User::factory()->create();
        $this->seller->assignRole('user');
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
            'region' => 'Toshkent shahri',
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
        foreach (['/', '/?q=Mushuk', "/posts/{$this->post->id}", '/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee('id="zmHeader"', false)->assertSee('id="zmFooter"', false);
        }
    }

    public function test_header_has_katalog_left_of_search_and_no_filter_panel(): void
    {
        $category = Category::firstWhere('name', 'Mushuk');

        $html = $this->get('/?' . http_build_query(['q' => 'britan', 'category_id' => $category->id]))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="zmSearchForm"'));
        $this->assertStringNotContainsString('zmFilterPanel', $html);

        // Katalog comes before the search form and lists every category
        $this->assertLessThan(strpos($html, 'id="zmSearchForm"'), strpos($html, 'aria-controls="zmCatalog"'));
        $menu = substr($html, strpos($html, 'id="zmCatalog"'), 3000);
        $this->assertStringContainsString('category_id=' . $category->id, $menu);

        $this->assertMatchesRegularExpression('/name="q" value="britan"/', $html);
    }

    public function test_my_listings_search_targets_my_listings(): void
    {
        $this->actingAs($this->seller)->get('/my-posts')
            ->assertOk()
            ->assertSee('action="' . route('posts.mine') . '"', false)
            ->assertDontSee('zmFilterPanel', false);
    }

    public function test_old_listing_page_links_land_on_the_home_page(): void
    {
        $this->get('/posts?category_id=3&sort=price_asc')->assertRedirect(route('home', ['category_id' => 3, 'sort' => 'price_asc']));
        $this->actingAs($this->seller)->get('/posts?mine=1')->assertRedirect(route('posts.mine'));
    }

    public function test_buyer_pages_render_with_site_chrome(): void
    {
        $this->actingAs($this->buyer);

        foreach (['/', '/?q=Mushuk', "/posts/{$this->post->id}", '/profile', '/favorites', '/user/purchase-requests', '/chats', "/chats/{$this->chat->id}"] as $url) {
            $this->get($url)->assertOk()->assertSee('id="zmHeader"', false)->assertSee('aria-label="Mening sahifalarim"', false);
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
            $chrome = str_replace($m[0] ?? '', '', $html);
            // The phone's tab bar and the profile sheet's phone-only shortcuts repeat header links on purpose
            $chrome = preg_replace('#<nav aria-label="Asosiy menyu".*?</nav>#s', '', $chrome);
            $chrome = preg_replace('#<a [^>]*[\s"]md:hidden[^>]*>.*?</a>#s', '', $chrome);
            return [$chrome, $m[1] ?? ''];
        };

        // One account both sells and buys: selling and buying entry points share the same chrome.
        $this->actingAs($this->seller);
        foreach (['/?q=Mushuk', '/my-posts', "/posts/{$this->post->id}", '/profile', '/favorites', '/admin/purchase-requests', '/admin/archive', '/user/purchase-requests', '/chats'] as $url) {
            [$chrome, $main] = $split($this->get($url)->assertOk()->getContent());
            $this->assertSame(1, $count($chrome, '/posts/create'), "{$url}: 'Yangi e'lon' must appear once in the chrome");
            // Incoming requests, archive, banners live in the account row under the header, not in the profile menu
            $this->assertSame(1, $count($chrome, '/admin/purchase-requests'), "{$url}: 'So'rovlar' must appear once in the chrome");
            $this->assertSame(1, $count($chrome, '/admin/archive'), "{$url}: 'Arxiv' must appear once in the chrome");
            $this->assertSame(1, $count($chrome, '/banners'), "{$url}: 'Bannerlar' must appear once in the chrome");
            $this->assertSame(1, $count($chrome, '/user/purchase-requests'), "{$url}: 'Buyurtmalar' must appear once in the chrome");
            $this->assertSame(1, $count($chrome, '/favorites'), "{$url}: 'Sevimlilar' must appear once in the chrome");
            $this->assertSame(0, $count($main, '/posts/create'), "{$url}: no extra 'Yangi e'lon' buttons in the page");
            $this->assertSame(0, preg_match('#href="[^"]*/admin"#', $chrome . $main), "{$url}: no links to the /admin redirect");
        }
        // Tab pages don't repeat the tab row as buttons.
        [, $main] = $split($this->get('/admin/purchase-requests')->getContent());
        $this->assertSame(0, $count($main, '/admin/archive'));
        [, $main] = $split($this->get('/admin/archive')->getContent());
        $this->assertSame(0, $count($main, '/admin/purchase-requests'));

    }

    public function test_seller_pages_render_with_site_chrome(): void
    {
        $this->actingAs($this->seller);

        foreach ([
            '/', '/my-posts', '/posts/create', "/posts/{$this->post->id}", "/posts/{$this->post->id}/edit",
            '/profile', '/admin/purchase-requests', '/admin/archive', '/chats', "/chats/{$this->chat->id}",
        ] as $url) {
            $response = $this->get($url);
            $this->assertContains($response->status(), [200], "{$url} returned {$response->status()}");
            $response->assertSee('id="zmHeader"', false)->assertSee("Yangi e'lon", false);
        }
    }
}
