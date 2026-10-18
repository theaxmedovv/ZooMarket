<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Chat;
use App\Models\Post;
use App\Models\PurchaseRequest;
use App\Services\DatabaseImageService;
use App\Services\GroqModerationService;
use App\Support\ListingFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    private function canCreatePosts(): bool
    {
        return (bool) auth()->user()?->can('create posts');
    }

    /**
     * Everyone holds the edit/delete permissions, so changes are limited to the listing's owner.
     */
    private function authorizeOwner(Post $post): void
    {
        abort_unless($post->user_id === auth()->id(), 403);
    }

    /**
     * "Mening e'lonlarim": the user's own listings in every moderation state.
     * The public storefront (search, filters, categories) is the home page.
     */
    public function mine(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $posts = Post::with(['user', 'category'])
            ->where('user_id', $request->user()->id)
            ->whereNotIn('status', ['sold', 'archived'])
            ->when($search !== '', fn ($q) => ListingFilters::applySearch($q, $search))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('posts.mine', compact('posts', 'search'));
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

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'breed' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:100',
            'gender' => 'required|in:male,female,mixed',
            'male_quantity' => 'nullable|integer|min:0|max:100',
            'female_quantity' => 'nullable|integer|min:0|max:100',
            'age' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:UZS,USD,EUR,RUB',
            'is_negotiable' => 'nullable|boolean',
            'region' => ['required', Rule::in(config('zoomarket.regions'))],
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,reserved,sold',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'region.required' => 'Viloyatni tanlang.',
            'region.in' => 'Ro\'yxatdagi viloyatlardan birini tanlang.',
            'location.required' => 'Shahar yoki tumanni yozing.',
        ]);

        $validator->after(function ($v) use ($request) {
            $quantity = (int) $request->input('quantity');
            $gender = $request->input('gender');
            $maleQty = (int) $request->input('male_quantity', 0);
            $femaleQty = (int) $request->input('female_quantity', 0);

            if ($quantity === 1 && $gender === 'mixed') {
                $v->errors()->add('gender', "Miqdor 1 bo'lganda faqat Erkak yoki Urg'ochi tanlanishi mumkin.");
            }

            if ($gender === 'mixed') {
                if ($maleQty < 1) {
                    $v->errors()->add('male_quantity', "Aralash tanlanganda kamida 1 ta erkak hayvon kiritilishi shart.");
                }
                if ($femaleQty < 1) {
                    $v->errors()->add('female_quantity', "Aralash tanlanganda kamida 1 ta urg'ochi hayvon kiritilishi shart.");
                }
                if (($maleQty + $femaleQty) !== $quantity) {
                    $v->errors()->add('male_quantity', "Erkak ({$maleQty}) va urg'ochi ({$femaleQty}) hayvonlar soni yig'indisi umumiy miqdorga ({$quantity}) teng bo'lishi kerak.");
                }
            }
        });

        $data = $validator->validate();

        $quantity = (int) $data['quantity'];
        if ($data['gender'] === 'mixed') {
            $data['male_quantity'] = (int) $data['male_quantity'];
            $data['female_quantity'] = (int) $data['female_quantity'];
        } elseif ($data['gender'] === 'male') {
            $data['male_quantity'] = $quantity;
            $data['female_quantity'] = 0;
        } else {
            $data['male_quantity'] = 0;
            $data['female_quantity'] = $quantity;
        }

        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['content'] = $data['description'];
        unset($data['images']);

        $uploadedImages = [];
        foreach ($request->file('images', []) as $file) {
            $uploadedImages[] = DatabaseImageService::store($file, 'posts');
        }

        if (!empty($uploadedImages)) {
            $data['image'] = $uploadedImages[0];
            $data['images'] = $uploadedImages;
        }

        $data['moderation_status'] = 'pending';
        $post = $request->user()->posts()->create($data);

        // AI pre-check; anything it lets through waits for the main admin
        $moderation = app(GroqModerationService::class)->moderate($post);

        if ($moderation['status'] === 'rejected') {
            return redirect()->route('posts.show', $post)
                ->with('warning', "E'lon avtomatik tekshiruvdan o'tmadi: " . ($moderation['reason'] ?? 'Xavfsizlik qoidalariga mos kelmadi.'));
        }

        return redirect()->route('posts.show', $post)
            ->with('info', "E'lon tekshiruvda. Biroz vaqtdan keyin tasdiqlanadi va hammaga ko'rinadi.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with(['user', 'category'])
            ->withCount('likedByUsers')
            ->findOrFail($id);

        if ($post->moderation_status !== 'approved') {
            $user = auth()->user();
            if (! $user || ($post->user_id !== $user->id && ! $user->hasRole('admin'))) {
                abort(404, "Ushbu e'lon mavjud emas yoki hali tasdiqlanmagan.");
            }
        }

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
        $post = Post::withTrashed()->findOrFail($id);
        Gate::authorize('edit posts');
        $this->authorizeOwner($post);

        if ($post->status === 'sold' || $post->status === 'archived' || $post->trashed()) {
            abort(403, "Arxivlangan yoki sotilgan e'lonlar faqat o'qish uchun (read-only) saqlanadi va ularni tahrirlab bo'lmaydi.");
        }

        $categories = Category::query()->orderBy('name')->get();

        return view('posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        Gate::authorize('edit posts');
        $this->authorizeOwner($post);

        if ($post->status === 'sold' || $post->status === 'archived' || $post->trashed()) {
            abort(403, "Arxivlangan yoki sotilgan e'lonlar faqat o'qish uchun (read-only) saqlanadi va ularni tahrirlab bo'lmaydi.");
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'breed' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:100',
            'gender' => 'required|in:male,female,mixed',
            'male_quantity' => 'nullable|integer|min:0|max:100',
            'female_quantity' => 'nullable|integer|min:0|max:100',
            'age' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:UZS,USD,EUR,RUB',
            'is_negotiable' => 'nullable|boolean',
            'region' => ['required', Rule::in(config('zoomarket.regions'))],
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,reserved,sold',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'region.required' => 'Viloyatni tanlang.',
            'region.in' => 'Ro\'yxatdagi viloyatlardan birini tanlang.',
            'location.required' => 'Shahar yoki tumanni yozing.',
        ]);

        $validator->after(function ($v) use ($request) {
            $quantity = (int) $request->input('quantity');
            $gender = $request->input('gender');
            $maleQty = (int) $request->input('male_quantity', 0);
            $femaleQty = (int) $request->input('female_quantity', 0);

            if ($quantity === 1 && $gender === 'mixed') {
                $v->errors()->add('gender', "Miqdor 1 bo'lganda faqat Erkak yoki Urg'ochi tanlanishi mumkin.");
            }

            if ($gender === 'mixed') {
                if ($maleQty < 1) {
                    $v->errors()->add('male_quantity', "Aralash tanlanganda kamida 1 ta erkak hayvon kiritilishi shart.");
                }
                if ($femaleQty < 1) {
                    $v->errors()->add('female_quantity', "Aralash tanlanganda kamida 1 ta urg'ochi hayvon kiritilishi shart.");
                }
                if (($maleQty + $femaleQty) !== $quantity) {
                    $v->errors()->add('male_quantity', "Erkak ({$maleQty}) va urg'ochi ({$femaleQty}) hayvonlar soni yig'indisi umumiy miqdorga ({$quantity}) teng bo'lishi kerak.");
                }
            }
        });

        $data = $validator->validate();

        $quantity = (int) $data['quantity'];
        if ($data['gender'] === 'mixed') {
            $data['male_quantity'] = (int) $data['male_quantity'];
            $data['female_quantity'] = (int) $data['female_quantity'];
        } elseif ($data['gender'] === 'male') {
            $data['male_quantity'] = $quantity;
            $data['female_quantity'] = 0;
        } else {
            $data['male_quantity'] = 0;
            $data['female_quantity'] = $quantity;
        }

        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['content'] = $data['description'];
        unset($data['images']);

        if ($request->hasFile('images')) {
            foreach ($post->allImages() as $old) {
                DatabaseImageService::delete($old);
            }
            $uploadedImages = [];
            foreach ($request->file('images') as $file) {
                $uploadedImages[] = DatabaseImageService::store($file, 'posts');
            }
            $data['image'] = $uploadedImages[0];
            $data['images'] = $uploadedImages;
        }

        $data['moderation_status'] = 'pending';
        $post->update($data);

        // Any edit sends the listing back through the AI pre-check and the admin queue
        $moderation = app(GroqModerationService::class)->moderate($post);

        if ($moderation['status'] === 'rejected') {
            return redirect()->route('posts.show', $post)
                ->with('warning', "E'lon yangilandi, biroq avtomatik tekshiruvdan o'tmadi: " . ($moderation['reason'] ?? 'Xavfsizlik qoidalariga mos kelmadi.'));
        }

        return redirect()->route('posts.show', $post)
            ->with('info', "E'lon yangilandi va qayta tekshiruvga yuborildi.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        Gate::authorize('delete posts');
        $this->authorizeOwner($post);

        if ($post->status === 'sold' || $post->status === 'archived') {
            return back()->withErrors(['error' => 'Sotilgan yoki arxivlangan e\'lonlar arxivda saqlanadi va ularni o\'chirib bo\'lmaydi.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($post) {
            // Update active requests to rejected so listing is no longer active in request system
            \App\Models\PurchaseRequest::where('animal_id', $post->id)
                ->whereIn('status', ['pending', 'approved'])
                ->update(['status' => 'rejected']);

            // Close all related chats
            \App\Models\Chat::where('post_id', $post->id)
                ->update(['closed_at' => now()]);

            foreach ($post->allImages() as $img) {
                DatabaseImageService::delete($img);
            }

            $post->delete();
        });

        return redirect()->route('posts.mine')->with('success', 'E\'lon muvaffaqiyatli o\'chirildi va tegishli so\'rovlar yangilandi.');
    }

    public function toggleLike(Request $request, Post $post)
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('user'), 403);
        abort_unless($post->moderation_status === 'approved', 404);

        $liked = ! $user->likedPosts()->whereKey($post->id)->exists();

        if ($liked) {
            $user->likedPosts()->attach($post->id);
            $message = "Sevimlilarga qo'shildi.";
        } else {
            $user->likedPosts()->detach($post->id);
            $message = 'Sevimlilardan olib tashlandi.';
        }

        // The heart buttons toggle in place via fetch; plain form posts still redirect back.
        if ($request->expectsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes' => $post->likedByUsers()->count(),
                'favorites' => $user->favoritePosts()->count(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

}
