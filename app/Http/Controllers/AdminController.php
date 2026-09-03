<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\DatabaseImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('profile.show');
    }

    public function archive(Request $request)
    {
        $user = $request->user();

        $archivedPosts = Post::withTrashed()
            ->with([
                'category',
                'purchaseRequests' => function ($query) {
                    $query->where('status', 'sold')->with(['user', 'chat'])->latest('updated_at');
                }
            ])
            ->where('user_id', $user->id)
            ->whereIn('status', ['sold', 'archived'])
            ->latest('updated_at')
            ->paginate(15);

        $totalArchived = Post::withTrashed()->where('user_id', $user->id)->whereIn('status', ['sold', 'archived'])->count();
        $totalSold = Post::withTrashed()->where('user_id', $user->id)->where('status', 'sold')->count();

        return view('admin.archive', compact('archivedPosts', 'totalArchived', 'totalSold'));
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
                DatabaseImageService::delete($user->avatar);
            }

            $validated['avatar'] = DatabaseImageService::store($request->file('avatar'), 'avatars');
        }

        $user->update($validated);

        return back()->with('success', 'Profil muvaffaqiyatli yangilandi.');
    }
}
