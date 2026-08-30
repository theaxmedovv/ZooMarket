<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('profile.show');
    }

    public function archive(Request $request)
    {
        $user = $request->user();

        $archivedPosts = Post::with(['category', 'purchaseRequests' => function ($query) {
                $query->where('status', 'approved')->with('user');
            }])
            ->where('user_id', $user->id)
            ->whereIn('status', ['sold', 'archived'])
            ->latest('updated_at')
            ->paginate(15);

        $totalArchived = Post::where('user_id', $user->id)->whereIn('status', ['sold', 'archived'])->count();
        $totalSold = Post::where('user_id', $user->id)->where('status', 'sold')->count();

        return view('admin.archive', compact('archivedPosts', 'totalArchived', 'totalSold'));
    }

    public function restorePost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->update(['status' => 'active']);

        return back()->with('success', 'E\'lon muvaffaqiyatli faollashtirildi va asosiy ro\'yxatga qaytarildi.');
    }

    public function archivePost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->update(['status' => 'archived']);

        return back()->with('success', 'E\'lon arxivga o\'tkazildi.');
    }

    public function updateProfile(Request $request): RedirectResponse
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

        return back()->with('success', 'Profil muvaffaqiyatli yangilandi.');
    }
}
