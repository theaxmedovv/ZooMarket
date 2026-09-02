<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnimalQuantityAndGenderTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $buyer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $this->seller = User::factory()->create();
        $this->seller->assignRole('seller');

        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('user');

        $this->category = Category::create([
            'name' => 'Mushuklar',
        ]);
    }

    public function test_seller_can_create_single_animal_listing_with_male_or_female(): void
    {
        $response = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Britan mushugi',
            'description' => 'Sog\'lom va emlangan britan mushugi',
            'category_id' => $this->category->id,
            'breed' => 'British Shorthair',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '6 oy',
            'price' => 1500000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Britan mushugi',
            'quantity' => 1,
            'gender' => 'male',
            'male_quantity' => 1,
            'female_quantity' => 0,
        ]);
    }

    public function test_seller_cannot_create_single_animal_with_mixed_gender(): void
    {
        $response = $this->actingAs($this->seller)->from(route('posts.create'))->post(route('posts.store'), [
            'title' => 'Yakka mushuk',
            'category_id' => $this->category->id,
            'breed' => 'British',
            'quantity' => 1,
            'gender' => 'mixed',
            'male_quantity' => 1,
            'female_quantity' => 0,
            'age' => '1 yosh',
            'price' => 1000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['gender']);
    }

    public function test_seller_cannot_create_listing_with_quantity_out_of_range(): void
    {
        // Quantity 0
        $response0 = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Mushuklar',
            'category_id' => $this->category->id,
            'breed' => 'British',
            'quantity' => 0,
            'gender' => 'male',
            'age' => '1 yosh',
            'price' => 1000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);
        $response0->assertSessionHasErrors(['quantity']);

        // Quantity 101
        $response101 = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Mushuklar',
            'category_id' => $this->category->id,
            'breed' => 'British',
            'quantity' => 101,
            'gender' => 'male',
            'age' => '1 yosh',
            'price' => 1000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);
        $response101->assertSessionHasErrors(['quantity']);
    }

    public function test_seller_can_create_multiple_animals_with_mixed_gender_and_valid_split(): void
    {
        $response = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Kuchukvachchalar to\'plami',
            'description' => 'Ajoyib naslli kuchukvachchalar',
            'category_id' => $this->category->id,
            'breed' => 'Labrador',
            'quantity' => 10,
            'gender' => 'mixed',
            'male_quantity' => 4,
            'female_quantity' => 6,
            'age' => '2 oy',
            'price' => 2000000,
            'currency' => 'UZS',
            'location' => 'Samarqand',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Kuchukvachchalar to\'plami',
            'quantity' => 10,
            'gender' => 'mixed',
            'male_quantity' => 4,
            'female_quantity' => 6,
        ]);
    }

    public function test_seller_cannot_create_mixed_listing_when_split_does_not_equal_total(): void
    {
        $response = $this->actingAs($this->seller)->from(route('posts.create'))->post(route('posts.store'), [
            'title' => 'Kuchukvachchalar to\'plami',
            'category_id' => $this->category->id,
            'breed' => 'Labrador',
            'quantity' => 10,
            'gender' => 'mixed',
            'male_quantity' => 3,
            'female_quantity' => 5, // 3 + 5 = 8 !== 10
            'age' => '2 oy',
            'price' => 2000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['male_quantity']);
    }

    public function test_buyer_can_purchase_specific_gender_and_quantity_and_stock_decreases(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Labrador bolalari',
            'content' => 'Labrador bolalari haqida tavsif',
            'breed' => 'Labrador',
            'quantity' => 10,
            'gender' => 'mixed',
            'male_quantity' => 4,
            'female_quantity' => 6,
            'age' => '2 oy',
            'price' => 2000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $post->id,
            'gender' => 'male',
            'quantity' => 3,
        ]);

        $response->assertSessionHas('success');

        $post->refresh();
        $this->assertEquals(1, $post->male_quantity);
        $this->assertEquals(6, $post->female_quantity);
        $this->assertEquals(7, $post->quantity);
        $this->assertEquals('active', $post->status);

        $this->assertDatabaseHas('purchase_requests', [
            'user_id' => $this->buyer->id,
            'animal_id' => $post->id,
            'gender' => 'male',
            'quantity' => 3,
            'status' => 'pending',
        ]);
    }

    public function test_buyer_cannot_purchase_more_than_available_for_gender(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Mushukchalar',
            'content' => 'Mushukchalar haqida',
            'breed' => 'British',
            'quantity' => 5,
            'gender' => 'mixed',
            'male_quantity' => 2,
            'female_quantity' => 3,
            'age' => '1 oy',
            'price' => 1000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        // Attempt to buy 3 males when only 2 exist
        $response = $this->actingAs($this->buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $post->id,
            'gender' => 'male',
            'quantity' => 3,
        ]);

        $response->assertSessionHasErrors(['quantity']);

        $post->refresh();
        $this->assertEquals(2, $post->male_quantity);
        $this->assertEquals(5, $post->quantity);
    }

    public function test_buyer_cannot_purchase_mixed_gender(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Mushukchalar',
            'content' => 'Mushukchalar haqida',
            'breed' => 'British',
            'quantity' => 5,
            'gender' => 'mixed',
            'male_quantity' => 2,
            'female_quantity' => 3,
            'age' => '1 oy',
            'price' => 1000000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $post->id,
            'gender' => 'mixed',
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors(['gender']);
    }

    public function test_inventory_depletion_marks_post_as_sold(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Yakka kuchuk',
            'content' => 'Kuchuk haqida',
            'breed' => 'Ovcharka',
            'quantity' => 2,
            'gender' => 'male',
            'male_quantity' => 2,
            'female_quantity' => 0,
            'age' => '3 oy',
            'price' => 1500000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        $this->actingAs($this->buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $post->id,
            'gender' => 'male',
            'quantity' => 2,
        ]);

        $post->refresh();
        $this->assertEquals(0, $post->male_quantity);
        $this->assertEquals(0, $post->quantity);
        $this->assertEquals('sold', $post->status);
    }

    public function test_rejection_restores_inventory(): void
    {
        $post = Post::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Mushukchalar',
            'content' => 'Mushukchalar haqida',
            'breed' => 'Siam',
            'quantity' => 5,
            'gender' => 'mixed',
            'male_quantity' => 2,
            'female_quantity' => 3,
            'age' => '2 oy',
            'price' => 800000,
            'currency' => 'UZS',
            'status' => 'active',
        ]);

        $this->actingAs($this->buyer)->post(route('purchase-requests.store'), [
            'animal_id' => $post->id,
            'gender' => 'female',
            'quantity' => 2,
        ]);

        $post->refresh();
        $this->assertEquals(1, $post->female_quantity);
        $this->assertEquals(3, $post->quantity);

        $purchaseRequest = PurchaseRequest::first();

        // Seller rejects request
        $this->actingAs($this->seller)->post(route('admin.purchase-requests.reject', $purchaseRequest));

        $post->refresh();
        $this->assertEquals(3, $post->female_quantity);
        $this->assertEquals(5, $post->quantity);
        $this->assertEquals('active', $post->status);
    }
}
