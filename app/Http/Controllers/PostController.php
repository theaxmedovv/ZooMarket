<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Post;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    private function canCreatePosts(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('seller') || $user->can('create posts')
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (auth()->check()) {
            Gate::authorize('read posts');
        }

        $search = trim((string) $request->query('q', ''));
        $canUseFilters = !auth()->check() || auth()->user()->hasRole('user');
        $categoryId = $request->integer('category_id');
        $gender = (string) $request->query('gender', '');
        $location = trim((string) $request->query('location', ''));
        $currency = (string) $request->query('currency', '');
        $priceMin = $request->query('price_min');
        $priceMax = $request->query('price_max');

        if (! $canUseFilters) {
            $categoryId = null;
            $gender = '';
            $location = '';
            $currency = '';
            $priceMin = null;
            $priceMax = null;
        }

        $availableCategoryIds = Category::query()->pluck('id')->all();
        $allowedGenders = ['male', 'female'];
        $allowedCurrencies = ['UZS', 'USD', 'EUR', 'RUB'];

        if (! in_array($categoryId, $availableCategoryIds, true)) {
            $categoryId = null;
        }

        if (! in_array($gender, $allowedGenders, true)) {
            $gender = '';
        }

        if (! in_array($currency, $allowedCurrencies, true)) {
            $currency = '';
        }

        $priceMin = is_numeric($priceMin) ? (float) $priceMin : null;
        $priceMax = is_numeric($priceMax) ? (float) $priceMax : null;

        $postsQuery = Post::with(['user', 'category'])
            ->where('status', '!=', 'sold')
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($gender !== '', function ($query) use ($gender) {
                $query->where('gender', $gender);
            })
            ->when($location !== '', function ($query) use ($location) {
                $query->where('location', 'like', "%{$location}%");
            })
            ->when($currency !== '', function ($query) use ($currency) {
                $query->where('currency', $currency);
            })
            ->when($priceMin !== null, function ($query) use ($priceMin) {
                $query->where('price', '>=', $priceMin);
            })
            ->when($priceMax !== null, function ($query) use ($priceMax) {
                $query->where('price', '<=', $priceMax);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        if (auth()->check()) {
            $postsQuery->withCount('likedByUsers');
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        $likedPostIds = auth()->check()
            ? auth()->user()->likedPosts()->pluck('posts.id')->all()
            : [];

        $requestedAnimalIds = auth()->check() && auth()->user()->hasRole('user')
            ? PurchaseRequest::query()
                ->where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'approved'])
                ->pluck('animal_id')
                ->all()
            : [];

        $categories = Category::query()->orderBy('name')->get();

        $activeFilters = [
            'category_id' => $categoryId,
            'gender' => $gender,
            'location' => $location,
            'currency' => $currency,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
        ];

        return view('posts.index', compact('posts', 'search', 'likedPostIds', 'requestedAnimalIds', 'categories', 'activeFilters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless($this->canCreatePosts(), 403);

        $categories = Category::query()->orderBy('name')->get();

        return view('posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($this->canCreatePosts(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'breed' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'age' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:UZS,USD,EUR,RUB',
            'is_negotiable' => 'nullable|boolean',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,reserved,sold',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['content'] = $data['description'];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $request->user()->posts()->create($data);

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with(['user', 'category'])
            ->withCount('likedByUsers')
            ->findOrFail($id);

        abort_if($post->status === 'sold', 404);

        if (auth()->check()) {
            Gate::authorize('read posts');
        }

        $isLiked = auth()->check()
            && auth()->user()->likedPosts()->whereKey($post->id)->exists();

        $hasPurchaseRequest = auth()->check()
            && auth()->user()->hasRole('user')
            && PurchaseRequest::query()
                ->where('user_id', auth()->id())
                ->where('animal_id', $post->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

        return view('posts.show', compact('post', 'isLiked', 'hasPurchaseRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $post = Post::findOrFail($id);
        Gate::authorize('edit posts');

        $categories = Category::query()->orderBy('name')->get();

        return view('posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);
        Gate::authorize('edit posts');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'breed' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'age' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:UZS,USD,EUR,RUB',
            'is_negotiable' => 'nullable|boolean',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,reserved,sold',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['content'] = $data['description'];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can('delete posts')
                || $user->hasRole('seller')
                || $post->user_id === $user->id
            ),
            403
        );

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    public function toggleLike(Request $request, Post $post)
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('user'), 403);

        if ($user->likedPosts()->whereKey($post->id)->exists()) {
            $user->likedPosts()->detach($post->id);
            $message = 'Post unlike qilindi.';
        } else {
            $user->likedPosts()->attach($post->id);
            $message = 'Post yoqtirildi.';
        }

        return back()->with('success', $message);
    }

}
