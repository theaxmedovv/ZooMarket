<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chat;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The buyer can take a purchase request back, and the request flow holds up against double and stale actions.
 */
class PurchaseRequestCancelTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $buyer;
    protected User $other;
    protected User $third;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        foreach (['seller', 'buyer', 'other', 'third'] as $role) {
            $this->{$role} = User::factory()->create();
            $this->{$role}->assignRole('user');
        }

        $this->post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => Category::create(['name' => 'Parrots'])->id,
            'title' => "To'tiqushlar",
            'content' => 'x',
            'breed' => 'Ara',
            'quantity' => 3,
            'gender' => 'mixed',
            'male_quantity' => 2,
            'female_quantity' => 1,
            'age' => '1 yosh',
            'price' => 500000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Chilonzor',
            'status' => 'active',
            'moderation_status' => 'approved',
        ]);
    }

    private function request(User $buyer, int $male, int $female): PurchaseRequest
    {
        $this->actingAs($buyer)->post(route('purchase-requests.store'), ['animal_id' => $this->post->id, 'male_quantity' => $male, 'female_quantity' => $female])
            ->assertSessionHas('success');

        return PurchaseRequest::where('user_id', $buyer->id)->latest('id')->firstOrFail();
    }

    private function approve(PurchaseRequest $request): void
    {
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $request))->assertSessionHasNoErrors();
    }

    public function test_buyer_can_cancel_a_waiting_request_and_the_animals_return_to_the_listing(): void
    {
        $request = $this->request($this->buyer, 2, 1);
        $this->assertSame('reserved', $this->post->fresh()->status, 'Everything is reserved');

        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $request->fresh()->status);
        $post = $this->post->fresh();
        $this->assertSame(2, $post->male_quantity);
        $this->assertSame(1, $post->female_quantity);
        $this->assertSame(3, $post->quantity);
        $this->assertSame('active', $post->status, 'Back on sale');
    }

    public function test_cancelling_an_approved_request_also_closes_the_chat(): void
    {
        $request = $this->request($this->buyer, 1, 0);
        $this->approve($request);
        $chat = Chat::where('purchase_request_id', $request->id)->firstOrFail();
        $this->assertNull($chat->closed_at);

        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request))->assertSessionHas('success');

        $this->assertSame('cancelled', $request->fresh()->status);
        $this->assertNotNull($chat->fresh()->closed_at);
        $this->assertSame(2, $this->post->fresh()->male_quantity);
    }

    public function test_a_sold_request_cannot_be_cancelled(): void
    {
        $request = $this->request($this->buyer, 1, 0);
        $this->approve($request);
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.mark-sold', $request));

        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request))->assertSessionHasErrors('purchase_request');

        $this->assertSame('sold', $request->fresh()->status);
        $this->assertSame(1, $this->post->fresh()->male_quantity, 'Stock stays deducted');
    }

    public function test_cancelling_twice_does_not_return_the_animals_twice(): void
    {
        $request = $this->request($this->buyer, 1, 0);

        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request));
        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request))->assertSessionHas('success');

        $this->assertSame(2, $this->post->fresh()->male_quantity);
        $this->assertSame(3, $this->post->fresh()->quantity);
    }

    public function test_only_the_buyer_can_cancel_their_request(): void
    {
        $request = $this->request($this->buyer, 1, 0);

        $this->actingAs($this->other)->post(route('user.purchase-requests.cancel', $request))->assertForbidden();
        $this->actingAs($this->seller)->post(route('user.purchase-requests.cancel', $request))->assertForbidden();
        auth()->logout();
        $this->post(route('user.purchase-requests.cancel', $request))->assertRedirect(route('login'));

        $this->assertSame('pending', $request->fresh()->status);
    }

    public function test_seller_cannot_act_on_a_cancelled_request(): void
    {
        $request = $this->request($this->buyer, 1, 0);
        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request));

        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $request))->assertSessionHasErrors('purchase_request');
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.mark-sold', $request))->assertSessionHasErrors('purchase_request');
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.reject', $request))->assertSessionHasErrors('purchase_request');

        $this->assertSame('cancelled', $request->fresh()->status);
        $this->assertNull(Chat::where('purchase_request_id', $request->id)->first(), 'No chat for a cancelled request');
        $this->assertSame(3, $this->post->fresh()->quantity, 'Stock is not touched again');
    }

    public function test_seller_cannot_approve_a_rejected_or_sold_request(): void
    {
        $rejected = $this->request($this->buyer, 1, 0);
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.reject', $rejected));
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $rejected))->assertSessionHasErrors('purchase_request');
        $this->assertSame('rejected', $rejected->fresh()->status);

        $sold = $this->request($this->other, 1, 0);
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.mark-sold', $sold));
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $sold))->assertSessionHasErrors('purchase_request');
        $this->assertSame('sold', $sold->fresh()->status);
        $this->assertNull(Chat::where('purchase_request_id', $sold->id)->first());
    }

    public function test_a_buyer_cannot_stack_requests_on_the_same_listing(): void
    {
        $this->request($this->buyer, 1, 0);

        $this->actingAs($this->buyer)->post(route('purchase-requests.store'), ['animal_id' => $this->post->id, 'male_quantity' => 1])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(1, PurchaseRequest::where('user_id', $this->buyer->id)->count());
        $this->assertSame(1, $this->post->fresh()->male_quantity, 'Only the first request reserved stock');
    }

    public function test_after_cancelling_the_buyer_can_ask_again(): void
    {
        $first = $this->request($this->buyer, 1, 0);
        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $first));

        $second = $this->request($this->buyer, 2, 0);

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('pending', $second->status);
    }

    public function test_another_buyer_can_still_ask_while_one_request_is_open(): void
    {
        $this->request($this->buyer, 1, 0);
        $this->request($this->other, 1, 0);

        $this->assertSame(2, PurchaseRequest::count());
    }

    public function test_orders_page_offers_cancel_only_for_open_requests(): void
    {
        $pending = $this->request($this->buyer, 1, 0);
        $approved = $this->request($this->other, 1, 0);
        $this->approve($approved);
        $sold = $this->request($this->third, 0, 1);
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.mark-sold', $sold));
        $this->assertSame('sold', $sold->fresh()->status);

        $html = $this->actingAs($this->buyer)->get(route('user.purchase-requests.index'))->assertOk()->getContent();
        $this->assertStringContainsString(route('user.purchase-requests.cancel', $pending), $html);
        $this->assertStringContainsString("So'rovni bekor qilish", $html);
        $this->assertStringNotContainsString(route('user.purchase-requests.cancel', $approved), $html, "Someone else's request has no button");

        $other = $this->actingAs($this->other)->get(route('user.purchase-requests.index'))->getContent();
        $this->assertStringContainsString(route('user.purchase-requests.cancel', $approved), $other, 'Approved requests can be cancelled too');

        $third = $this->actingAs($this->third)->get(route('user.purchase-requests.index'))->getContent();
        $this->assertStringNotContainsString(route('user.purchase-requests.cancel', $sold), $third, 'Sold ones cannot');
    }

    public function test_cancelled_requests_have_their_own_tab_and_label(): void
    {
        $request = $this->request($this->buyer, 1, 0);
        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request));

        $this->actingAs($this->buyer)->get(route('user.purchase-requests.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee('Bekor qilingan')
            ->assertSee("Siz bu so'rovni bekor qildingiz")
            ->assertDontSee("So'rovni bekor qilish");

        $this->actingAs($this->seller)->get(route('admin.purchase-requests.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee('Bekor qilingan')
            ->assertDontSee('Tasdiqlash');
    }

    public function test_listing_page_and_card_show_that_a_request_was_sent(): void
    {
        $this->request($this->buyer, 1, 0);

        $page = $this->actingAs($this->buyer)->get(route('posts.show', $this->post))->assertOk()->getContent();
        $this->assertStringContainsString("So'rov yuborilgan", $page);
        $this->assertStringNotContainsString('id="buyModalDetail"', $page, 'No second request from here');
        $pending = PurchaseRequest::where('user_id', $this->buyer->id)->firstOrFail();
        $this->assertStringContainsString(route('user.purchase-requests.cancel', $pending), $page, 'Cancel button on the listing page');

        $card = $this->actingAs($this->buyer)->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString("So'rov yuborilgan", $card);
        $this->assertStringNotContainsString('#buy"', $card);
        $this->assertStringContainsString(route('user.purchase-requests.cancel', $pending), $card, 'Cancel button on the card');

        // Someone who has not asked still sees the normal button
        $this->actingAs($this->other)->get(route('home'))->assertSee('#buy"', false);
        $this->actingAs($this->other)->get(route('posts.show', $this->post))->assertSee('id="buyModalDetail"', false);

        // After cancelling, the button is back
        $request = PurchaseRequest::firstOrFail();
        $this->actingAs($this->buyer)->post(route('user.purchase-requests.cancel', $request));
        $this->actingAs($this->buyer)->get(route('home'))->assertSee('#buy"', false)->assertDontSee("So'rov yuborilgan");
    }

    public function test_guest_returns_to_the_listing_after_registering_or_logging_in(): void
    {
        $path = route('posts.show', $this->post, false);

        $this->get(route('posts.show', $this->post))->assertSee('next=' . urlencode($path), false);
        $this->get(route('posts.show', $this->post))->assertSee(route('login', ['next' => $path]), false);
        $this->get(route('home'))->assertDontSee('next=', false);

        $this->get(route('register', ['next' => $path]));
        $this->post(route('register.post'), ['name' => 'Yangi', 'email' => 'new@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])
            ->assertRedirect($path);

        auth()->logout();
        $this->get(route('login', ['next' => 'https://evil.test']));
        $this->post(route('login.post'), ['email' => $this->buyer->email, 'password' => 'password'])->assertRedirect(route('home', [], false));
    }
}
