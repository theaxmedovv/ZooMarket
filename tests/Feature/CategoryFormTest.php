<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\GroqModerationService;
use App\Support\ListingProfile;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The listing form depends on the chosen category (config/listing_types.php).
 */
class CategoryFormTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');

        $this->mock(GroqModerationService::class)->shouldReceive('moderate')->andReturn(['status' => 'pending', 'reason' => null]);
    }

    private function category(string $name): Category
    {
        return Category::where('name', $name)->firstOrFail();
    }

    private function payload(string $category, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sinov e\'loni',
            'category_id' => $this->category($category)->id,
            'breed' => 'Aralash',
            'quantity' => 2,
            'gender' => 'male',
            'age' => '1 yosh',
            'description' => 'Tavsif',
            'price' => 100000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Chilonzor',
            'status' => 'active',
        ], $overrides);
    }

    public function test_every_category_has_a_profile_and_valid_field_definitions(): void
    {
        foreach (Category::pluck('name') as $name) {
            $profile = ListingProfile::forName($name);

            foreach ($profile->fields() as $key => $field) {
                $this->assertArrayHasKey('label', $field, "{$name}.{$key}");
                $this->assertContains($field['type'], ['select', 'number', 'text'], "{$name}.{$key}");
                if ($field['type'] === 'select') {
                    $this->assertNotEmpty($field['options'], "{$name}.{$key}");
                }
                if ($field['type'] === 'number') {
                    $this->assertArrayHasKey('unit', $field, "{$name}.{$key}");
                }
            }
        }
    }

    public function test_form_renders_every_categories_question_group_and_its_wording(): void
    {
        $html = $this->actingAs($this->owner)->get(route('posts.create'))->assertOk()->getContent();

        $this->assertStringContainsString('data-listing-form', $html);
        foreach (['Nasabnomasi', 'Parvoz turi', 'Mos akvarium hajmi', 'Nima sotilmoqda', "Bo'g'ozligi", 'Rasmiy ruxsatnoma (CITES)'] as $text) {
            $this->assertStringContainsString(e($text), $html);
        }
        // The script gets each category's wording and switches
        $this->assertStringContainsString('&quot;breed_label&quot;:&quot;Ari zoti&quot;', $html);
        $this->assertStringContainsString('data-profile-extras="Asalarichilik"', $html);
    }

    public function test_a_dog_listing_saves_its_details_and_requires_the_vaccination_answer(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('It'))->assertSessionHasErrors('details.vaccinated');

        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('It', [
            'details' => ['vaccinated' => 'Ha', 'passport' => 'Bor', 'pedigree' => '', 'trained' => 'O\'tir, yot'],
        ]))->assertSessionHasNoErrors();

        $post = Post::firstOrFail();
        $this->assertSame(['vaccinated' => 'Ha', 'passport' => 'Bor', 'trained' => "O'tir, yot"], $post->details);
        $this->assertSame('Emlangan', $post->detailRows()[0]['label']);
    }

    public function test_livestock_needs_weight_and_purpose(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Qoramol'))
            ->assertSessionHasErrors(['details.weight_kg', 'details.purpose']);

        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Qoramol', [
            'details' => ['weight_kg' => '450.5', 'purpose' => 'Sut', 'milk_l' => '18'],
        ]))->assertSessionHasNoErrors();

        $this->assertSame('450.5 kg', Post::firstOrFail()->detailRows()[0]['value']);
    }

    public function test_detail_values_are_checked(): void
    {
        $bad = $this->payload('Qoramol', ['details' => ['weight_kg' => 'juda og\'ir', 'purpose' => 'Uchish uchun']]);

        $this->actingAs($this->owner)->post(route('posts.store'), $bad)->assertSessionHasErrors(['details.weight_kg', 'details.purpose']);
        $this->assertSame(0, Post::count());
    }

    public function test_details_of_other_categories_are_dropped(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Mushuk', [
            'details' => ['vaccinated' => 'Ha', 'weight_kg' => '450', 'hive_type' => 'Ruta'],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(['vaccinated' => 'Ha'], Post::firstOrFail()->details);
    }

    public function test_fish_are_not_asked_for_sex_or_age_and_are_counted_as_one_group(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Baliq', [
            'gender' => null, 'age' => null, 'quantity' => 12, 'color' => 'Qizil',
            'details' => ['water' => 'Chuchuk', 'size_cm' => '4'],
        ]))->assertSessionHasNoErrors();

        $post = Post::firstOrFail();
        $this->assertSame(12, $post->male_quantity);
        $this->assertSame(0, $post->female_quantity);
        $this->assertNull($post->age);
        $this->assertFalse($post->usesGender());
        $this->assertNull($post->fresh()->age);

        // The listing page shows a plain stock count, no sex and no age
        $html = $this->get(route('posts.show', $post->update(['moderation_status' => 'approved']) ? $post : $post))->assertOk()->getContent();
        $this->assertStringContainsString('Zaxira:', $html);
        $this->assertStringNotContainsString('Zaxira va jins', $html);
        $this->assertStringNotContainsString('bi-gender-ambiguous', $html);
        $this->assertStringNotContainsString('bi-calendar3 mr-1"></i> Yoshi', $html);
        $this->assertStringContainsString('Suv turi', $html);
        $this->assertStringContainsString('Mavjud: 12 ta', $html);
    }

    public function test_beekeeping_has_its_own_wording_and_no_sex_age_or_colour(): void
    {
        $profile = ListingProfile::forName('Asalarichilik');

        $this->assertSame('Ari zoti', $profile->breedLabel());
        $this->assertFalse($profile->usesGender());
        $this->assertFalse($profile->usesAge());
        $this->assertFalse($profile->usesColor());

        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Asalarichilik'))->assertSessionHasErrors('details.product');
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Asalarichilik', [
            'gender' => null, 'age' => null, 'color' => 'qora', 'details' => ['product' => 'Ari oilasi', 'honey_kg' => '12'],
        ]))->assertSessionHasNoErrors();

        $post = Post::firstOrFail();
        $this->assertNull($post->color, 'Colour does not apply');
        $this->assertSame(['product' => 'Ari oilasi', 'honey_kg' => '12'], $post->details);
    }

    public function test_sold_by_sex_categories_still_validate_gender_and_age(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Otlar', ['gender' => null, 'age' => null, 'details' => ['height_cm' => 150, 'purpose' => 'Poyga']]))
            ->assertSessionHasErrors(['gender', 'age']);
    }

    public function test_changing_the_category_when_editing_swaps_the_details(): void
    {
        $this->actingAs($this->owner)->post(route('posts.store'), $this->payload('Mushuk', ['details' => ['vaccinated' => 'Ha']]));
        $post = Post::firstOrFail();

        $edit = $this->actingAs($this->owner)->get(route('posts.edit', $post))->assertOk()->getContent();
        $this->assertStringContainsString('data-listing-form', $edit);
        $this->assertMatchesRegularExpression('#id="detail_mushuk_vaccinated"[^>]*>\s*<option value="">Tanlang</option>\s*<option value="Ha" selected#', $edit);

        $this->actingAs($this->owner)->put(route('posts.update', $post), $this->payload('Quyon', ['details' => ['purpose' => 'Go\'sht', 'vaccinated' => 'Yo\'q']]))
            ->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertSame($this->category('Quyon')->id, $post->category_id);
        $this->assertSame(['purpose' => "Go'sht", 'vaccinated' => "Yo'q"], $post->details);
    }

    public function test_listing_page_shows_the_category_details(): void
    {
        $post = Post::create($this->payload('Otlar') + [
            'user_id' => $this->owner->id, 'content' => 'x', 'moderation_status' => 'approved', 'male_quantity' => 2, 'female_quantity' => 0,
            'details' => ['height_cm' => 152, 'purpose' => 'Poyga'],
        ]);

        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee("Bo'yi")->assertSee('152 sm')->assertSee('Maqsadi')->assertSee('Poyga')
            ->assertSee('Zoti');
    }

    public function test_purchase_requests_for_uncounted_sex_listings_show_a_plain_count(): void
    {
        $post = Post::create($this->payload('Baliq', ['gender' => 'male', 'age' => null]) + [
            'user_id' => $this->owner->id, 'content' => 'x', 'moderation_status' => 'approved', 'male_quantity' => 12, 'female_quantity' => 0,
        ]);
        $buyer = User::factory()->create();
        $buyer->assignRole('user');

        $this->actingAs($buyer)->post(route('purchase-requests.store'), ['animal_id' => $post->id, 'male_quantity' => 5])
            ->assertSessionHas('success');

        $this->assertSame(7, $post->fresh()->male_quantity);
        $this->actingAs($buyer)->get(route('user.purchase-requests.index'))->assertOk()->assertSee('Soni: 5')->assertDontSee('Erkak ♂');

        // The buy dialog asks for a single count
        $dialog = $this->actingAs($buyer)->get(route('posts.show', $post))->assertOk()->getContent();
        $this->assertStringContainsString('1. Nechta kerakligini tanlang:', $dialog);
        $this->assertStringNotContainsString('buyerQty_female', $dialog);
    }

    public function test_home_card_and_sidebar_adapt(): void
    {
        $fish = Post::create($this->payload('Baliq', ['gender' => 'male', 'age' => null, 'title' => 'Guppi baliqlari']) + [
            'user_id' => $this->owner->id, 'content' => 'x', 'moderation_status' => 'approved', 'male_quantity' => 10, 'female_quantity' => 0,
        ]);

        $card = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('Guppi baliqlari', $card);
        $this->assertStringNotContainsString('title="Jinsi"', $card);

        // Sex filter is offered for dogs but not for fish
        $this->get(route('home', ['category_id' => $this->category('Baliq')->id]))->assertOk()->assertDontSee('>Jinsi</h3>', false);
        $this->get(route('home', ['category_id' => $this->category('It')->id]))->assertOk()->assertSee('>Jinsi</h3>', false);
    }
}
