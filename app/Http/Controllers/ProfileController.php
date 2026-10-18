<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Post;
use App\Models\User;
use App\Services\DatabaseImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Profile and settings, with the user's own listings. Saved listings live on their own page (favorites.index).
     */
    public function show(Request $request)
    {
        $user = $request->user()->loadCount('posts');

        $recentPosts = $user->posts()
            ->where('status', '!=', 'sold')
            ->latest()
            ->take(5)
            ->get();

        $admin = $user->hasRole('admin') ? $this->adminOverview($user) : null;

        return view('profile.show', compact('user', 'recentPosts', 'admin'));
    }

    /**
     * The main admin's overview: what needs a decision, how the site is doing, and their latest decisions.
     */
    private function adminOverview(User $admin): array
    {
        $pending = Post::where('moderation_status', 'pending');

        return [
            'stats' => [
                'pending_posts' => (clone $pending)->count(),
                'oldest_pending' => (clone $pending)->min('updated_at'),
                'pending_banners' => Banner::where('moderation_status', 'pending')->count(),
                'active_posts' => Post::listed()->count(),
                'sold_posts' => Post::where('status', 'sold')->count(),
                'new_today' => Post::whereDate('created_at', today())->count(),
                'users' => User::whereHas('roles', fn ($q) => $q->where('name', 'user'))->count(),
            ],
            'queue' => (clone $pending)->with(['user', 'category'])->oldest('updated_at')->take(5)->get(),
            'decisions' => Post::with(['user', 'category'])
                ->where('moderated_by', $admin->id)
                ->latest('moderated_at')
                ->take(5)
                ->get(),
        ];
    }

    public function user()
    {
        return redirect()->route('profile.show');
    }

    public function updateUserProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'telegram_username' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                DatabaseImageService::delete($user->avatar);
            }

            $validated['avatar'] = DatabaseImageService::store($request->file('avatar'), 'avatars');
        }

        $user->update($validated);

        return back()->with('success', 'Profil ma\'lumotlari yangilandi.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Parol muvaffaqiyatli yangilandi.');
    }
}
