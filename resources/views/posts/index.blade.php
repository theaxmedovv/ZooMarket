@extends('layouts.app')

@section('content')
@php
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
@endphp

<div class="container page-shell">

    @php
        $isSellerView = auth()->check() && auth()->user()->hasRole('seller');
        $pageTitle = match (true) {
            (bool) $currentCategory => $currentCategory->emoji . ' ' . $currentCategory->name,
            ! empty($search) => '"' . $search . "\" bo'yicha",
            $isSellerView => "Mening e'lonlarim",
            default => "Barcha e'lonlar",
        };
        $pageSubtitle = $posts->total() . " ta e'lon";
    @endphp
    <x-page-head :title="$pageTitle" :subtitle="$pageSubtitle">
        <x-slot:actions>
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
        </x-slot:actions>
    </x-page-head>

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
                    {{ $currentCategory->emoji }} {{ $currentCategory->name }} <i class="bi bi-x-circle-fill"></i>
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

    {{-- ── LISTING GRID ───────────────────────────── --}}
    <div class="listing-grid">
    @forelse($posts as $post)
        <div class="post-col">
            <article class="post-card">
                {{-- Card Image Header --}}
                <div class="card-img-wrap">
                    <a href="{{ route('posts.show', $post) }}" class="card-img-link" aria-label="{{ $post->title }}">
                        @if($post->image)
                            <div class="card-img-backdrop" style="background-image: url('{{ $post->imageUrl() }}');"></div>
                            <img class="card-img" src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy">
                        @else
                            <div class="card-img-placeholder">
                                <i class="bi bi-image"></i> Rasm yuklanmagan
                            </div>
                        @endif
                    </a>

                    {{-- Badges on Image --}}
                    <div class="card-badges-top">
                        <span class="card-tag-pill tag-cat">
                            {{ $post->category?->name ?? 'Hayvon' }}
                        </span>

                        @if($post->isSoldOut())
                            <span class="card-tag-pill tag-sold">
                                <i class="bi bi-x-circle-fill"></i> Sotilgan
                            </span>
                        @elseif($post->totalAvailableCount() > 0)
                            <span class="card-tag-pill tag-stock">
                                <i class="bi bi-box-seam-fill"></i> {{ $post->totalAvailableCount() }} ta
                            </span>
                        @endif

                        @if(auth()->check() && auth()->user()->hasRole('seller'))
                            @if($post->moderation_status === 'approved')
                                <span class="card-tag-pill" style="background: rgba(40, 167, 69, 0.85); color: #fff;">
                                    <i class="bi bi-shield-check"></i> Tasdiqlangan
                                </span>
                            @elseif($post->moderation_status === 'rejected')
                                <span class="card-tag-pill" style="background: rgba(220, 53, 69, 0.9); color: #fff;" title="{{ $post->moderation_reason }}">
                                    <i class="bi bi-shield-x"></i> Rad etilgan
                                </span>
                            @else
                                <span class="card-tag-pill" style="background: rgba(255, 193, 7, 0.9); color: #212529;">
                                    <i class="bi bi-clock-history"></i> AI tekshiruvida
                                </span>
                            @endif
                        @endif
                    </div>

                    {{-- Floating Favorite Button (Top Right) --}}
                    @if(auth()->check() && auth()->user()->hasRole('user'))
                        <form action="{{ route('posts.like', $post) }}" method="POST" class="card-heart-form" data-like-form>
                            @csrf
                            <button class="btn-card-heart {{ in_array($post->id, $likedPostIds ?? []) ? 'liked' : '' }}" type="submit" aria-label="Yoqtirish" title="Saqlash">
                                <i class="bi {{ in_array($post->id, $likedPostIds ?? []) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Card Body --}}
                <div class="card-body-inner">
                    {{-- Author row & Dropdown menu --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="card-author">
                            <span class="author-avatar">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</span>
                            <div class="author-meta">
                                <span class="author-name">{{ $post->user->name }}</span>
                                <small class="author-time">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        @if(auth()->check() && (auth()->user()->can('delete posts') || auth()->id() === $post->user_id))
                            <div class="dropdown">
                                <button class="btn-card-menu" data-bs-toggle="dropdown" aria-label="Amallar">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
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

                    {{-- Title --}}
                    <h2 class="card-title">
                        <a class="card-title-link" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                    </h2>

                    {{-- Quick Metadata Chips (Zot, Manzil, Jinsi) --}}
                    <div class="card-meta-chips">
                        @if($post->breed)
                            <span class="meta-chip" title="Zot">
                                <i class="bi bi-award text-lime"></i> {{ Str::limit($post->breed, 15) }}
                            </span>
                        @endif
                        @if($post->location)
                            <span class="meta-chip" title="Manzil">
                                <i class="bi bi-geo-alt text-orange"></i> {{ Str::limit($post->location, 14) }}
                            </span>
                        @endif
                        <span class="meta-chip" title="Jinsi">
                            @if($post->gender === 'mixed')
                                <i class="bi bi-gender-male text-info"></i>{{ $post->availableMaleCount() }} <i class="bi bi-gender-female text-danger ms-1"></i>{{ $post->availableFemaleCount() }}
                            @elseif($post->gender === 'female')
                                <i class="bi bi-gender-female text-danger"></i> Urg'ochi
                            @else
                                <i class="bi bi-gender-male text-info"></i> Erkak
                            @endif
                        </span>
                    </div>

                    {{-- Price & Negotiable --}}
                    <div class="card-price-row mt-auto pt-2">
                        <div class="d-flex align-items-baseline gap-1">
                            @if(!empty($post->price))
                                <span class="card-price-num">{{ number_format($post->price, 0, '.', ' ') }}</span>
                                <span class="card-price-curr">{{ $post->currency ?? 'UZS' }}</span>
                            @else
                                <span class="card-price-num text-muted" style="font-size: 1rem;">Kelishiladi</span>
                            @endif
                        </div>
                        @if($post->is_negotiable)
                            <span class="card-negotiable-tag">Kelishiladi</span>
                        @endif
                    </div>

                    {{-- Footer Action Buttons --}}
                    <div class="card-footer-inner">
                        <a class="btn-card-view" href="{{ route('posts.show', $post) }}">
                            <span>Batafsil</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        @if(auth()->check() && auth()->user()->hasRole('user'))
                            @if(!$post->isSoldOut())
                                <a class="btn-card-quick-buy" href="{{ route('posts.show', $post) }}" title="Sotib olish">
                                    <i class="bi bi-cart-check-fill me-1"></i> Xarid
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </article>
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
