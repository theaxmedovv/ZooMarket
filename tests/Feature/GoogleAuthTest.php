<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id = 'google-123', ?string $email = 'ali@gmail.com', string $name = 'Ali Valiyev'): void
    {
        $googleUser = (new GoogleUser())->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_login_and_register_pages_show_google_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('auth.google.redirect'), false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Google orqali ro&#039;yxatdan o&#039;tish', false)
            ->assertSee('role=seller', false);
    }

    public function test_redirect_sends_user_to_google_and_remembers_role(): void
    {
        config([
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $response = $this->get(route('auth.google.redirect', ['role' => 'seller']));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com/', $response->headers->get('Location'));
        $response->assertSessionHas('google_role', 'seller');
    }

    public function test_new_google_user_is_created_as_buyer_by_default(): void
    {
        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('posts.index'));

        $user = User::where('email', 'ali@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('Ali Valiyev', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->can('read posts'));
    }

    public function test_new_google_user_gets_role_chosen_on_register_page(): void
    {
        $this->fakeGoogleUser();

        $this->withSession(['google_role' => 'seller'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('admin.dashboard'));

        $user = User::where('email', 'ali@gmail.com')->firstOrFail();
        $this->assertTrue($user->hasRole('seller'));
        $this->assertTrue($user->can('create posts'));
    }

    public function test_existing_email_account_is_linked_to_google(): void
    {
        $existing = User::factory()->create(['email' => 'ali@gmail.com', 'google_id' => null]);
        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))->assertRedirect();

        $this->assertAuthenticatedAs($existing);
        $this->assertSame('google-123', $existing->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_returning_google_user_logs_in_even_if_email_changed(): void
    {
        $existing = User::factory()->create(['email' => 'old@gmail.com', 'google_id' => 'google-123']);
        $this->fakeGoogleUser(email: 'new@gmail.com');

        $this->get(route('auth.google.callback'))->assertRedirect();

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::count());
    }

    public function test_failed_google_callback_returns_to_login_with_error(): void
    {
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andThrow(new \Laravel\Socialite\Two\InvalidStateException());
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
