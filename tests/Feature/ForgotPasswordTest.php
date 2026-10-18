<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->user = User::factory()->create(['email' => 'ali@gmail.com', 'password' => 'old-password-1']);
        RateLimiter::clear('pw-reset-email:ali@gmail.com');
        RateLimiter::clear('pw-reset-ip:127.0.0.1');
    }

    /** Request a code and return the 6 digits that were mailed. */
    private function requestCode(string $email = 'ali@gmail.com'): string
    {
        $this->post(route('password.email'), ['email' => $email])->assertRedirect(route('password.code'));

        $sent = null;
        Mail::assertSent(PasswordResetCodeMail::class, function (PasswordResetCodeMail $mail) use (&$sent) {
            $sent = $mail->code;

            return true;
        });

        return $sent;
    }

    public function test_login_page_links_to_forgot_password(): void
    {
        $this->get(route('login'))->assertOk()->assertSee(route('password.request'), false);
        $this->get(route('password.request'))->assertOk()->assertSee('Kod yuborish');
    }

    public function test_code_is_mailed_to_the_user_and_only_its_hash_is_stored(): void
    {
        $code = $this->requestCode();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        Mail::assertSent(PasswordResetCodeMail::class, fn ($m) => $m->hasTo('ali@gmail.com'));

        $row = PasswordResetCode::where('email', 'ali@gmail.com')->firstOrFail();
        $this->assertNotSame($code, $row->code_hash);
        $this->assertTrue($row->matches($code));
        $this->assertTrue($row->expires_at->between(now()->addMinutes(9), now()->addMinutes(10)->addSecond()));
    }

    public function test_mail_contains_the_code(): void
    {
        $html = (new PasswordResetCodeMail('482913', 'Ali'))->render();

        $this->assertStringContainsString('482913', $html);
        $this->assertStringContainsString('10 daqiqa', $html);
    }

    public function test_unknown_email_gets_the_same_answer_but_no_mail(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@gmail.com'])
            ->assertRedirect(route('password.code'))
            ->assertSessionHas('info');

        Mail::assertNothingSent();
        $this->assertSame(0, PasswordResetCode::count());

        // ...and the code step can never succeed for it
        $this->post(route('password.verify'), ['code' => '123456'])->assertSessionHasErrors('code');
    }

    public function test_correct_code_then_new_password_completes_the_reset(): void
    {
        $code = $this->requestCode();

        $this->post(route('password.verify'), ['code' => $code])->assertRedirect(route('password.reset'));
        $this->get(route('password.reset'))->assertOk()->assertSee('Parolni yangilash');

        $oldRemember = $this->user->remember_token;

        $this->post(route('password.update'), ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('brand-new-pass', $this->user->password));
        $this->assertNotSame($oldRemember, $this->user->remember_token);
        $this->assertSame(0, PasswordResetCode::count());

        // The new password logs in; the old one does not
        $this->post(route('login.post'), ['email' => 'ali@gmail.com', 'password' => 'old-password-1'])->assertSessionHasErrors('email');
        $this->post(route('login.post'), ['email' => 'ali@gmail.com', 'password' => 'brand-new-pass'])->assertRedirect(route('home'));
    }

    public function test_reset_signs_out_stored_sessions_of_the_user(): void
    {
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert([
            'id' => 'other-device', 'user_id' => $this->user->id, 'payload' => '', 'last_activity' => time(),
        ]);

        $code = $this->requestCode();
        $this->post(route('password.verify'), ['code' => $code]);
        $this->post(route('password.update'), ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass']);

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
    }

    public function test_wrong_code_is_rejected_and_attempts_are_limited(): void
    {
        $code = $this->requestCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        foreach (range(1, PasswordResetCode::MAX_ATTEMPTS) as $i) {
            $this->post(route('password.verify'), ['code' => $wrong])->assertSessionHasErrors('code');
        }

        // Five misses burn the code: even the right one no longer works
        $this->post(route('password.verify'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertSame(0, PasswordResetCode::count());
        $this->get(route('password.reset'))->assertRedirect(route('password.request'));
    }

    public function test_expired_code_is_rejected(): void
    {
        $code = $this->requestCode();
        PasswordResetCode::where('email', 'ali@gmail.com')->update(['expires_at' => now()->subSecond()]);

        $this->post(route('password.verify'), ['code' => $code])->assertSessionHasErrors('code');
        $this->get(route('password.reset'))->assertRedirect(route('password.request'));
    }

    public function test_code_must_be_six_digits(): void
    {
        $this->requestCode();

        $this->post(route('password.verify'), ['code' => 'abc'])->assertSessionHasErrors('code');
        $this->post(route('password.verify'), ['code' => '12345'])->assertSessionHasErrors('code');
    }

    public function test_reset_form_is_closed_until_the_code_is_verified(): void
    {
        $this->get(route('password.reset'))->assertRedirect(route('password.request'));

        $this->requestCode();
        $this->get(route('password.reset'))->assertRedirect(route('password.request'));

        $this->post(route('password.update'), ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])
            ->assertRedirect(route('password.request'));
        $this->assertTrue(Hash::check('old-password-1', $this->user->fresh()->password));
    }

    public function test_verification_goes_stale_after_fifteen_minutes(): void
    {
        $code = $this->requestCode();
        $this->post(route('password.verify'), ['code' => $code]);
        PasswordResetCode::where('email', 'ali@gmail.com')->update(['verified_at' => now()->subMinutes(16)]);

        $this->get(route('password.reset'))->assertRedirect(route('password.request'));
    }

    public function test_new_password_needs_confirmation_and_eight_characters(): void
    {
        $code = $this->requestCode();
        $this->post(route('password.verify'), ['code' => $code]);

        $this->post(route('password.update'), ['password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->post(route('password.update'), ['password' => 'brand-new-pass', 'password_confirmation' => 'different'])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password-1', $this->user->fresh()->password));
    }

    public function test_a_code_for_one_email_cannot_reset_another_account(): void
    {
        $other = User::factory()->create(['email' => 'vali@gmail.com', 'password' => 'vali-password-1']);

        $code = $this->requestCode();
        $this->post(route('password.verify'), ['code' => $code]);
        $this->post(route('password.update'), ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass']);

        $this->assertTrue(Hash::check('vali-password-1', $other->fresh()->password));
    }

    public function test_requesting_again_replaces_the_code_but_is_rate_limited(): void
    {
        $this->requestCode();

        // A second request straight away is refused: one code per minute per email
        $this->post(route('password.resend'))->assertSessionHasErrors('code');

        RateLimiter::clear('pw-reset-email:ali@gmail.com');
        $this->post(route('password.resend'))->assertRedirect(route('password.code'));

        $row = PasswordResetCode::where('email', 'ali@gmail.com')->firstOrFail();
        $this->assertSame(1, PasswordResetCode::count());
        $this->assertSame(0, $row->attempts);
        Mail::assertSent(PasswordResetCodeMail::class, 2);
    }

    public function test_mail_failure_shows_an_error_and_leaves_no_code(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post(route('password.email'), ['email' => 'ali@gmail.com'])->assertSessionHasErrors('email');

        $this->assertSame(0, PasswordResetCode::count());
    }

    public function test_hourly_limit_per_ip(): void
    {
        foreach (range(1, 10) as $i) {
            RateLimiter::hit('pw-reset-ip:127.0.0.1', 3600);
        }

        $this->post(route('password.email'), ['email' => 'ali@gmail.com'])->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }

    public function test_logged_in_users_are_sent_away_from_the_forgot_pages(): void
    {
        $this->actingAs($this->user)->get(route('password.request'))->assertRedirect();
    }
}
