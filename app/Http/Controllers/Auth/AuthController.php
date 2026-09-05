<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if (Auth::user()->hasRole('seller')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('posts.index');
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

        $user = DB::transaction(function () use ($data) {
            // Ensure the role and core permissions exist
            $role = Role::firstOrCreate(['name' => $data['role'], 'guard_name' => 'web']);

            if ($data['role'] === 'seller') {
                $sellerPermissions = ['create posts', 'read posts', 'edit posts', 'delete posts'];
                foreach ($sellerPermissions as $p) {
                    Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
                }
                $role->syncPermissions($sellerPermissions);
            } elseif ($data['role'] === 'user') {
                $userPermission = Permission::firstOrCreate(['name' => 'read posts', 'guard_name' => 'web']);
                $role->syncPermissions([$userPermission]);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->assignRole($role);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

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
