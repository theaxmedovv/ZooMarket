<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that home page redirects to posts page
     */
    public function test_home_redirects_to_posts(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('posts.index'));
    }

    /**
     * Test that posts page loads successfully
     */
    public function test_posts_page_loads(): void
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
    }

    /**
     * Test that posts page with query filters and sort works
     */
    public function test_posts_page_with_filters_and_sort(): void
    {
        $response = $this->get('/posts?gender=male&price_min=10&price_max=500&sort=price_asc');

        $response->assertStatus(200);
        $response->assertSee('Filtrlar');
        $response->assertSee('Narx: arzonroq');
    }
}
