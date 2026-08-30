<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Chat;
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
        $sort = (string) $request->query('sort', 'latest');

        if (! $canUseFilters) {
            $categoryId = null;
            $gender = '';
            $location = '';
            $currency = '';
            $priceMin = null;
            $priceMax = null;
            $sort = 'latest';
        }

        $availableCategoryIds = Category::query()->pluck('id')->all();
        $allowedGenders = ['male', 'female'];
        $allowedCurrencies = ['UZS', 'USD', 'EUR', 'RUB'];
        $allowedSorts = ['latest', 'price_asc', 'price_desc', 'oldest'];

        if (! in_array($categoryId, $availableCategoryIds, true)) {
            $categoryId = null;
        }

        if (! in_array($gender, $allowedGenders, true)) {
            $gender = '';
        }

        if (! in_array($currency, $allowedCurrencies, true)) {
            $currency = '';
        }

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        $priceMin = is_numeric($priceMin) ? (float) $priceMin : null;
        $priceMax = is_numeric($priceMax) ? (float) $priceMax : null;

        $postsQuery = Post::with(['user', 'category'])
            ->where('status', '!=', 'sold');

        if (auth()->check() && auth()->user()->hasRole('seller')) {
            $postsQuery->where('user_id', auth()->id());
        }

        $postsQuery->when($categoryId, function ($query) use ($categoryId) {
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
            });

        if ($sort === 'price_asc') {
            $postsQuery->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $postsQuery->orderBy('price', 'desc');
        } elseif ($sort === 'oldest') {
            $postsQuery->oldest();
        } else {
            $postsQuery->latest();
        }

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
            'sort' => $sort,
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
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['content'] = $data['description'];
        unset($data['images']);

        $uploadedImages = [];
        foreach ($request->file('images', []) as $file) {
            $uploadedImages[] = $file->store('posts', 'public');
        }

        if (!empty($uploadedImages)) {
            $data['image'] = $uploadedImages[0];
            $data['images'] = $uploadedImages;
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

        if ($post->status === 'sold') {
            $user = auth()->user();
            if (! $user) {
                abort(404);
            }
            $isPostOwner     = $post->user_id === $user->id;
            $isApprovedBuyer = PurchaseRequest::where('user_id', $user->id)
                ->where('animal_id', $post->id)
                ->where('status', 'approved')
                ->exists();
            abort_unless($isPostOwner || $isApprovedBuyer, 404);
        }

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

        $chat = null;
        if (auth()->check()) {
            $uid  = auth()->id();
            $chat = Chat::where('post_id', $post->id)
                ->where(function ($q) use ($uid) {
                    $q->where('buyer_id', $uid)->orWhere('seller_id', $uid);
                })->first();
        }

        return view('posts.show', compact('post', 'isLiked', 'hasPurchaseRequest', 'chat'));
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
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['content'] = $data['description'];
        unset($data['images']);

        if ($request->hasFile('images')) {
            foreach ($post->allImages() as $old) {
                Storage::disk('public')->delete($old);
            }
            $uploadedImages = [];
            foreach ($request->file('images') as $file) {
                $uploadedImages[] = $file->store('posts', 'public');
            }
            $data['image'] = $uploadedImages[0];
            $data['images'] = $uploadedImages;
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
