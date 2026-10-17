<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The home page is the only storefront: "Eng arzon narxlarda" (4 cheapest) and "Mashhurlar"
 * (12 most liked, 12 more per "Yana 12 tasini ko'rish"), or search results.
 */
class HomeSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

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
            'price' => 1000000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'approved',
        ], $overrides));
    }

    /** The HTML of one home section, up to the next section. */
    private function section(string $html, string $id): string
    {
        $start = strpos($html, 'id="' . $id . '"');
        $this->assertNotFalse($start, "Section #{$id} is missing");
        $end = strpos($html, '<section', $start + 1);

        return substr($html, $start, $end === false ? null : $end - $start);
    }

    public function test_home_has_cheapest_and_popular_sections_and_no_all_listings_links(): void
    {
        $this->makePost();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mashhurlar')
            ->assertSee('Eng arzon <span>narxlarda</span>', false)
            ->assertDontSee('Hammasi')
            ->assertDontSee("Barcha e&#039;lonlar", false)
            ->assertDontSee('Barchasi');
    }

    public function test_popular_comes_before_cheapest(): void
    {
        $this->makePost();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'id="eng-arzon"'), strpos($html, 'id="mashhurlar"'));
    }

    public function test_cheapest_shows_the_four_lowest_prices_in_soum(): void
    {
        // 100 USD ≈ 1 270 000 so'm: dearer than the 500 000 so'm listings despite the small number
        $this->makePost(['title' => 'Dollarli mushuk', 'price' => 100, 'currency' => 'USD']);
        foreach ([500000, 400000, 300000, 200000] as $price) {
            $this->makePost(['title' => "Arzon {$price}", 'price' => $price]);
        }
        $this->makePost(['title' => 'Bepul kelishiladi', 'price' => 0]);
        $this->makePost(['title' => 'Band mushuk', 'price' => 1000, 'status' => 'reserved', 'quantity' => 0, 'male_quantity' => 0]);
        $this->makePost(['title' => 'Tekshiruvdagi', 'price' => 1000, 'moderation_status' => 'pending']);

        $cheapest = $this->section($this->get(route('home'))->assertOk()->getContent(), 'eng-arzon');

        $this->assertSame(4, substr_count($cheapest, '<article '));
        $this->assertTrue(
            strpos($cheapest, 'Arzon 200000') < strpos($cheapest, 'Arzon 300000')
            && strpos($cheapest, 'Arzon 400000') < strpos($cheapest, 'Arzon 500000'),
            'Cheapest first'
        );
        foreach (['Dollarli mushuk', 'Bepul kelishiladi', 'Band mushuk', 'Tekshiruvdagi'] as $title) {
            $this->assertStringNotContainsString($title, $cheapest);
        }
    }

    public function test_popular_shows_twelve_most_liked_with_load_more(): void
    {
        $posts = collect(range(1, 14))->map(fn ($i) => $this->makePost(['title' => "Mashhur {$i}"]));
        $fan = User::factory()->create();
        $fan->likedPosts()->attach($posts[0]->id); // "Mashhur 1" is the most liked, though the oldest

        $popular = $this->section($this->get(route('home'))->assertOk()->getContent(), 'mashhurlar');

        $this->assertSame(12, substr_count($popular, '<article '));
        // The liked one comes first, then the newest: 14 down to 4
        $this->assertSame(strpos($popular, 'aria-label="Mashhur '), strpos($popular, 'aria-label="Mashhur 1"'));
        $this->assertStringContainsString('Mashhur 14<', $popular);
        $this->assertStringNotContainsString('Mashhur 2<', $popular);
        $this->assertStringContainsString("Yana 12 tasini ko'rish", $popular);
        $this->assertStringContainsString('data-load-more="' . e(route('home.more', ['section' => 'popular', 'page' => 2])) . '"', $popular);
    }

    public function test_load_more_returns_the_next_twelve_and_stops_at_the_end(): void
    {
        foreach (range(1, 26) as $i) {
            $this->makePost(['title' => "Mashhur {$i}"]);
        }

        $page2 = $this->getJson(route('home.more', ['section' => 'popular', 'page' => 2]))->assertOk();
        $this->assertSame(12, substr_count($page2->json('html'), '<article '));
        $this->assertSame(route('home.more', ['section' => 'popular', 'page' => 3]), $page2->json('next'));

        $page3 = $this->getJson($page2->json('next'))->assertOk();
        $this->assertSame(2, substr_count($page3->json('html'), '<article '));
        $this->assertNull($page3->json('next'));
    }

    public function test_no_load_more_button_with_twelve_or_fewer_listings(): void
    {
        foreach (range(1, 12) as $i) {
            $this->makePost(['title' => "Mashhur {$i}"]);
        }

        $this->get(route('home'))->assertOk()->assertDontSee('data-load-more', false);
    }

    public function test_search_shows_results_on_the_home_page(): void
    {
        $this->makePost(['title' => 'Oq mushuk']);
        $this->makePost(['title' => 'Qora it']);

        $this->get(route('home', ['q' => 'mushuk']))
            ->assertOk()
            ->assertSee('id="natijalar"', false)
            ->assertSee('Oq mushuk')
            ->assertDontSee('Qora it')
            ->assertDontSee('id="hero"', false)
            ->assertDontSee('id="mashhurlar"', false);
    }

    public function test_results_load_more_keeps_the_filters(): void
    {
        foreach (range(1, 13) as $i) {
            $this->makePost(['title' => "Oq mushuk {$i}"]);
        }
        $this->makePost(['title' => 'Qora it']);

        $html = $this->get(route('home', ['q' => 'mushuk']))->assertOk()->getContent();
        $next = route('home.more', ['q' => 'mushuk', 'section' => 'results', 'page' => 2]);
        $this->assertStringContainsString('data-load-more="' . e($next) . '"', $html);

        $more = $this->getJson($next)->assertOk();
        $this->assertSame(1, substr_count($more->json('html'), '<article '));
        $this->assertStringNotContainsString('Qora it', $more->json('html'));
    }

    public function test_category_tab_filters_and_clicking_it_again_clears_it(): void
    {
        $dogs = Category::create(['name' => 'Dogs']);
        $this->makePost(['title' => 'Oq mushuk']);
        $this->makePost(['title' => 'Qora it', 'category_id' => $dogs->id]);

        $html = $this->get(route('home', ['category_id' => $dogs->id]))
            ->assertOk()
            ->assertSee('Qora it')
            ->assertDontSee('Oq mushuk')
            // Category names are shown without emoji (tabs, filter list, results title)
            ->assertDontSee('🐶')
            ->assertDontSee('🐱')
            ->getContent();

        // The active tab now links back to the plain home page
        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('home'), '#') . '"\s+aria-current="page"#', $html);
    }

    public function test_category_row_is_text_links_with_a_yana_menu(): void
    {
        Category::create(['name' => 'Dogs']);

        $html = $this->get(route('home'))->assertOk()->getContent();

        // Each category once in the row and once (hidden until it overflows) under "Yana"
        $this->assertStringContainsString('data-overflow-nav', $html);
        $this->assertStringContainsString('aria-controls="zmCatMore"', $html);
        $this->assertSame(Category::count(), substr_count($html, 'data-overflow-copy'));
        $this->assertStringNotContainsString('rounded-full bg-soft px-4', $html);
    }

    public function test_price_sort_compares_currencies_in_soum(): void
    {
        $this->makePost(['title' => 'Dollarli', 'price' => 100, 'currency' => 'USD']);   // ≈ 1 270 000
        $this->makePost(['title' => "So'mli", 'price' => 500000]);

        $html = $this->get(route('home', ['sort' => 'price_asc']))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Dollarli'), strpos($html, 'So&#039;mli'));
    }
}
