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

        $popularPosts = $baseQuery()
            ->withCount('likedByUsers')
            ->orderByDesc('liked_by_users_count')
            ->latest()
            ->take(12)
            ->get();

        $categories = Category::query()
            ->withCount(['posts' => fn ($q) => $q->approved()->whereNotIn('status', ['sold', 'archived'])])
            ->orderBy('name')
            ->get();

        // One showcase row per category that actually has listings.
        $categorySections = $categories
            ->filter(fn ($category) => $category->posts_count > 0)
            ->sortByDesc('posts_count')
            ->take(3)
            ->map(fn ($category) => [
                'category' => $category,
                'posts' => $baseQuery()->where('category_id', $category->id)->latest()->take(10)->get(),
            ])
            ->values();

        // "Daily essentials"-style strip: most listed breeds, each with a sample listing for the image.
        $topBreeds = $baseQuery()
            ->whereNotNull('breed')
            ->where('breed', '!=', '')
            ->latest()
            ->take(60)
            ->get()
            ->groupBy(fn ($post) => mb_convert_case(trim($post->breed), MB_CASE_TITLE))
            ->map(fn ($posts, $breed) => [
                'name' => $breed,
                'count' => $posts->count(),
                'sample' => $posts->first(fn ($p) => $p->imageUrl()) ?? $posts->first(),
                'min_price' => $posts->where('price', '>', 0)->min('price'),
                'currency' => $posts->first()->currency ?? 'UZS',
            ])
            ->sortByDesc('count')
            ->take(6)
            ->values();

        $stats = [
            'posts' => $categories->sum('posts_count'),
            // whereHas instead of User::role(): the latter throws when the role hasn't been seeded yet.
            'sellers' => User::whereHas('roles', fn ($q) => $q->where('name', 'seller'))->count(),
            'categories' => $categories->count(),
        ];

        $likedPostIds = auth()->check()
            ? auth()->user()->likedPosts()->pluck('posts.id')->all()
            : [];

        return view('home', compact('latestPosts', 'popularPosts', 'categories', 'categorySections', 'topBreeds', 'stats', 'likedPostIds'));
    }
}
