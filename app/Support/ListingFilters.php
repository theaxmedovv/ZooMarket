<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Search, filters and sort from the header search form (q, category_id, gender, location,
 * currency, price_min, price_max, sort). Invalid values are dropped rather than rejected.
 */
class ListingFilters
{
    public const CURRENCIES = ['UZS', 'USD', 'EUR', 'RUB'];

    public const SORTS = ['latest', 'price_asc', 'price_desc', 'oldest'];

    public function __construct(
        public string $search = '',
        public ?int $categoryId = null,
        public string $gender = '',
        public string $breed = '',
        public string $region = '',
        public string $location = '',
        public string $currency = '',
        public ?float $priceMin = null,
        public ?float $priceMax = null,
        public string $sort = 'latest',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $categoryId = $request->integer('category_id') ?: null;
        if ($categoryId && ! Category::whereKey($categoryId)->exists()) {
            $categoryId = null;
        }

        $gender = (string) $request->query('gender', '');
        $currency = (string) $request->query('currency', '');
        $sort = (string) $request->query('sort', 'latest');
        $priceMin = $request->query('price_min');
        $priceMax = $request->query('price_max');

        return new self(
            search: trim((string) $request->query('q', '')),
            categoryId: $categoryId,
            gender: in_array($gender, ['male', 'female'], true) ? $gender : '',
            breed: trim((string) $request->query('breed', '')),
            region: in_array($request->query('region'), config('zoomarket.regions'), true) ? $request->query('region') : '',
            location: trim((string) $request->query('location', '')),
            currency: in_array($currency, self::CURRENCIES, true) ? $currency : '',
            priceMin: is_numeric($priceMin) ? (float) $priceMin : null,
            priceMax: is_numeric($priceMax) ? (float) $priceMax : null,
            sort: in_array($sort, self::SORTS, true) ? $sort : 'latest',
        );
    }

    /**
     * Whether the visitor searched, filtered or sorted: the home page then shows results.
     */
    public function active(): bool
    {
        return $this->search !== '' || $this->categoryId || $this->gender !== '' || $this->breed !== '' || $this->region !== '' || $this->location !== ''
            || $this->currency !== '' || $this->priceMin !== null || $this->priceMax !== null || $this->sort !== 'latest';
    }

    public function apply(Builder $query): Builder
    {
        $query
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->gender !== '', fn ($q) => $q->where('gender', $this->gender))
            ->when($this->breed !== '', fn ($q) => $q->where('breed', $this->breed))
            ->when($this->region !== '', fn ($q) => $q->where('region', $this->region))
            ->when($this->location !== '', fn ($q) => $q->where('location', 'like', "%{$this->location}%"))
            ->when($this->currency !== '', fn ($q) => $q->where('currency', $this->currency))
            ->when($this->priceMin !== null, fn ($q) => $q->where('price', '>=', $this->priceMin))
            ->when($this->priceMax !== null, fn ($q) => $q->where('price', '<=', $this->priceMax))
            ->when($this->search !== '', fn ($q) => self::applySearch($q, $this->search));

        $query = match ($this->sort) {
            'price_asc' => $query->orderByPrice('asc'),
            'price_desc' => $query->orderByPrice('desc'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        // Tie-break on id so "Yana 12 tasini ko'rish" pages never repeat or skip a listing.
        return $this->sort === 'oldest' ? $query->oldest('id') : $query->latest('id');
    }

    public static function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('breed', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"));
        });
    }

    /**
     * The filters as the views use them (filter chips, header form).
     */
    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'gender' => $this->gender,
            'breed' => $this->breed,
            'region' => $this->region,
            'location' => $this->location,
            'currency' => $this->currency,
            'price_min' => $this->priceMin,
            'price_max' => $this->priceMax,
            'sort' => $this->sort,
        ];
    }
}
