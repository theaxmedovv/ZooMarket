@extends('layouts.app')

@section('content')
@php
    $showFilters = !auth()->check() || auth()->user()->hasRole('user');

    $activeCount = 0;
    if (!empty($activeFilters['category_id'])) $activeCount++;
    if (!empty($activeFilters['gender'])) $activeCount++;
    if (!empty($activeFilters['location'])) $activeCount++;
    if (!empty($activeFilters['currency'])) $activeCount++;
    if (($activeFilters['price_min'] ?? null) !== null) $activeCount++;
    if (($activeFilters['price_max'] ?? null) !== null) $activeCount++;

    $hasActiveFilters = $activeCount > 0;

    $currentCategory = !empty($activeFilters['category_id']) 
        ? $categories->firstWhere('id', $activeFilters['category_id']) 
        : null;

    $categoryEmojis = [
        'Big Cats' => '🐆',
        'Primates' => '🦍',
        'Reptiles' => '🐊',
        'Birds' => '🦜',
        'Ungulates' => '🦏',
        'Marine Life' => '🐋',
        'Insects' => '🦋',
        'Enrichment' => '🏗️',
        'Nutrition' => '🥩',
        'Habitats' => '🌿',
    ];

    $getCategoryEmoji = function($name) use ($categoryEmojis) {
        if (isset($categoryEmojis[$name])) {
            return $categoryEmojis[$name];
        }
        $n = mb_strtolower($name);
        if (str_contains($n, 'cat') || str_contains($n, 'mushuk') || str_contains($n, 'sher') || str_contains($n, 'yo\'lbars')) return '🐆';
        if (str_contains($n, 'primate') || str_contains($n, 'maymun') || str_contains($n, 'gorilla')) return '🦍';
        if (str_contains($n, 'reptil') || str_contains($n, 'sudral') || str_contains($n, 'toshbaqa') || str_contains($n, 'ilon')) return '🐊';
        if (str_contains($n, 'bird') || str_contains($n, 'qush') || str_contains($n, 'popugay')) return '🦜';
        if (str_contains($n, 'ungulate') || str_contains($n, 'ot') || str_contains($n, 'tuyoq') || str_contains($n, 'kiyik')) return '🦏';
        if (str_contains($n, 'marine') || str_contains($n, 'baliq') || str_contains($n, 'fish') || str_contains($n, 'kit')) return '🐋';
        if (str_contains($n, 'insect') || str_contains($n, 'hasharot') || str_contains($n, 'kapalak')) return '🦋';
        if (str_contains($n, 'enrichment') || str_contains($n, 'jihoz') || str_contains($n, 'qafas')) return '🏗️';
        if (str_contains($n, 'nutrition') || str_contains($n, 'oziq') || str_contains($n, 'korm') || str_contains($n, 'food')) return '🥩';
        if (str_contains($n, 'habitat') || str_contains($n, 'makon') || str_contains($n, 'o\'simlik')) return '🌿';
        if (str_contains($n, 'dog') || str_contains($n, 'it') || str_contains($n, 'kuchuk')) return '🐕';
        if (str_contains($n, 'rabbit') || str_contains($n, 'quyon') || str_contains($n, 'hamster')) return '🐰';
        return '🐾';
    };
@endphp

