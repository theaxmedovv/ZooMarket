<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    private function profileData(Request $request): array
    {
        $user = $request->user()->loadCount('posts');
        $recentPosts = $user->posts()
            ->latest()
            ->take(5)
            ->get();

        return compact('user', 'recentPosts');
    }

    private function likedProfileData(Request $request): array
    {
        $user = $request->user()->loadCount('likedPosts');
        $likedPosts = $user->likedPosts()
            ->with(['user', 'category'])
            ->latest('post_likes.created_at')
            ->take(12)
            ->get();

        return compact('user', 'likedPosts');
    }

    public function show(Request $request)
    {
        return view('profile.show', $this->profileData($request));
    }

    public function user(Request $request)
    {
        return view('profile.user', $this->likedProfileData($request));
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
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
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
