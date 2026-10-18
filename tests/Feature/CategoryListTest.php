<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryListTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_animal_categories_exist_in_the_given_order(): void
    {
        $this->assertSame(CategorySeeder::CATEGORIES, Category::ordered()->pluck('name')->all());
        $this->assertSame('Boshqa', Category::ordered()->get()->last()->name);
    }

    public function test_seeder_can_run_again_without_duplicates(): void
    {
        $count = Category::count();
        $this->seed(CategorySeeder::class);

        $this->assertSame($count, Category::count());
    }

    public function test_the_old_qush_category_is_renamed_and_keeps_its_listings(): void
    {
        $owner = User::factory()->create();
        $old = Category::create(['name' => 'Qush']);
        Category::where('name', 'Qushlar')->delete();
        $post = Post::create([
            'user_id' => $owner->id, 'category_id' => $old->id, 'title' => 'Kanareyka', 'content' => 'x', 'breed' => 'Kanareyka',
            'quantity' => 1, 'gender' => 'male', 'male_quantity' => 1, 'female_quantity' => 0, 'age' => '1 yosh',
            'price' => 1, 'currency' => 'UZS', 'location' => 'Toshkent', 'status' => 'active', 'moderation_status' => 'approved',
        ]);

        $this->seed(CategorySeeder::class);

        $this->assertSame('Qushlar', $old->fresh()->name);
        $this->assertSame($old->id, $post->fresh()->category_id);
        $this->assertSame(1, Category::where('name', 'Qushlar')->count());
        $this->assertNull(Category::where('name', 'Qush')->first());
    }

    public function test_new_listing_form_offers_the_categories_in_order(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('user');

        $html = $this->actingAs($user)->get(route('posts.create'))->assertOk()->getContent();

        $last = 0;
        foreach (['Ekzotik hayvonlar', 'Qoramol', 'Qo&#039;y va echki', 'Otlar', 'Tuya', 'Asalarichilik'] as $name) {
            $pos = strpos($html, '>' . $name . '</option>');
            $this->assertNotFalse($pos, "{$name} missing from the form");
            $this->assertGreaterThan($last, $pos);
            $last = $pos;
        }
    }

    public function test_home_page_lists_the_categories(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        foreach (['Kaptar', 'Parrandalar', 'Ekzotik hayvonlar', 'Asalarichilik'] as $name) {
            $this->assertStringContainsString($name, $html);
        }
    }
}
