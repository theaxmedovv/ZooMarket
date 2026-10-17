<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A form sent with a stale CSRF token must never end on the bare "419 Page Expired" page.
 */
class ExpiredCsrfTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Laravel skips CSRF checks in tests; turn them back on.
        $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_logout_works_with_a_stale_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)
            ->withSession(['_token' => 'current-token'])
            ->post(route('logout'), ['_token' => 'token-from-an-old-page'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_after_session_expired_goes_to_login(): void
    {
        $this->post(route('logout'), ['_token' => 'token-from-an-old-page'])
            ->assertRedirect(route('login'));
    }

    public function test_other_forms_with_a_stale_token_go_back_with_a_warning(): void
    {
        $this->withSession(['_token' => 'current-token'])
            ->from(route('login'))
            ->post(route('login.post'), [
                '_token' => 'token-from-an-old-page',
                'email' => 'ali@example.com',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning')
            ->assertSessionHasInput('email', 'ali@example.com')
            ->assertSessionMissing('_old_input.password');
    }

    public function test_valid_token_is_still_required_for_other_forms(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)
            ->withSession(['_token' => 'current-token'])
            ->from(route('profile.show'))
            ->post(route('admin.profile.update'), ['_token' => 'wrong', 'name' => 'Hacked'])
            ->assertRedirect(route('profile.show'));

        $this->assertNotSame('Hacked', $user->fresh()->name);
    }
}
