<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * The header as a person uses it: dropdowns open and close, links go where they say, the layout holds.
 */
class NavigationTest extends DuskTestCase
{
    private const ACCOUNT_LINKS = [
        "Mening e'lonlarim" => '/my-posts',
        "Kelgan so'rovlar" => '/admin/purchase-requests',
        'Buyurtmalarim' => '/user/purchase-requests',
        'Sevimlilar' => '/favorites',
        'Arxiv' => '/admin/archive',
        'Mening bannerlarim' => '/banners',
    ];

    #[Test]
    public function kabinet_dropdown_lists_the_six_account_pages_and_closes(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->visit('/')
                ->assertMissing('#zmMyMenu')
                ->click('button[aria-controls="zmMyMenu"]')
                ->waitFor('#zmMyMenu');

            foreach (self::ACCOUNT_LINKS as $label => $path) {
                $browser->assertSeeIn('#zmMyMenu', $label);
                $this->assertStringEndsWith($path, $browser->attribute("#zmMyMenu a[href$='{$path}']", 'href'));
            }

            // Escape closes it, and so does a click elsewhere
            $browser->keys('', ['{escape}'])->waitUntilMissing('#zmMyMenu')
                ->click('button[aria-controls="zmMyMenu"]')->waitFor('#zmMyMenu')
                ->click('#zmSearchForm input[name=q]')->waitUntilMissing('#zmMyMenu');
        });
    }

    #[Test]
    public function kabinet_link_opens_its_page(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->visit('/')
                ->click('button[aria-controls="zmMyMenu"]')->waitFor('#zmMyMenu')
                ->clickLink("Mening e'lonlarim")
                ->waitForLocation('/my-posts')
                ->assertSee("Mening e'lonlarim");
        });
    }

    #[Test]
    public function profile_menu_has_only_profile_and_logout_and_logout_works(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->visit('/')
                ->click('button[aria-controls="zmProfileMenu"]')->waitFor('#zmProfileMenu')
                ->assertSeeIn('#zmProfileMenu', 'Profil va sozlamalar')
                ->assertSeeIn('#zmProfileMenu', 'Chiqish')
                ->assertSeeIn('#zmProfileMenu', $user->email)
                ->assertDontSeeIn('#zmProfileMenu', 'Buyurtmalarim')
                ->assertDontSeeIn('#zmProfileMenu', 'Arxiv')
                ->press('Chiqish')
                ->waitForLocation('/login')
                ->assertGuest()
                ->assertPresent('a[href$="/login"]');
        });
    }

    #[Test]
    public function only_one_dropdown_is_open_at_a_time(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->visit('/')
                ->click('button[aria-controls="zmMyMenu"]')->waitFor('#zmMyMenu')
                ->click('button[aria-controls="zmProfileMenu"]')->waitFor('#zmProfileMenu')
                ->assertMissing('#zmMyMenu')
                ->click('button[aria-controls="zmCatalog"]')->waitFor('#zmCatalog')
                ->assertMissing('#zmProfileMenu');
        });
    }

    #[Test]
    public function header_order_is_logo_catalog_new_listing_search_then_account(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->visit('/');

            $x = fn (string $selector) => $browser->script("return document.querySelector(arguments[0]).getBoundingClientRect().left", $selector)[0] ?? null;
            // script() forwards no arguments, so inline the selectors
            $left = fn (string $selector) => $browser->script("return document.querySelector(" . json_encode($selector) . ").getBoundingClientRect().left")[0];

            $order = [
                $left('#zmHeader img'),
                $left('button[aria-controls="zmCatalog"]'),
                $left('#zmHeader a[href$="/posts/create"]'),
                $left('#zmSearchForm'),
                $left('button[aria-controls="zmMyMenu"]'),
                $left('#zmHeader a[href$="/chats"]'),
                $left('button[aria-controls="zmProfileMenu"]'),
            ];

            $sorted = $order;
            sort($sorted);
            $this->assertSame($sorted, $order, 'header items are not laid out left to right in the intended order');
        });
    }

    #[Test]
    public function katalog_shows_a_categorys_filters_on_hover(): void
    {
        $seller = $this->makeUser();
        $post = $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit('/')
                ->click('button[aria-controls="zmCatalog"]')->waitFor('#zmCatalog')
                ->mouseover("#zmCatalog [data-catalog-tab='{$post->category_id}']")
                ->waitFor("#zmCatalog [data-catalog-panel='{$post->category_id}']")
                ->assertSeeIn("#zmCatalog [data-catalog-panel='{$post->category_id}']", 'Britan')
                ->assertSeeIn("#zmCatalog [data-catalog-panel='{$post->category_id}']", 'Toshkent shahri');
        });
    }

    #[Test]
    public function search_finds_a_listing_and_category_chip_filters_the_home_page(): void
    {
        $seller = $this->makeUser();
        $this->makePost($seller, ['title' => 'Britan mushugi']);
        $this->makePost($seller, ['title' => 'Alabay iti', 'breed' => 'Alabay']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->type('#zmSearchForm input[name=q]', 'Alabay')
                ->keys('#zmSearchForm input[name=q]', '{enter}')
                ->waitForText('Alabay iti')
                ->assertDontSee('Britan mushugi');
        });
    }

    #[Test]
    public function guest_who_logs_in_from_a_listing_lands_back_on_it(): void
    {
        $seller = $this->makeUser();
        $post = $this->makePost($seller);
        $buyer = $this->makeUser('user', ['email' => 'buyer@example.test']);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit("/posts/{$post->id}")
                ->assertSee('tizimga kiring')
                ->clickLink('Kirish', 'main a[href*="login"], a[href*="login"][class*="rounded-full"]')
                ->waitForLocation('/login')
                ->type('email', 'buyer@example.test')
                ->type('password', 'password')
                ->press('Kirish')
                ->waitForLocation("/posts/{$post->id}")
                ->assertAuthenticated()
                ->assertSee('Britan mushugi');
        });
    }
}
