<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seller pages with reviews, listing expiry with renewal, and reports to the admin.
 */
class TrustFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $buyer;
    private User $stranger;
    private User $admin;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        foreach (['seller', 'buyer', 'stranger'] as $name) {
            $this->{$name} = User::factory()->create();
            $this->{$name}->assignRole('user');
        }
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->post = $this->makePost();
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create($overrides + [
            'user_id' => $this->seller->id,
            'category_id' => Category::firstOrCreate(['name' => 'Mushuk'])->id,
            'title' => 'Britan mushugi',
            'content' => 'x',
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
    }

    private function dealWith(User $buyer, string $status = 'approved'): void
    {
        PurchaseRequest::create(['user_id' => $buyer->id, 'animal_id' => $this->post->id, 'gender' => 'male', 'quantity' => 1, 'status' => $status]);
    }

    // ---- seller page & reviews ----

    public function test_seller_page_is_public_and_lists_only_listings_on_sale(): void
    {
        $this->makePost(['title' => 'Yashirin e\'lon', 'moderation_status' => 'pending']);
        $this->makePost(['title' => 'Sotilgan it', 'status' => 'sold']);

        $this->get(route('sellers.show', $this->seller))
            ->assertOk()
            ->assertSee($this->seller->name)
            ->assertSee('Britan mushugi')
            ->assertDontSee('Yashirin e\'lon')
            ->assertDontSee('Sotilgan it')
            ->assertSee('Hali baholanmagan');
    }

    public function test_the_admin_has_no_seller_page(): void
    {
        $this->get(route('sellers.show', $this->admin))->assertNotFound();
    }

    public function test_listing_links_to_the_seller_page_and_shows_the_rating(): void
    {
        Review::create(['seller_id' => $this->seller->id, 'reviewer_id' => $this->buyer->id, 'rating' => 4, 'comment' => 'Yaxshi']);

        $this->get(route('posts.show', $this->post))
            ->assertOk()
            ->assertSee(route('sellers.show', $this->seller), false)
            ->assertSee('4.0');
    }

    public function test_only_a_buyer_with_an_accepted_request_can_review(): void
    {
        $this->dealWith($this->buyer, 'pending');
        $this->actingAs($this->buyer)
            ->post(route('reviews.store', $this->seller), ['rating' => 5])
            ->assertForbidden();

        $this->dealWith($this->stranger, 'approved');
        $this->actingAs($this->stranger)
            ->post(route('reviews.store', $this->seller), ['rating' => 5, 'comment' => "A'lo sotuvchi"])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', ['seller_id' => $this->seller->id, 'reviewer_id' => $this->stranger->id, 'rating' => 5]);
        $this->get(route('sellers.show', $this->seller))->assertSee("A'lo sotuvchi")->assertSee('5.0');
    }

    public function test_reviewing_again_edits_the_review_and_rating_is_validated(): void
    {
        $this->dealWith($this->buyer, 'sold');
        $this->actingAs($this->buyer);

        $this->post(route('reviews.store', $this->seller), ['rating' => 2])->assertRedirect();
        $this->post(route('reviews.store', $this->seller), ['rating' => 5, 'comment' => 'Fikrim o\'zgardi'])->assertRedirect();
        $this->assertSame(1, Review::count());
        $this->assertSame(5, Review::first()->rating);

        $this->from('/')->post(route('reviews.store', $this->seller), ['rating' => 9])->assertSessionHasErrors('rating');
    }

    public function test_nobody_reviews_themselves_and_only_the_author_or_admin_deletes(): void
    {
        $this->actingAs($this->seller)->post(route('reviews.store', $this->seller), ['rating' => 5])->assertForbidden();

        $review = Review::create(['seller_id' => $this->seller->id, 'reviewer_id' => $this->buyer->id, 'rating' => 3]);

        $this->actingAs($this->stranger)->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->actingAs($this->buyer)->delete(route('reviews.destroy', $review))->assertRedirect();
        $this->assertSame(0, Review::count());
    }

    public function test_review_form_only_shows_to_eligible_buyers(): void
    {
        $this->dealWith($this->buyer);

        $this->actingAs($this->buyer)->get(route('sellers.show', $this->seller))->assertSee(route('reviews.store', $this->seller), false);
        $this->actingAs($this->stranger)->get(route('sellers.show', $this->seller))->assertDontSee(route('reviews.store', $this->seller), false);
    }

    // ---- expiry & renewal ----

    public function test_new_listings_get_an_end_date(): void
    {
        $days = (int) config('zoomarket.listing_days');

        $this->assertTrue($this->post->expires_at->between(now()->addDays($days)->subMinute(), now()->addDays($days)->addMinute()));
    }

    public function test_expire_command_archives_only_listings_past_their_date(): void
    {
        $old = $this->makePost(['title' => 'Eski', 'expires_at' => now()->subDay()]);
        $sold = $this->makePost(['title' => 'Sotilgan', 'status' => 'sold', 'expires_at' => now()->subDay()]);

        $this->artisan('posts:expire')->assertSuccessful();

        $this->assertSame('archived', $old->fresh()->status);
        $this->assertSame('sold', $sold->fresh()->status);
        $this->assertSame('active', $this->post->fresh()->status);
    }

    public function test_owner_renews_an_archived_listing(): void
    {
        $old = $this->makePost(['status' => 'archived', 'expires_at' => now()->subDays(3)]);

        $this->actingAs($this->seller)->post(route('posts.renew', $old))->assertRedirect();

        $old->refresh();
        $this->assertSame('active', $old->status);
        $this->assertTrue($old->expires_at->isFuture());
    }

    public function test_renewal_is_for_the_owner_and_not_for_sold_or_fresh_listings(): void
    {
        $archived = $this->makePost(['status' => 'archived']);
        $this->actingAs($this->stranger)->post(route('posts.renew', $archived))->assertForbidden();

        $sold = $this->makePost(['status' => 'sold']);
        $this->actingAs($this->seller)->post(route('posts.renew', $sold))->assertRedirect()->assertSessionHas('error');
        $this->assertSame('sold', $sold->fresh()->status);

        // a listing with plenty of time left has nothing to renew
        $this->actingAs($this->seller)->post(route('posts.renew', $this->post))->assertSessionHas('error');
    }

    public function test_expiring_listing_offers_renewal_and_shows_days_left(): void
    {
        $this->post->update(['expires_at' => now()->addDays(3)]);

        $this->actingAs($this->seller)->get(route('posts.mine'))->assertOk()->assertSee('kun qoldi');
        $this->actingAs($this->seller)->get(route('posts.show', $this->post))->assertSee(route('posts.renew', $this->post), false);
    }

    public function test_archive_page_offers_to_renew_archived_but_not_sold(): void
    {
        $archived = $this->makePost(['title' => 'Arxivdagi', 'status' => 'archived']);
        $this->makePost(['title' => 'Sotilgan', 'status' => 'sold']);

        $html = $this->actingAs($this->seller)->get(route('admin.archive.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, route('posts.renew', $archived)));
        $this->assertSame(1, preg_match_all('#/posts/\d+/renew#', $html));
    }

    // ---- reports ----

    public function test_signed_in_user_reports_a_listing_once(): void
    {
        $this->actingAs($this->buyer)
            ->post(route('reports.post', $this->post), ['reason' => 'fraud', 'comment' => 'Oldindan pul so\'radi'])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('reports', ['reporter_id' => $this->buyer->id, 'post_id' => $this->post->id, 'reason' => 'fraud', 'status' => 'open']);

        $this->post(route('reports.post', $this->post), ['reason' => 'spam'])->assertSessionHas('info');
        $this->assertSame(1, Report::count());
    }

    public function test_report_rules(): void
    {
        $this->post(route('reports.post', $this->post), ['reason' => 'fraud'])->assertRedirect(route('login'));

        $this->actingAs($this->seller)->post(route('reports.post', $this->post), ['reason' => 'fraud'])->assertForbidden();
        $this->actingAs($this->seller)->post(route('reports.user', $this->seller), ['reason' => 'fraud'])->assertForbidden();

        $this->actingAs($this->buyer)->post(route('reports.post', $this->post), ['reason' => 'nonsense'])->assertSessionHasErrors('reason');
        $this->actingAs($this->buyer)->post(route('reports.post', $this->post), ['reason' => 'other'])->assertSessionHasErrors('comment');
        $this->assertSame(0, Report::count());
    }

    public function test_user_can_be_reported_from_the_seller_page(): void
    {
        $this->actingAs($this->buyer)
            ->get(route('sellers.show', $this->seller))
            ->assertSee(route('reports.user', $this->seller), false);

        $this->post(route('reports.user', $this->seller), ['reason' => 'fake'])->assertSessionHas('success');
        $this->assertDatabaseHas('reports', ['reported_user_id' => $this->seller->id, 'post_id' => null]);
    }

    public function test_report_button_is_not_shown_to_the_owner(): void
    {
        $this->actingAs($this->seller)->get(route('posts.show', $this->post))->assertDontSee('reportPost');
        $this->actingAs($this->buyer)->get(route('posts.show', $this->post))->assertSee('reportPost');
    }

    public function test_only_the_admin_reads_and_handles_reports(): void
    {
        $report = Report::create(['reporter_id' => $this->buyer->id, 'post_id' => $this->post->id, 'reason' => 'fraud']);

        $this->actingAs($this->buyer)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($this->buyer)->post(route('reports.resolve', $report))->assertForbidden();

        $this->actingAs($this->admin)->get(route('reports.index'))->assertOk()->assertSee('Firibgarlik shubhasi')->assertSee('Britan mushugi');
        $this->get('/')->assertSee('aria-label="Shikoyatlar"', false);
    }

    public function test_admin_dismisses_and_resolves(): void
    {
        $a = Report::create(['reporter_id' => $this->buyer->id, 'post_id' => $this->post->id, 'reason' => 'spam']);
        $b = Report::create(['reporter_id' => $this->stranger->id, 'reported_user_id' => $this->seller->id, 'reason' => 'fake']);

        $this->actingAs($this->admin);
        $this->post(route('reports.dismiss', $a))->assertRedirect();
        $this->post(route('reports.resolve', $b))->assertRedirect();

        $this->assertSame('dismissed', $a->fresh()->status);
        $this->assertSame('resolved', $b->fresh()->status);
        $this->assertSame($this->admin->id, $a->fresh()->handled_by);
        $this->get(route('reports.index', ['status' => 'dismissed']))->assertOk()->assertSee('Britan mushugi');
    }

    public function test_hiding_a_reported_listing_rejects_it_and_closes_its_reports(): void
    {
        $a = Report::create(['reporter_id' => $this->buyer->id, 'post_id' => $this->post->id, 'reason' => 'fraud']);
        $b = Report::create(['reporter_id' => $this->stranger->id, 'post_id' => $this->post->id, 'reason' => 'fake']);

        $this->actingAs($this->admin)->post(route('reports.hide-post', $a))->assertRedirect();

        $this->post->refresh();
        $this->assertSame('rejected', $this->post->moderation_status);
        $this->assertStringContainsString('Firibgarlik shubhasi', $this->post->moderation_reason);
        $this->assertSame('resolved', $a->fresh()->status);
        $this->assertSame('resolved', $b->fresh()->status);

        auth()->logout();
        $this->get(route('posts.show', $this->post))->assertNotFound();
    }
}
