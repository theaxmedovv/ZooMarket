<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Support\ListingFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The one storefront page: "Eng arzon narxlarda" and "Mashhurlar", or search results
 * when the header search / filters / category tabs are used.
 */
class HomeController extends Controller
{
    /** Cards per "Yana 12 tasini ko'rish" step. */
    public const PER_PAGE = 12;

    public function index(Request $request)
    {
        $filters = ListingFilters::fromRequest($request);
        $likedPostIds = $this->likedPostIds();

        if ($filters->active()) {
            $results = $filters->apply($this->listed())->paginate(self::PER_PAGE, page: 1);
            $moreUrl = $this->moreUrl($request, 'results', $results);
            $category = $filters->categoryId ? Category::find($filters->categoryId) : null;

            return view('home', compact('filters', 'results', 'moreUrl', 'category', 'likedPostIds'));
        }

        $popular = $this->popular()->paginate(self::PER_PAGE, page: 1);
        $moreUrl = $this->moreUrl($request, 'popular', $popular);

        $cheapest = $this->listed()
            ->where('status', '!=', 'reserved')
            ->where('price', '>', 0)
            ->orderByPrice('asc')
            ->take(4)
            ->get();

        $stats = [
            'posts' => Post::listed()->count(),
            // Anyone can sell, so count the accounts that actually have a listing on sale.
            'sellers' => User::whereHas('posts', fn ($q) => $q->listed())->count(),
        ];

        $banners = Banner::active()->ordered()->get();

        return view('home', compact('filters', 'popular', 'moreUrl', 'cheapest', 'stats', 'banners', 'likedPostIds'));
    }

    /**
     * The next 12 cards for "Yana 12 tasini ko'rish" (?section=popular|results&page=N plus the filters).
     */
    public function more(Request $request): JsonResponse
    {
        $query = match ($request->query('section')) {
            'popular' => $this->popular(),
            'results' => ListingFilters::fromRequest($request)->apply($this->listed()),
            default => abort(404),
        };

        $posts = $query->paginate(self::PER_PAGE);

        return response()->json([
            'html' => view('partials.post-cards', ['posts' => $posts, 'likedPostIds' => $this->likedPostIds()])->render(),
            'next' => $this->moreUrl($request, $request->query('section'), $posts),
        ]);
    }

    private function listed(): Builder
    {
        return Post::with(['user', 'category'])->listed();
    }

    /**
     * Most liked first; equally liked listings newest first.
     */
    private function popular(): Builder
    {
        return $this->listed()
            ->withCount('likedByUsers')
            ->orderByDesc('liked_by_users_count')
            ->latest()
            ->latest('id'); // stable order for listings posted in the same second, so pages never repeat
    }

    private function moreUrl(Request $request, string $section, LengthAwarePaginator $posts): ?string
    {
        if (! $posts->hasMorePages()) {
            return null;
        }

        return route('home.more', array_merge($request->except('page', 'section'), [
            'section' => $section,
            'page' => $posts->currentPage() + 1,
        ]));
    }

    private function likedPostIds(): array
    {
        return auth()->check()
            ? auth()->user()->likedPosts()->pluck('posts.id')->all()
            : [];
    }
}
