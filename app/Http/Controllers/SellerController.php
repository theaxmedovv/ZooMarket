<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * A seller's public page: who they are, how buyers rated them, and everything they have on sale.
 */
class SellerController extends Controller
{
    public function show(Request $request, User $user)
    {
        // Only accounts that can sell have a page (the main admin does not)
        abort_unless($user->hasRole('user'), 404);

        $viewer = $request->user();

        $posts = Post::with(['user', 'category'])
            ->listed()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12, ['*'], 'posts')
            ->withQueryString();

        $reviews = $user->reviewsReceived()
            ->with('reviewer')
            ->latest()
            ->paginate(10, ['*'], 'reviews')
            ->withQueryString();

        $ratingCount = $user->reviewsReceived()->count();
        $ratingAverage = $ratingCount > 0 ? round((float) $user->reviewsReceived()->avg('rating'), 1) : null;

        $stats = [
            'active' => Post::listed()->where('user_id', $user->id)->count(),
            'sold' => Post::withTrashed()->where('user_id', $user->id)->where('status', 'sold')->count(),
        ];

        $isSelf = $viewer && $viewer->is($user);
        $canReview = $viewer && ! $isSelf && $viewer->hasRole('user') && $viewer->canReview($user);
        $myReview = $viewer ? $user->reviewsReceived()->where('reviewer_id', $viewer->id)->first() : null;

        $likedPostIds = $viewer ? $viewer->likedPosts()->pluck('posts.id')->all() : [];

        return view('sellers.show', compact(
            'user', 'posts', 'reviews', 'ratingCount', 'ratingAverage', 'stats',
            'isSelf', 'canReview', 'myReview', 'likedPostIds'
        ));
    }
}
