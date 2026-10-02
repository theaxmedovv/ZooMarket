<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        // Sellers manage their own listings; the storefront is for guests and buyers.
        if (auth()->check() && auth()->user()->hasRole('seller')) {
            return redirect()->route('posts.index');
        }

        $baseQuery = fn () => Post::with(['user', 'category'])
            ->approved()
            ->whereNotIn('status', ['sold', 'archived']);

        $latestPosts = $baseQuery()->latest()->take(12)->get();

        $categories = Category::query()
            ->withCount(['posts' => fn ($q) => $q->approved()->whereNotIn('status', ['sold', 'archived'])])
            ->orderBy('name')
            ->get();

        $stats = [
            'posts' => $categories->sum('posts_count'),
            // whereHas instead of User::role(): the latter throws when the role hasn't been seeded yet.
            'sellers' => User::whereHas('roles', fn ($q) => $q->where('name', 'seller'))->count(),
            'categories' => $categories->count(),
        ];

        $likedPostIds = auth()->check()
            ? auth()->user()->likedPosts()->pluck('posts.id')->all()
            : [];

        return view('home', compact('latestPosts', 'stats', 'likedPostIds'));
    }
}
