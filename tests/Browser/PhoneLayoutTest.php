<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * The phone layout in a real browser at 375px: nothing sideways, tab bar, full-screen catalog, bottom sheets.
 */
class PhoneLayoutTest extends DuskTestCase
{
    private function overflows(Browser $browser): bool
    {
        return $browser->script('return document.documentElement.scrollWidth > window.innerWidth + 1')[0];
    }

    #[Test]
    public function pages_do_not_scroll_sideways_on_a_phone(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $post = $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($buyer, $post) {
            $this->phone($browser)->loginAs($buyer);

            foreach (['/', "/posts/{$post->id}", '/favorites', '/profile', '/chats', '/my-posts', '/posts/create', '/admin/archive', "/sellers/{$post->user_id}"] as $url) {
                $browser->visit($url);
                $this->assertFalse($this->overflows($browser), "{$url} scrolls sideways at 375px");
            }
        });
    }

    #[Test]
    public function tab_bar_is_there_on_a_phone_and_gone_on_desktop(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $this->phone($browser)->loginAs($user)->visit('/')
                ->assertVisible('nav[aria-label="Asosiy menyu"]')
                ->tap(fn (Browser $b) => $this->desktop($b))->visit('/')
                ->assertMissing('nav[aria-label="Asosiy menyu"]');
        });
    }

    #[Test]
    public function phone_catalog_is_full_screen_with_drill_down(): void
    {
        $post = $this->makePost($this->makeUser());

        $this->browse(function (Browser $browser) use ($post) {
            $this->phone($browser)->visit('/')
                ->click('button[aria-controls="zmCatalog"]')->waitFor('#zmCatalog');

            $size = $browser->script("const r = document.getElementById('zmCatalog').getBoundingClientRect(); return [r.width, r.height]")[0];
            $this->assertEqualsWithDelta(375, $size[0], 1);
            $this->assertGreaterThanOrEqual(800, $size[1]);

            // category -> its own lists -> back -> close
            $browser->click("#zmCatalog [data-catalog-tab='{$post->category_id}']")
                ->waitFor("#zmCatalog [data-catalog-panel='{$post->category_id}']")
                ->assertSeeIn('#zmCatalog', 'Britan')
                ->click('#zmCatalog [data-catalog-back]')
                ->waitUntilMissing("#zmCatalog [data-catalog-panel='{$post->category_id}']")
                ->click('#zmCatalog button[aria-label="Yopish"]')
                ->waitUntilMissing('#zmCatalog');
        });
    }

    #[Test]
    public function profile_and_kabinet_open_as_bottom_sheets_on_a_phone(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $this->phone($browser)->loginAs($user)->visit('/')
                ->click('button[aria-controls="zmMyMenu"]')->waitFor('#zmMyMenu')
                ->assertSeeIn('#zmMyMenu', "Mening e'lonlarim");

            $bottom = $browser->script("return window.innerHeight - document.getElementById('zmMyMenu').getBoundingClientRect().bottom")[0];
            $this->assertLessThan(2, abs($bottom), 'the sheet is not pinned to the bottom of the screen');

            $browser->keys('', ['{escape}'])->waitUntilMissing('#zmMyMenu')
                ->click('nav[aria-label="Asosiy menyu"] button[aria-controls="zmProfileMenu"]')
                ->waitFor('#zmProfileMenu')
                ->assertSeeIn('#zmProfileMenu', 'Profil va sozlamalar');
        });
    }

    #[Test]
    public function touch_targets_in_the_header_and_tab_bar_are_at_least_44px(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $this->phone($browser)->loginAs($user)->visit('/');

            $small = $browser->script(<<<'JS'
                return [...document.querySelectorAll('#zmHeader a, #zmHeader button, nav[aria-label="Asosiy menyu"] a, nav[aria-label="Asosiy menyu"] button')]
                    .filter(el => el.offsetParent !== null)
                    .map(el => { const r = el.getBoundingClientRect(); return [el.getAttribute('aria-label') || el.textContent.trim().slice(0, 20), Math.round(r.width), Math.round(r.height)]; })
                    .filter(([, w, h]) => h < 44 || w < 44);
            JS)[0];

            $this->assertSame([], $small, 'touch targets below 44px: ' . json_encode($small));
        });
    }

    #[Test]
    public function listing_page_has_the_phone_action_bar_and_buy_sheet(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $post = $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($buyer, $post) {
            $this->phone($browser)->loginAs($buyer)->visit("/posts/{$post->id}")
                ->assertSee('900 000')
                ->assertMissing('nav[aria-label="Asosiy menyu"]')
                ->click('div.fixed.bottom-0 button[data-dialog-open="buyModalDetail"]')
                ->waitFor('#buyModalDetail')
                ->assertSeeIn('#buyModalDetail', "Sotib olish so'rovi");

            $bottom = $browser->script("return window.innerHeight - document.getElementById('buyModalDetail').getBoundingClientRect().bottom")[0];
            $this->assertLessThan(2, abs($bottom), 'the buy dialog is not a bottom sheet');
        });
    }
}
