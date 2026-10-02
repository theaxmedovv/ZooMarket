<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListingArchiveAndChatManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $buyer1;
    protected User $buyer2;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $this->seller = User::factory()->create(['name' => 'Seller John', 'email' => 'seller@example.com']);
        $this->seller->assignRole('seller');

        $this->buyer1 = User::factory()->create(['name' => 'Buyer Alice', 'email' => 'alice@example.com', 'phone' => '+998901234567']);
        $this->buyer1->assignRole('user');

        $this->buyer2 = User::factory()->create(['name' => 'Buyer Bob', 'email' => 'bob@example.com']);
        $this->buyer2->assignRole('user');

        $this->category = Category::create(['name' => 'Parrots']);
    }

    private function makeListing(int $male, int $female): Post
    {
        return Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'To\'tiqushlar to\'dasi',
            'content' => 'Chiroyli to\'tiqushlar',
            'breed' => 'Ara',
            'quantity' => $male + $female,
            'gender' => 'mixed',
            'male_quantity' => $male,
            'female_quantity' => $female,
            'age' => '1 yosh',
            'price' => 500000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);
    }

    private function requestAnimals(User $buyer, Post $post, int $male, int $female): PurchaseRequest
    {
        $this->actingAs($buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $post->id,
            'male_quantity' => $male,
            'female_quantity' => $female,
        ])->assertSessionHas('success');

        return PurchaseRequest::where('user_id', $buyer->id)->latest('id')->firstOrFail();
    }

    /**
     * A partial sale finalizes only the selected animals: the rest stays in the
     * listing, other buyers' requests and chats are untouched.
     */
    public function test_partial_sale_keeps_remaining_animals_in_listing(): void
    {
        $post = $this->makeListing(6, 4);

        $req1 = $this->requestAnimals($this->buyer1, $post, 1, 2);
        $req2 = $this->requestAnimals($this->buyer2, $post, 0, 2);

        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $req1));
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $req2));
        $chat1 = $req1->fresh()->chat;
        $chat2 = $req2->fresh()->chat;

        $this->actingAs($this->seller)
            ->post(route('admin.purchase-requests.mark-sold', $req1))
            ->assertRedirect();

        $post->refresh();

        // Remaining animals stay on sale
        $this->assertSame('active', $post->status);
        $this->assertFalse($post->isArchived());
        $this->assertSame(5, $post->male_quantity);
        $this->assertSame(0, $post->female_quantity);
        $this->assertSame(5, $post->quantity);

        // Only the sold request is finalized and its chat closed
        $this->assertSame('sold', $req1->fresh()->status);
        $this->assertTrue($chat1->fresh()->isClosed());
        $this->actingAs($this->buyer1)
            ->post(route('chats.messages.store', $chat1), ['body' => 'Hello, can I still buy?'])
            ->assertSessionHasErrors('body');
        $this->assertDatabaseMissing('messages', ['body' => 'Hello, can I still buy?']);

        // The other buyer keeps their request and can keep arranging details in chat
        $this->assertSame('approved', $req2->fresh()->status);
        $this->assertFalse($chat2->fresh()->isClosed());
        $this->actingAs($this->buyer2)
            ->post(route('chats.messages.store', $chat2), ['body' => 'Qachon olib ketsam bo\'ladi?'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('messages', ['body' => 'Qachon olib ketsam bo\'ladi?']);
    }

    /**
     * The listing is archived only once every animal has been sold.
     */
    public function test_listing_is_archived_only_when_remaining_quantity_reaches_zero(): void
    {
        $post = $this->makeListing(1, 2);

        $req1 = $this->requestAnimals($this->buyer1, $post, 1, 1);
        $req2 = $this->requestAnimals($this->buyer2, $post, 0, 1);

        // All animals reserved, but nothing sold yet: listing is not archived
        $post->refresh();
        $this->assertSame(0, $post->quantity);
        $this->assertSame('reserved', $post->status);
        $this->assertFalse($post->isArchived());

        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $req1));
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.mark-sold', $req1));

        // Buyer 2 still holds a reservation
        $post->refresh();
        $this->assertFalse($post->isArchived());
        $this->assertSame('pending', $req2->fresh()->status);

        $this->actingAs($this->seller)->post(route('admin.purchase-requests.approve', $req2));
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.mark-sold', $req2));

        $post->refresh();
        $this->assertSame('sold', $post->status);
        $this->assertTrue($post->isArchived());
        $this->assertSame(2, PurchaseRequest::where('animal_id', $post->id)->where('status', 'sold')->count());
    }

    /**
     * Test Requirement 1 & 4: Archive view shows full buyer, quantity, gender, order info, and NO restore button.
     */
    public function test_archive_view_shows_complete_transaction_info_and_no_restore_button(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Noyob to\'tiqush',
            'content' => 'Tavsif',
            'breed' => 'Kakadu',
            'quantity' => 5,
            'gender' => 'male',
            'male_quantity' => 5,
            'female_quantity' => 0,
            'age' => '2 yosh',
            'price' => 1200000,
            'currency' => 'UZS',
            'location' => 'Samarqand',
            'status' => 'sold',
        ]);

        $req = PurchaseRequest::create([
            'user_id' => $this->buyer1->id,
            'animal_id' => $post->id,
            'gender' => 'male',
            'quantity' => 2,
            'status' => 'sold',
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('admin.archive.index'));

        $response->assertStatus(200);
        $response->assertSee('Noyob to\'tiqush');
        $response->assertSee('Buyer Alice');
        $response->assertSee('+998901234567');
        $response->assertSee('2 ta sotildi');
        $response->assertSee('Erkak ♂');
        $response->assertSee('2 400 000'); // 2 * 1 200 000
        $response->assertSee('REQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee("Faqat ko'rish", false);

        // Confirm there is NO "Qayta tiklash" (restore) button in HTML
        $response->assertDontSee('Qayta tiklash');
        $response->assertDontSee('admin.posts.restore');
    }

    /**
     * Test Requirement 2: Deleted listing updates active requests to rejected and synchronizes statuses.
     */
    public function test_deleting_listing_updates_active_requests_to_rejected(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'O\'chiriladigan e\'lon',
            'content' => 'Tavsif',
            'breed' => 'Volnistiy',
            'quantity' => 3,
            'gender' => 'female',
            'male_quantity' => 0,
            'female_quantity' => 3,
            'age' => '6 oylik',
            'price' => 100000,
            'currency' => 'UZS',
            'location' => 'Buxoro',
            'status' => 'active',
        ]);

        $req = PurchaseRequest::create([
            'user_id' => $this->buyer1->id,
            'animal_id' => $post->id,
            'gender' => 'female',
            'quantity' => 1,
            'status' => 'pending',
        ]);

        // Seller deletes the post
        $response = $this->actingAs($this->seller)
            ->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));

        $req->refresh();
        $this->assertSame('rejected', $req->status);

        // Soft-deleted post still exists in DB so history is not destroyed
        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        // And in buyer requests index, it appears under rejected
        $buyerIndexResponse = $this->actingAs($this->buyer1)
            ->get(route('user.purchase-requests.index', ['status' => 'rejected']));

        $buyerIndexResponse->assertStatus(200);
        $buyerIndexResponse->assertSee($post->title);
        $buyerIndexResponse->assertSee("E'lon sotuvchi tomonidan olib tashlangan", false);
    }

    /**
     * Test Requirement 4: Archived/Sold listings cannot be edited, deleted, or restored.
     */
    public function test_archived_or_sold_listings_cannot_be_edited_or_deleted(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Arxivdagi qush',
            'content' => 'Tavsif',
            'breed' => 'Korella',
            'quantity' => 1,
            'gender' => 'male',
            'male_quantity' => 1,
            'female_quantity' => 0,
            'age' => '1 yosh',
            'price' => 300000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'sold',
        ]);

        // Edit page must return 403
        $this->actingAs($this->seller)
            ->get(route('posts.edit', $post))
            ->assertStatus(403);

        // Update action must return 403
        $this->actingAs($this->seller)
            ->put(route('posts.update', $post), [
                'title' => 'Updated title',
            ])
            ->assertStatus(403);

        // Delete action must fail with error
        $this->actingAs($this->seller)
            ->delete(route('posts.destroy', $post))
            ->assertSessionHasErrors('error');

        // Post remains in database and status remains sold
        $post->refresh();
        $this->assertSame('sold', $post->status);
        $this->assertFalse($post->trashed());
    }
}
