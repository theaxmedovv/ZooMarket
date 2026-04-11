<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
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
}
