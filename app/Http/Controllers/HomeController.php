<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
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
            // Anyone can sell, so count the accounts that actually have a listing on sale.
            'sellers' => User::whereHas('posts', fn ($q) => $q->approved()->whereNotIn('status', ['sold', 'archived']))->count(),
            'categories' => $categories->count(),
        ];

        $banners = Banner::active()->ordered()->get();

        $likedPostIds = auth()->check()
            ? auth()->user()->likedPosts()->pluck('posts.id')->all()
            : [];

        return view('home', compact('latestPosts', 'stats', 'likedPostIds', 'banners'));
    }
}
