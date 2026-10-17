<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The main admin's review queue: every listing waits here after the AI pre-check
 * and only becomes public once the admin approves it.
 */
class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->query('status')
            : 'pending';

        $posts = Post::with(['user', 'category', 'moderator'])
            ->where('moderation_status', $status)
            ->when($status === 'pending', fn ($q) => $q->oldest('updated_at'), fn ($q) => $q->latest('updated_at'))
            ->paginate(15)
            ->withQueryString();

        $counts = Post::query()
            ->selectRaw('moderation_status, count(*) as total')
            ->groupBy('moderation_status')
            ->pluck('total', 'moderation_status');

        return view('moderation.index', compact('posts', 'status', 'counts'));
    }

    public function approve(Request $request, Post $post): RedirectResponse
    {
        $post->update([
            'moderation_status' => 'approved',
            'moderation_reason' => null,
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);

        return back()->with('success', "\"{$post->title}\" tasdiqlandi va endi hammaga ko'rinadi.");
    }

    public function reject(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => "Rad etish sababini yozing — u sotuvchiga ko'rsatiladi.",
            'reason.min' => "Sabab kamida 5 ta belgidan iborat bo'lsin.",
        ]);

        $post->update([
            'moderation_status' => 'rejected',
            'moderation_reason' => $data['reason'],
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);

        return back()->with('success', "\"{$post->title}\" rad etildi. Sabab sotuvchiga ko'rsatiladi.");
    }
}
