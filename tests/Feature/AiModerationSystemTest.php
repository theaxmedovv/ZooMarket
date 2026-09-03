<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiModerationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $buyer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions and roles
        $permissions = [
            'create posts',
            'read posts',
            'edit posts',
            'delete posts',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $sellerRole = Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        $sellerRole->syncPermissions($permissions);

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions(['read posts']);

        $this->seller = User::factory()->create(['name' => 'Seller Alice', 'email' => 'seller@test.com']);
        $this->seller->syncRoles(['seller']);

        $this->buyer = User::factory()->create(['name' => 'Buyer Bob', 'email' => 'buyer@test.com']);
        $this->buyer->syncRoles(['user']);

        $this->category = Category::create(['name' => 'Cats']);

        Config::set('services.groq.key', 'gsk_test_mock_api_key_12345');
    }

    /**
     * Test 1: User creates safe listing -> Groq approves -> Published to marketplace.
     */
    public function test_post_creation_is_moderated_and_approved_by_groq_ai(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'status' => 'approved',
                                'reason' => null,
                                'flags' => [],
                                'confidence' => 0.98,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Britaniya zotli sog\'lom mushukcha',
            'category_id' => $this->category->id,
            'breed' => 'British Shorthair',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '3 oylik',
            'color' => 'Kulrang',
            'description' => 'Sog\'lom, barcha emlashlari qilingan mehribon mushukcha.',
            'price' => 500000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        $post = Post::latest('id')->first();

        $this->assertNotNull($post);
        $this->assertEquals('approved', $post->moderation_status);
        $this->assertNull($post->moderation_reason);
        $this->assertNotNull($post->moderated_at);

        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success');

        // Verify public marketplace visibility for buyers
        $publicResponse = $this->actingAs($this->buyer)->get(route('posts.index'));
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Britaniya zotli sog\'lom mushukcha');
    }

    /**
     * Test 2: User creates violating listing -> Groq rejects -> Not published + reason stored.
     */
    public function test_post_creation_is_moderated_and_rejected_by_groq_ai(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'status' => 'rejected',
                                'reason' => 'Qizil kitobga kiritilgan yovvoyi hayvonlarni sotish taqiqlanadi.',
                                'flags' => ['prohibited_species'],
                                'confidence' => 0.99,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Noyob Qor bars bolasi',
            'category_id' => $this->category->id,
            'breed' => 'Snow Leopard',
            'quantity' => 1,
            'gender' => 'female',
            'age' => '1 oylik',
            'color' => 'Oq-qora',
            'description' => 'Tog\'dan olib kelingan yovvoyi bars bolasi.',
            'price' => 50000000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        $post = Post::latest('id')->first();

        $this->assertNotNull($post);
        $this->assertEquals('rejected', $post->moderation_status);
        $this->assertEquals('Qizil kitobga kiritilgan yovvoyi hayvonlarni sotish taqiqlanadi.', $post->moderation_reason);

        $response->assertRedirect(route('posts.show', $post));
        $response->assertSessionHas('warning');

        // Verify HIDDEN from public marketplace
        $publicMarketResponse = $this->actingAs($this->buyer)->get(route('posts.index'));
        $publicMarketResponse->assertStatus(200);
        $publicMarketResponse->assertDontSee('Noyob Qor bars bolasi');

        // Verify public buyer cannot access direct link (404)
        $buyerDetailResponse = $this->actingAs($this->buyer)->get(route('posts.show', $post));
        $buyerDetailResponse->assertStatus(404);

        // Verify seller CAN view their own rejected post with the rejection reason
        $sellerDetailResponse = $this->actingAs($this->seller)->get(route('posts.show', $post));
        $sellerDetailResponse->assertStatus(200);
        $sellerDetailResponse->assertSee('Groq AI moderatsiyasidan', false);
        $sellerDetailResponse->assertSee('Qizil kitobga kiritilgan yovvoyi hayvonlarni sotish taqiqlanadi.', false);
    }

    /**
     * Test 3: Seller can edit rejected post to fix it -> Groq re-moderates and approves -> Now visible.
     */
    public function test_seller_can_edit_and_resubmit_rejected_post(): void
    {
        // Initially rejected post
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Noma\'lum mahsulot',
            'description' => 'Avtomobil ehtiyot qismi',
            'content' => 'Avtomobil ehtiyot qismi',
            'breed' => 'Yo\'q',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '1',
            'price' => 100000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'rejected',
            'moderation_reason' => 'E\'lon hayvonga tegishli emas.',
        ]);

        // Seller opens edit page and sees previous rejection reason
        $editPageResponse = $this->actingAs($this->seller)->get(route('posts.edit', $post));
        $editPageResponse->assertStatus(200);
        $editPageResponse->assertSee('avval Groq AI tomonidan rad etilgan', false);
        $editPageResponse->assertSee('hayvonga tegishli emas', false);

        // Now seller corrects the post and updates it
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'status' => 'approved',
                                'reason' => null,
                                'flags' => [],
                                'confidence' => 0.95,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $updateResponse = $this->actingAs($this->seller)->put(route('posts.update', $post), [
            'title' => 'Fors mushugi (tozalangan e\'lon)',
            'category_id' => $this->category->id,
            'breed' => 'Persian',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '6 oylik',
            'color' => 'Oq',
            'description' => 'Haqiqiy toza qonli fors mushugi, barcha hujjatlari bor.',
            'price' => 1200000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        $post->refresh();
        $this->assertEquals('approved', $post->moderation_status);
        $this->assertNull($post->moderation_reason);

        // Now visible to buyers on marketplace
        $marketplaceResponse = $this->actingAs($this->buyer)->get(route('posts.index'));
        $marketplaceResponse->assertSee('Fors mushugi', false);
    }

    /**
     * Test 4: Pending post remains hidden from public marketplace and buyers.
     */
    public function test_pending_post_is_hidden_from_public(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Tekshiruvdagi quyoncha',
            'description' => 'Yangi tug\'ilgan quyoncha',
            'content' => 'Yangi tug\'ilgan quyoncha',
            'breed' => 'Kaliforniya',
            'quantity' => 1,
            'gender' => 'female',
            'age' => '2 oylik',
            'price' => 80000,
            'currency' => 'UZS',
            'location' => 'Samarqand',
            'status' => 'active',
            'moderation_status' => 'pending',
        ]);

        // Buyer cannot see on index
        $buyerIndex = $this->actingAs($this->buyer)->get(route('posts.index'));
        $buyerIndex->assertDontSee('Tekshiruvdagi quyoncha');

        // Buyer cannot view show page
        $buyerShow = $this->actingAs($this->buyer)->get(route('posts.show', $post));
        $buyerShow->assertStatus(404);

        // Seller CAN see with pending status
        $sellerShow = $this->actingAs($this->seller)->get(route('posts.show', $post));
        $sellerShow->assertStatus(200);
        $sellerShow->assertSee('AI moderatsiyasida', false);
    }

    /**
     * Test 5: Cannot purchase or like an unapproved post.
     */
    public function test_cannot_purchase_or_like_unapproved_post(): void
    {
        $rejectedPost = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Rad etilgan post',
            'description' => 'Test',
            'content' => 'Test',
            'breed' => 'Test',
            'quantity' => 2,
            'gender' => 'male',
            'age' => '1',
            'price' => 10000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'rejected',
        ]);

        // Trying to like should return 404
        $likeResponse = $this->actingAs($this->buyer)->post(route('posts.like', $rejectedPost));
        $likeResponse->assertStatus(404);

        // Trying to create purchase request should fail
        $purchaseResponse = $this->actingAs($this->buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $rejectedPost->id,
            'quantity' => 1,
            'gender' => 'male',
        ]);

        $purchaseResponse->assertSessionHasErrors(['quantity']);
    }
}
