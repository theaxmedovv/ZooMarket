<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()->favoritePosts()
            ->with(['user', 'category'])
            ->latest('post_likes.created_at')
            ->paginate(20);

        // Every card here is saved, so each heart starts filled.
        $likedPostIds = $posts->pluck('id')->all();

        return view('favorites.index', compact('posts', 'likedPostIds'));
    }
}
