<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\GroqModerationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A listing's location = a region (viloyat) chosen from a fixed list + the exact place typed by the owner.
 */
class ListingRegionTest extends TestCase
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

        // Moderation is not under test here
        $this->mock(GroqModerationService::class)->shouldReceive('moderate')->andReturn(['status' => 'pending', 'reason' => null]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Mushukcha',
            'category_id' => $this->category->id,
            'breed' => 'Britan',
            'quantity' => 1,
            'gender' => 'male',
            'age' => '3 oy',
            'description' => 'Chiroyli mushukcha',
            'price' => 100000,
            'currency' => 'UZS',
            'region' => 'Samarqand viloyati',
            'location' => 'Urgut tumani',
            'status' => 'active',
        ], $overrides);
    }

    public function test_the_region_list_has_the_twelve_regions(): void
    {
        $regions = config('zoomarket.regions');

        foreach (['Andijon', 'Buxoro', 'Jizzax', 'Qashqadaryo', 'Navoiy', 'Namangan', 'Samarqand', 'Surxondaryo', 'Sirdaryo', 'Toshkent', "Farg'ona", 'Xorazm'] as $name) {
            $this->assertContains("{$name} viloyati", $regions);
        }
    }

    public function test_create_form_offers_every_region_and_a_place_field(): void
    {
        $html = $this->actingAs($this->owner)->get(route('posts.create'))->assertOk()->getContent();

        $this->assertStringContainsString('name="region"', $html);
        $this->assertStringContainsString('name="location"', $html);
        foreach (config('zoomarket.regions') as $region) {
            $this->assertStringContainsString('value="' . e($region) . '"', $html);
        }
    }

    public function test_listing_is_saved_with_region_and_place(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload())->assertSessionHasNoErrors();

        $post = Post::firstOrFail();
        $this->assertSame('Samarqand viloyati', $post->region);
        $this->assertSame('Urgut tumani', $post->location);
        $this->assertSame('Urgut tumani, Samarqand viloyati', $post->fullLocation());
    }

    public function test_region_is_required_and_must_be_from_the_list(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload(['region' => '']))->assertSessionHasErrors('region');
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload(['region' => 'Atlantida viloyati']))->assertSessionHasErrors('region');
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload(['location' => '']))->assertSessionHasErrors('location');

        $this->assertSame(0, Post::count());
    }

    public function test_region_can_be_changed_when_editing(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload());
        $post = Post::firstOrFail();

        $this->actingAs($this->owner)->get(route('posts.edit', $post))->assertOk()->assertSee('Samarqand viloyati');

        $this->actingAs($this->owner)->put(route('posts.update', $post), $this->payload(['region' => 'Xorazm viloyati', 'location' => 'Urganch shahri']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Xorazm viloyati', $post->fresh()->region);

        $this->actingAs($this->owner)->put(route('posts.update', $post), $this->payload(['region' => '']))->assertSessionHasErrors('region');
    }

    public function test_listing_page_shows_place_and_region(): void
    {
        $post = Post::create($this->payload() + ['user_id' => $this->owner->id, 'content' => 'x', 'moderation_status' => 'approved', 'male_quantity' => 1, 'female_quantity' => 0]);

        $this->get(route('posts.show', $post))->assertOk()->assertSee('Urgut tumani, Samarqand viloyati');
    }

    public function test_filter_manzil_lists_only_regions_and_filters_by_them(): void
    {
        foreach ([['Samarqand viloyati', 'Urgut tumani'], ['Qashqadaryo viloyati', 'Qarshi shahri'], ['Qashqadaryo viloyati', 'Shahrisabz shahri']] as [$region, $place]) {
            Post::create($this->payload(['region' => $region, 'location' => $place, 'title' => "E'lon {$place}"]) + ['user_id' => $this->owner->id, 'content' => 'x', 'moderation_status' => 'approved', 'male_quantity' => 1, 'female_quantity' => 0]);
        }

        $html = $this->get(route('home', ['category_id' => $this->category->id]))->assertOk()->getContent();

        $this->assertStringContainsString('>Manzil</h3>', $html);
        $this->assertStringContainsString('name="region" value="Qashqadaryo viloyati"', $html);
        $this->assertStringContainsString('name="region" value="Samarqand viloyati"', $html);
        $this->assertStringNotContainsString('name="location"', $html);
        $this->assertStringNotContainsString('value="Qarshi shahri"', $html);

        // Choosing a region narrows the results to it and shows a chip
        $this->get(route('home', ['category_id' => $this->category->id, 'region' => 'Qashqadaryo viloyati']))
            ->assertOk()
            ->assertSee('Manzil: Qashqadaryo viloyati')
            ->assertSee('Qarshi shahri')
            ->assertDontSee('Urgut tumani');

        // Not a real region: ignored rather than an error
        $this->get(route('home', ['category_id' => $this->category->id, 'region' => 'Atlantida']))->assertOk()->assertSee('Urgut tumani');
    }
}
