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

    /**
     * Test Requirement 1: Partial sale moves multi-animal listing to Archive,
     * keeps all buyer/gender/quantity/order info, and rejects other pending requests.
     */
    public function test_partial_sale_moves_listing_to_archive_and_rejects_other_requests(): void
    {
        // 10 animals in one listing
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'To\'tiqushlar to\'dasi',
            'content' => 'Chiroyli to\'tiqushlar',
            'breed' => 'Ara',
            'quantity' => 10,
            'gender' => 'mixed',
            'male_quantity' => 6,
            'female_quantity' => 4,
            'age' => '1 yosh',
            'price' => 500000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        // Buyer 1 requests 1 male animal
        $req1 = PurchaseRequest::create([
            'user_id' => $this->buyer1->id,
            'animal_id' => $post->id,
            'gender' => 'male',
            'quantity' => 1,
            'status' => 'approved',
        ]);

        $chat1 = Chat::create([
            'purchase_request_id' => $req1->id,
            'post_id' => $post->id,
            'buyer_id' => $this->buyer1->id,
            'seller_id' => $this->seller->id,
        ]);

        // Buyer 2 has a pending request for 2 female animals
        $req2 = PurchaseRequest::create([
            'user_id' => $this->buyer2->id,
            'animal_id' => $post->id,
            'gender' => 'female',
            'quantity' => 2,
            'status' => 'pending',
        ]);

        // Seller marks Buyer 1's request as sold
        $response = $this->actingAs($this->seller)
            ->post(route('admin.purchase-requests.mark-sold', $req1));

        $response->assertRedirect();

        $post->refresh();
        $req1->refresh();
        $req2->refresh();
        $chat1->refresh();

        // 1. Post must be moved to Archive (status = sold) even though 9 animals remained
        $this->assertSame('sold', $post->status);
        $this->assertTrue($post->isArchived());

        // 2. Buyer 1 request is sold
        $this->assertSame('sold', $req1->status);

        // 3. Buyer 2 request is automatically rejected because listing is now archived/sold
        $this->assertSame('rejected', $req2->status);

        // 4. Chat is closed/deactivated
        $this->assertTrue($chat1->isClosed());
        $this->assertNotNull($chat1->closed_at);

        // 5. Sending new messages in closed chat is blocked
        $msgResponse = $this->actingAs($this->buyer1)
            ->post(route('chats.messages.store', $chat1), [
                'body' => 'Hello, can I still buy?',
            ]);
        $msgResponse->assertSessionHasErrors('body');
        $this->assertDatabaseMissing('messages', ['body' => 'Hello, can I still buy?']);
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
        $response->assertSee('Read-only');

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
