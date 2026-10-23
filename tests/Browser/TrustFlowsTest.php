<?php

namespace Tests\Browser;

use App\Models\PurchaseRequest;
use App\Models\Report;
use App\Models\Review;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Buying, reporting and reviewing, clicked through in a real browser (dialogs, steppers, star rating).
 */
class TrustFlowsTest extends DuskTestCase
{
    #[Test]
    public function buyer_sends_a_request_through_the_dialog_and_can_cancel_it(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $post = $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($buyer, $post) {
            $browser->loginAs($buyer)->visit("/posts/{$post->id}")
                ->press("Sotib olish so'rovini yuborish")
                ->waitFor('#buyModalDetail')
                ->assertSeeIn('#buyerTotalPrice', '900 000')
                // steppers: one more male (max 1) is refused, a female is added
                ->click('[data-qty-target="buyerQty_female"][data-qty-step="1"]')
                ->waitUsing(3, 100, fn () => $browser->value('#buyerQty_female') === '1')
                ->assertSeeIn('#buyerTotalPrice', '1 800 000')
                ->press("So'rov yuborish")
                ->waitForText("So'rov yuborilgan")
                ->assertSee("So'rov yuborilgan");
        });

        $this->assertSame(1, PurchaseRequest::where('user_id', $buyer->id)->count());
        $request = PurchaseRequest::first();
        $this->assertSame(2, $request->quantity);

        $this->browse(function (Browser $browser) use ($buyer, $post, $request) {
            $browser->loginAs($buyer)->visit("/posts/{$post->id}")
                ->press("So'rovni bekor qilish")
                ->acceptDialog()
                ->waitForText("Sotib olish so'rovini yuborish");
        });

        $this->assertSame('cancelled', $request->fresh()->status);
    }

    #[Test]
    public function buyer_reports_a_listing_from_the_dialog(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $post = $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($buyer, $post) {
            $browser->loginAs($buyer)->visit("/posts/{$post->id}")
                ->assertMissing('#reportPost')
                ->press("E'londan shikoyat qilish")
                ->waitFor('#reportPost')
                ->assertSeeIn('#reportPost', 'Firibgarlik shubhasi')
                // "Boshqa sabab" needs a comment: the browser refuses an empty form, so pick a plain reason
                ->radio('reason', 'fraud')
                ->type('#reportPost textarea', "Oldindan pul so'radi")
                ->within('#reportPost', fn (Browser $d) => $d->press('Yuborish'))
                ->waitForText('Shikoyatingiz adminga yuborildi');
        });

        $this->assertDatabaseHas('reports', ['reporter_id' => $buyer->id, 'post_id' => $post->id, 'reason' => 'fraud', 'comment' => "Oldindan pul so'radi"]);
    }

    #[Test]
    public function report_dialog_closes_with_cancel_and_the_owner_has_no_report_button(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $post = $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($seller, $buyer, $post) {
            $browser->loginAs($buyer)->visit("/posts/{$post->id}")
                ->press("E'londan shikoyat qilish")->waitFor('#reportPost')
                ->within('#reportPost', fn (Browser $d) => $d->press('Bekor qilish'))
                ->waitUntilMissing('#reportPost');

            $browser->loginAs($seller)->visit("/posts/{$post->id}")
                ->assertDontSee("E'londan shikoyat qilish");
        });

        $this->assertSame(0, Report::count());
    }

    #[Test]
    public function admin_takes_a_reported_listing_down_from_the_reports_page(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $admin = $this->makeUser('admin');
        $post = $this->makePost($seller);
        Report::create(['reporter_id' => $buyer->id, 'post_id' => $post->id, 'reason' => 'fraud']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)->visit('/')
                ->assertPresent('a[aria-label="Shikoyatlar"]')
                ->click('a[aria-label="Shikoyatlar"]')
                ->waitForLocation('/reports')
                ->assertSee('Firibgarlik shubhasi')
                ->press("E'lonni olib tashlash")
                ->acceptDialog()
                ->waitForText('olib tashlandi');
        });

        $this->assertSame('rejected', $post->fresh()->moderation_status);
    }

    #[Test]
    public function buyer_rates_a_seller_with_the_stars_and_the_review_shows_up(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $post = $this->makePost($seller);
        PurchaseRequest::create(['user_id' => $buyer->id, 'animal_id' => $post->id, 'gender' => 'male', 'quantity' => 1, 'status' => 'approved']);

        $this->browse(function (Browser $browser) use ($seller, $buyer) {
            $browser->loginAs($buyer)->visit("/sellers/{$seller->id}")
                ->assertSee('Hali baholanmagan')
                ->assertPresent('form[action$="/review"]')
                // the star labels are the click targets; 4th star = 4 points
                ->click('label[for="rate4"]')
                ->type('textarea[name=comment]', 'Hayvonlar sog\'lom ekan')
                ->press('Yuborish')
                ->waitForText("Hayvonlar sog'lom ekan")
                ->assertSee('1 ta sharh')
                ->assertSee('4.0');
        });

        $this->assertSame(4, Review::where('seller_id', $seller->id)->value('rating'));

        $this->browse(function (Browser $browser) use ($seller, $buyer) {
            $browser->loginAs($buyer)->visit("/sellers/{$seller->id}#sharhlar")
                ->press("O'chirish")
                ->acceptDialog()
                ->waitForText("Hali sharh yo'q");
        });

        $this->assertSame(0, Review::count());
    }

    #[Test]
    public function a_stranger_sees_no_review_form_but_can_report_the_seller(): void
    {
        $seller = $this->makeUser();
        $stranger = $this->makeUser();
        $this->makePost($seller);

        $this->browse(function (Browser $browser) use ($seller, $stranger) {
            $browser->loginAs($stranger)->visit("/sellers/{$seller->id}")
                ->assertMissing('form[action$="/review"]')
                ->assertSee('baholay oladi')
                ->press('Shikoyat qilish')
                ->waitFor('#reportSeller')
                ->radio('reason', 'spam')
                ->within('#reportSeller', fn (Browser $d) => $d->press('Yuborish'))
                ->waitForText('Shikoyatingiz adminga yuborildi');
        });

        $this->assertDatabaseHas('reports', ['reported_user_id' => $seller->id, 'reporter_id' => $stranger->id, 'reason' => 'spam']);
    }

    #[Test]
    public function owner_renews_an_archived_listing_from_the_archive(): void
    {
        $seller = $this->makeUser();
        $post = $this->makePost($seller, ['status' => 'archived', 'expires_at' => now()->subDay()]);

        $this->browse(function (Browser $browser) use ($seller) {
            $browser->loginAs($seller)->visit('/admin/archive')
                ->press('Yangilash')
                ->waitForText("E'lon yangilandi");
        });

        $post->refresh();
        $this->assertSame('active', $post->status);
        $this->assertTrue($post->expires_at->isFuture());
    }
}
