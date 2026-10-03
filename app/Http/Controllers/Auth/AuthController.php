<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => "Kiritilgan login yoki parol noto'g'ri.",
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectAfterLogin(Auth::user());
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:seller,user'],
        ]);

        $user = $this->createUserWithRole([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ], $data['role']);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        // Role picked on the register page; sign-ups from the login page become buyers
        $role = in_array($request->query('role'), ['seller', 'user'], true) ? $request->query('role') : 'user';
        $request->session()->put('google_role', $role);

        return $this->googleDriver()->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = $this->googleDriver()->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'email' => "Google orqali kirib bo'lmadi. Iltimos, qayta urinib ko'ring.",
            ]);
        }

        if (! $googleUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => "Google hisobingizdan email manzil olinmadi.",
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link an existing email/password account to Google on first Google login
            if (! $user->google_id) {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        } else {
            $user = $this->createUserWithRole([
                'name' => $googleUser->getName() ?: Str::before($googleUser->getEmail(), '@'),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                // Google accounts sign in without a password; store an unguessable one
                'password' => Str::random(40),
            ], $request->session()->get('google_role', 'user'));

            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $request->session()->forget('google_role');

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    /**
     * Build the callback URL from the current host so it matches whatever
     * address the app is served on (e.g. 127.0.0.1:8000 under artisan serve),
     * unless GOOGLE_REDIRECT_URI is set explicitly.
     */
    private function googleDriver()
    {
        $redirect = config('services.google.redirect') ?: route('auth.google.callback');

        return Socialite::driver('google')->redirectUrl($redirect);
    }

    private function createUserWithRole(array $attributes, string $roleName): User
    {
        return DB::transaction(function () use ($attributes, $roleName) {
            // Ensure the role and core permissions exist
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($roleName === 'seller') {
                $sellerPermissions = ['create posts', 'read posts', 'edit posts', 'delete posts'];
                foreach ($sellerPermissions as $p) {
                    Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
                }
                $role->syncPermissions($sellerPermissions);
            } elseif ($roleName === 'user') {
                $userPermission = Permission::firstOrCreate(['name' => 'read posts', 'guard_name' => 'web']);
                $role->syncPermissions([$userPermission]);
            }

            $user = User::create($attributes);
            $user->assignRole($role);

            return $user;
        });
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->hasRole('seller')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('posts.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