{{-- ── TOP CATEGORY QUICK SCROLLER ───────────────────────────── --}}
<section class="category-strip">
    <div class="container">
        <div class="category-strip-inner">
            <a class="category-strip-pill {{ empty($activeFilters['category_id']) ? 'active' : '' }}" href="{{ route('posts.index', request()->except('category_id', 'page')) }}">
                🐾 Barchasi
            </a>
            @foreach($categories as $category)
                @php $emoji = $getCategoryEmoji($category->name); @endphp
                <a class="category-strip-pill {{ (string)($activeFilters['category_id'] ?? '') === (string)$category->id ? 'active' : '' }}" href="{{ route('posts.index', array_merge(request()->except('page'), ['category_id' => $category->id])) }}">
                    {{ $emoji }} {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<div class="container page-shell">
    {{-- ── UNIFIED SEARCH & FILTER BAR (QIDIRUV VA FILTR YONMA-YON) ───────────────────────────── --}}
    @if($showFilters)
    <div class="search-filter-hero">
        <form action="{{ route('posts.index') }}" method="GET" id="searchFilterForm">
            @if(!empty($activeFilters['sort']))
                <input type="hidden" name="sort" value="{{ $activeFilters['sort'] }}">
            @endif

            <div class="search-filter-bar">
                {{-- Search Input with icon --}}
                <div class="search-input-group">
                    <i class="bi bi-search search-group-icon"></i>
                    <input type="text" name="q" value="{{ request('q', $search ?? '') }}" class="search-hero-input" placeholder="E'lonlar, zotlar, tavsif bo'yicha qidirish...">
                </div>

                {{-- Filter Button RIGHT NEXT TO SEARCH --}}
                <button class="btn-filter-trigger {{ $hasActiveFilters ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#filterExpandBox" aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}" aria-controls="filterExpandBox">
                    <i class="bi bi-sliders2"></i>
                    <span>Filtrlar</span>
                    @if($activeCount > 0)
                        <span class="badge filter-badge-count">{{ $activeCount }}</span>
                    @endif
                </button>

                {{-- Search Submit Button --}}
                <button type="submit" class="btn-search-submit">
                    <i class="bi bi-search"></i>
                    <span class="d-none d-sm-inline">Qidirish</span>
                </button>
            </div>

            {{-- ── EXPANDABLE DETAILED FILTER PANEL ───────────────────────────── --}}
            <div id="filterExpandBox" class="collapse {{ $hasActiveFilters ? 'show' : '' }}">
                <div class="filter-expand-box">
                    <div class="row g-3 align-items-end">
                        {{-- Category Filter --}}
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="filter-sec-label"><i class="bi bi-grid-3x3-gap me-1"></i> Kategoriya</label>
                            <select name="category_id" class="filter-select">
                                <option value="">Barcha kategoriyalar</option>
                                @foreach($categories as $category)
                                    @php $emoji = $getCategoryEmoji($category->name); @endphp
                                    <option value="{{ $category->id }}" @selected((string)($activeFilters['category_id'] ?? '') === (string)$category->id)>
                                        {{ $emoji }} {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Gender Filter --}}
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="filter-sec-label"><i class="bi bi-gender-ambiguous me-1"></i> Jinsi</label>
                            <div class="segmented-control">
                                <label class="segment-btn">
                                    <input type="radio" name="gender" value="" {{ empty($activeFilters['gender']) ? 'checked' : '' }}>
                                    <span>Hammasi</span>
                                </label>
                                <label class="segment-btn">
                                    <input type="radio" name="gender" value="male" {{ ($activeFilters['gender'] ?? '') === 'male' ? 'checked' : '' }}>
                                    <span>Erkak ♂</span>
                                </label>
                                <label class="segment-btn">
                                    <input type="radio" name="gender" value="female" {{ ($activeFilters['gender'] ?? '') === 'female' ? 'checked' : '' }}>
                                    <span>Urg'ochi ♀</span>
                                </label>
                            </div>
                        </div>

                        {{-- Price Range --}}
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="filter-sec-label mb-0"><i class="bi bi-tag me-1"></i> Narx oralig'i</label>
                                <select name="currency" class="filter-mini-select" title="Valyuta">
                                    <option value="">Valyuta</option>
                                    @foreach(['UZS','USD','EUR','RUB'] as $currency)
                                        <option value="{{ $currency }}" @selected(($activeFilters['currency'] ?? '') === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="price_min" class="filter-input" min="0" step="0.01" value="{{ $activeFilters['price_min'] ?? '' }}" placeholder="Min narx">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="price_max" class="filter-input" min="0" step="0.01" value="{{ $activeFilters['price_max'] ?? '' }}" placeholder="Max narx">
                                </div>
                            </div>
                        </div>

                        {{-- Location Filter --}}
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="filter-sec-label"><i class="bi bi-geo-alt me-1"></i> Manzil</label>
                            <div class="filter-input-wrap">
                                <i class="bi bi-geo-alt filter-input-icon"></i>
                                <input type="text" name="location" class="filter-input with-icon" value="{{ $activeFilters['location'] ?? '' }}" placeholder="Shahar yoki viloyat">
                            </div>
                        </div>

                        {{-- Actions in Panel --}}
                        <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top border-line">
                            <div class="text-muted small">
                                @if($hasActiveFilters)
                                    <span class="text-lime fw-semibold">{{ $activeCount }} ta filtr faol</span>
                                @else
                                    Qo'shimcha parametrlar bo'yicha saralash
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($hasActiveFilters)
                                    <a class="btn-filter-reset" href="{{ route('posts.index') }}">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Barchasini tozalash
                                    </a>
                                @endif
                                <button class="btn-filter-apply px-4" type="submit">
                                    <i class="bi bi-funnel-fill me-1"></i> Qo'llash
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endif

    {{-- ── ACTIVE FILTER CHIPS (LENTA) ───────────────────────────── --}}
    @if($hasActiveFilters || !empty($search))
        <div class="active-filter-chips">
            <span class="chips-label"><i class="bi bi-funnel me-1"></i> Faol:</span>

            @if(!empty($search))
                <a class="filter-chip" href="{{ route('posts.index', request()->except('q', 'page')) }}" title="Qidiruv filtrini olib tashlash">
                    Qidiruv: "{{ $search }}" <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif

            @if(!empty($activeFilters['category_id']) && $currentCategory)
                <a class="filter-chip" href="{{ route('posts.index', request()->except('category_id', 'page')) }}" title="Kategoriya filtrini olib tashlash">
                    {{ $getCategoryEmoji($currentCategory->name) }} {{ $currentCategory->name }} <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif

            @if(!empty($activeFilters['gender']))
                <a class="filter-chip" href="{{ route('posts.index', request()->except('gender', 'page')) }}" title="Jins filtrini olib tashlash">
                    Jinsi: {{ $activeFilters['gender'] === 'male' ? 'Erkak ♂' : 'Urg\'ochi ♀' }} <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif

            @if(!empty($activeFilters['location']))
                <a class="filter-chip" href="{{ route('posts.index', request()->except('location', 'page')) }}" title="Manzil filtrini olib tashlash">
                    Manzil: {{ $activeFilters['location'] }} <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif

            @if(!empty($activeFilters['currency']))
                <a class="filter-chip" href="{{ route('posts.index', request()->except('currency', 'page')) }}" title="Valyuta filtrini olib tashlash">
                    Valyuta: {{ $activeFilters['currency'] }} <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif

            @if(($activeFilters['price_min'] ?? null) !== null || ($activeFilters['price_max'] ?? null) !== null)
                <a class="filter-chip" href="{{ route('posts.index', request()->except('price_min', 'price_max', 'page')) }}" title="Narx filtrini olib tashlash">
                    Narx: {{ $activeFilters['price_min'] ?? '0' }} — {{ $activeFilters['price_max'] ?? '∞' }} {{ $activeFilters['currency'] ?? '' }} <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif

            <a class="filter-chip chip-clear-all" href="{{ route('posts.index') }}" title="Barcha filtrlarni tozalash">
                Hammasini tozalash <i class="bi bi-arrow-counterclockwise ms-1"></i>
            </a>
        </div>
    @endif

    {{-- ── CATALOG TOOLBAR (SARALASH VA NATIJALAR SONI) ───────────────────────────── --}}
    <div class="catalog-toolbar">
        <div>
            <h1 class="catalog-title">
                @if(auth()->check() && auth()->user()->hasRole('seller'))
                    @if($currentCategory)
                        {{ $getCategoryEmoji($currentCategory->name) }} {{ $currentCategory->name }} (E'lonlarim)
                    @elseif(!empty($search))
                        "{{ $search }}" bo'yicha e'lonlarim
                    @else
                        Mening e'lonlarim
                    @endif
                @else
                    @if($currentCategory)
                        {{ $getCategoryEmoji($currentCategory->name) }} {{ $currentCategory->name }}
                    @elseif(!empty($search))
                        "{{ $search }}" bo'yicha e'lonlar
                    @else
                        Barcha e'lonlar
                    @endif
                @endif
            </h1>
            <div class="result-meta">{{ $posts->total() }} ta e'lon mavjud</div>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            @if(auth()->check() && auth()->user()->hasRole('seller'))
                <a href="{{ route('posts.create') }}" class="btn-search-submit text-decoration-none d-none d-sm-inline-flex">
                    <i class="bi bi-plus-circle-fill"></i> Yangi e'lon
                </a>
            @endif

            {{-- Sort Dropdown --}}
            <div class="dropdown">
                <button class="btn-sort-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-arrow-down-up me-1 text-lime"></i>
                    @php
                        $sortLabel = match($activeFilters['sort'] ?? 'latest') {
                            'price_asc' => 'Narx: arzonroq',
                            'price_desc' => 'Narx: qimmatroq',
                            'oldest' => 'Eng eskisi',
                            default => 'Eng yangilari'
                        };
                    @endphp
                    <span>{{ $sortLabel }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                    <li><a class="dropdown-item {{ ($activeFilters['sort'] ?? 'latest') === 'latest' ? 'active' : '' }}" href="{{ route('posts.index', array_merge(request()->query(), ['sort' => 'latest'])) }}"><i class="bi bi-clock me-2"></i>Eng yangilari</a></li>
                    <li><a class="dropdown-item {{ ($activeFilters['sort'] ?? '') === 'price_asc' ? 'active' : '' }}" href="{{ route('posts.index', array_merge(request()->query(), ['sort' => 'price_asc'])) }}"><i class="bi bi-arrow-up-circle me-2"></i>Narx: arzonroq</a></li>
                    <li><a class="dropdown-item {{ ($activeFilters['sort'] ?? '') === 'price_desc' ? 'active' : '' }}" href="{{ route('posts.index', array_merge(request()->query(), ['sort' => 'price_desc'])) }}"><i class="bi bi-arrow-down-circle me-2"></i>Narx: qimmatroq</a></li>
                    <li><a class="dropdown-item {{ ($activeFilters['sort'] ?? '') === 'oldest' ? 'active' : '' }}" href="{{ route('posts.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}"><i class="bi bi-calendar me-2"></i>Eng eskisi</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ── LISTING GRID ───────────────────────────── --}}
    <div class="listing-grid">
    @forelse($posts as $post)
        <div class="post-col">
            <article class="post-card">
                <div class="card-img-wrap">
                    @if($post->image)
                        <div class="card-img-backdrop" style="background-image: url('{{ asset('storage/' . $post->image) }}');"></div>
                        <img class="card-img" src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" loading="lazy">
                    @else
                        <div class="card-img-placeholder"><i class="bi bi-image"></i> Rasm yuklanmagan</div>
                    @endif
                    <span class="card-badge">{{ $post->category?->name ?? 'E\'lon' }}</span>
                </div>
                <div class="card-body-inner">
                    <div class="card-author">
                        <span class="author-avatar">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</span>
                        <div>
                            <span>{{ $post->user->name }}</span>
                            <small class="author-time">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <h2 class="card-title">
                        <a class="card-title-link" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="card-desc">{{ Str::limit($post->description ?? $post->content, 90) }}</p>
                    @if(!empty($post->price))
                        <div class="card-price">{{ number_format($post->price) }} <small>{{ $post->currency ?? 'UZS' }}</small></div>
                    @else
                        <div class="card-price text-muted" style="font-size: 0.95rem;">Kelishiladi</div>
                    @endif
                    <div class="card-footer-inner">
                        <a class="btn-detail" href="{{ route('posts.show', $post) }}">Batafsil <i class="bi bi-arrow-right ms-1"></i></a>
                        <div class="card-actions">
                            @if(auth()->check() && auth()->user()->hasRole('user'))
                                @if(in_array($post->id, $requestedAnimalIds ?? []))
                                    <span class="action-tag tag-pending">So'ralgan</span>
                                @else
                                    <button class="btn-buy" type="button" data-bs-toggle="modal" data-bs-target="#buyModal{{ $post->id }}">Sotib olish</button>
                                @endif
                                <form action="{{ route('posts.like', $post) }}" method="POST">
                                    @csrf
                                    <button class="btn-icon {{ in_array($post->id, $likedPostIds ?? []) ? 'liked' : '' }}" type="submit" aria-label="Saqlash">
                                        <i class="bi {{ in_array($post->id, $likedPostIds ?? []) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                    </button>
                                </form>
                            @endif
                            @if(auth()->check() && (auth()->user()->can('delete posts') || auth()->id() === $post->user_id))
                                <div class="dropdown">
                                    <button class="btn-icon" data-bs-toggle="dropdown" aria-label="Amallar"><i class="bi bi-three-dots"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('posts.edit', $post) }}"><i class="bi bi-pencil me-2"></i>Tahrirlash</a></li>
                                        <li>
                                            <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('E\'lonni o\'chirishni xohlaysizmi?')">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>O'chirish</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
            @if(auth()->check() && auth()->user()->hasRole('user') && $post->status !== 'sold' && !in_array($post->id, $requestedAnimalIds ?? []))
                <div class="modal fade" id="buyModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content buy-modal">
                            <div class="modal-body p-4 text-center">
                                <div class="buy-modal-icon mb-3"><i class="bi bi-bag-check"></i></div>
                                <h5>Sotib olish so'rovini yuborish</h5>
                                <p class="text-muted"><strong>{{ $post->title }}</strong> bo'yicha sotuvchiga so'rov jo'natilsinmi?</p>
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <button class="btn-modal-cancel" data-bs-dismiss="modal">Bekor qilish</button>
                                    <form action="{{ route('purchase-requests.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="animal_id" value="{{ $post->id }}">
                                        <button class="btn-modal-confirm" type="submit">So'rov yuborish</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="col-12">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-collection"></i></div>
                @if(auth()->check() && auth()->user()->hasRole('seller'))
                    <h3 class="empty-title">Sizda hali faol e'lonlar yo'q</h3>
                    <p class="empty-text">Birinchi e'loningizni joylashtiring va xaridorlarga taklif qiling!</p>
                    <a href="{{ route('posts.create') }}" class="btn-filter-apply d-inline-flex mt-3 text-decoration-none px-4">
                        <i class="bi bi-plus-lg me-1"></i> Yangi e'lon qo'shish
                    </a>
                @else
                    <h3 class="empty-title">Hech qanday e'lon topilmadi</h3>
                    <p class="empty-text">Boshqa so'z bilan qidirib ko'ring yoki filtrlarni tozalang.</p>
                    <a href="{{ route('posts.index') }}" class="btn-filter-apply d-inline-flex mt-3 text-decoration-none px-4">Barcha e'lonlarni ko'rish</a>
                @endif
            </div>
        </div>
    @endforelse
    </div>

    @if($posts->hasPages())
        <div class="d-flex justify-content-center mt-5">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
