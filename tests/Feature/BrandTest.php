<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandTest extends TestCase
{
    public function test_site_shows_the_faunara_logo_and_name(): void
    {
        foreach ([route('home'), route('login'), route('register')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('Faunara', $html);
            $this->assertStringNotContainsString('ZooMarket', $html);
            $this->assertStringContainsString('brand/favicon-32.png', $html);
        }

        // Header and footer carry the full logo, the login card the round mark
        $home = $this->get(route('home'))->getContent();
        $this->assertSame(2, substr_count($home, 'brand/logo.png'));
        $this->assertStringContainsString('brand/mark.png', $this->get(route('login'))->getContent());
    }

    public function test_brand_images_exist(): void
    {
        foreach (['logo.png', 'mark.png', 'favicon-32.png', 'favicon-64.png', 'apple-touch-icon.png'] as $file) {
            $this->assertFileExists(public_path("brand/{$file}"));
        }
    }
}
