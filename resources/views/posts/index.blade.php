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

    $isSellerView = auth()->check() && auth()->user()->hasRole('seller');
    $isBuyer = auth()->check() && auth()->user()->hasRole('user');
    $pageTitle = match (true) {
        (bool) $currentCategory => $currentCategory->emoji . ' ' . $currentCategory->name,
        ! empty($search) => '"' . $search . "\" bo'yicha",
        $isSellerView => "Mening e'lonlarim",
        default => "Barcha e'lonlar",
    };
    $pageSubtitle = $posts->total() . " ta e'lon";

    $currentSort = $activeFilters['sort'] ?? 'latest';
    $sorts = [
        'latest' => ['bi-clock', 'Eng yangilari'],
        'price_asc' => ['bi-arrow-up-circle', 'Narx: arzonroq'],
        'price_desc' => ['bi-arrow-down-circle', 'Narx: qimmatroq'],
        'oldest' => ['bi-calendar', 'Eng eskisi'],
    ];

    $menu = 'absolute top-[calc(100%+4px)] right-0 z-20 min-w-[170px] rounded-[10px] border border-line bg-white p-1.5 shadow-lg';
    $menuItem = 'flex w-full cursor-pointer items-center gap-2 rounded-md px-3 py-1.5 text-left text-[13px] text-ink hover:bg-brand/12 hover:text-brand current:bg-brand/12 current:text-brand';
    $chip = 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors';
    $pill = 'inline-flex items-center gap-1 rounded-full px-2.5 py-[3px] text-[11px] font-bold tracking-wide shadow-[0_4px_12px_rgba(0,0,0,.12)] backdrop-blur-md';
    $metaChip = 'inline-flex items-center gap-1 rounded-md border border-black/6 bg-black/[.035] px-[7px] py-0.5 text-xs text-muted';
@endphp

<div class="page">
    <x-page-head :title="$pageTitle" :subtitle="$pageSubtitle">
        <x-slot:actions>
            {{-- Sort dropdown --}}
            <div class="relative">
                <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="sortMenu"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-1.5 text-[13px] font-semibold text-ink transition hover:border-brand hover:text-brand aria-expanded:border-brand aria-expanded:text-brand">
                    <i class="bi bi-arrow-down-up text-brand"></i>
                    <span>{{ $sorts[$currentSort][1] ?? $sorts['latest'][1] }}</span>
                    <i class="bi bi-chevron-down text-[10px]"></i>
                </button>
                <div id="sortMenu" hidden class="{{ $menu }}">
                    @foreach($sorts as $key => [$icon, $label])
                        <a href="{{ route('posts.index', array_merge(request()->query(), ['sort' => $key])) }}" @if($currentSort === $key) aria-current="true" @endif class="{{ $menuItem }}"><i class="bi {{ $icon }}"></i>{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </x-slot:actions>
    </x-page-head>

    {{-- Active filter chips --}}
    @if($hasActiveFilters || !empty($search))
        @php
            $chips = [];
            if (!empty($search)) $chips[] = [['q'], 'Qidiruv: "' . $search . '"', 'Qidiruv filtrini olib tashlash'];
            if (!empty($activeFilters['category_id']) && $currentCategory) $chips[] = [['category_id'], $currentCategory->emoji . ' ' . $currentCategory->name, 'Kategoriya filtrini olib tashlash'];
            if (!empty($activeFilters['gender'])) $chips[] = [['gender'], 'Jinsi: ' . ($activeFilters['gender'] === 'male' ? 'Erkak ♂' : "Urg'ochi ♀"), 'Jins filtrini olib tashlash'];
            if (!empty($activeFilters['location'])) $chips[] = [['location'], 'Manzil: ' . $activeFilters['location'], 'Manzil filtrini olib tashlash'];
            if (!empty($activeFilters['currency'])) $chips[] = [['currency'], 'Valyuta: ' . $activeFilters['currency'], 'Valyuta filtrini olib tashlash'];
            if (($activeFilters['price_min'] ?? null) !== null || ($activeFilters['price_max'] ?? null) !== null) {
                $chips[] = [['price_min', 'price_max'], 'Narx: ' . ($activeFilters['price_min'] ?? '0') . ' — ' . ($activeFilters['price_max'] ?? '∞') . ' ' . ($activeFilters['currency'] ?? ''), 'Narx filtrini olib tashlash'];
            }
        @endphp
        <div class="mb-4 flex flex-wrap items-center gap-1.5 rounded-xl border border-line bg-white/70 px-3 py-2">
            <span class="mr-1 inline-flex items-center gap-1 text-xs font-semibold text-muted"><i class="bi bi-funnel"></i> Faol:</span>
            @foreach($chips as [$keys, $label, $title])
                <a class="{{ $chip }} border-brand/25 bg-brand/9 text-brand hover:border-accent/40 hover:bg-accent/15 hover:text-accent" href="{{ route('posts.index', request()->except([...$keys, 'page'])) }}" title="{{ $title }}">
                    {{ $label }} <i class="bi bi-x-circle-fill text-xs opacity-80"></i>
                </a>
            @endforeach
            <a class="{{ $chip }} border-line bg-black/5 text-muted hover:border-danger hover:bg-danger/15 hover:text-danger" href="{{ route('posts.index') }}" title="Barcha filtrlarni tozalash">
                Hammasini tozalash <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    @endif

    {{-- Listing grid --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-[repeat(auto-fill,minmax(270px,1fr))]">
    @forelse($posts as $post)
        @php $liked = in_array($post->id, $likedPostIds ?? []); @endphp
        <article class="group relative flex min-h-[430px] flex-col overflow-hidden rounded-[18px] border border-line bg-linear-to-b from-white/70 to-white/95 transition duration-300 ease-[cubic-bezier(.2,0,0,1)] hover:-translate-y-[5px] hover:border-brand/45 hover:shadow-[0_16px_36px_rgba(0,0,0,.12),0_0_0_1px_rgba(0,142,204,.15)]">
            {{-- Image --}}
            <div class="relative h-[260px] w-full overflow-hidden border-b border-line bg-soft sm:h-[225px]">
                <a href="{{ route('posts.show', $post) }}" class="relative flex size-full items-center justify-center overflow-hidden" aria-label="{{ $post->title }}">
                    @if($post->image)
                        <div class="pointer-events-none absolute -inset-3.5 scale-115 bg-cover bg-center opacity-85 blur-[18px] brightness-[.28] saturate-[1.3]" style="background-image: url('{{ $post->imageUrl() }}');"></div>
                        <img class="relative z-[1] size-full object-cover transition-transform duration-450 ease-[cubic-bezier(.2,0,0,1)] group-hover:scale-[1.07]" src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy">
                    @else
                        <div class="flex size-full items-center justify-center gap-2 bg-soft text-sm text-muted">
                            <i class="bi bi-image"></i> Rasm yuklanmagan
                        </div>
                    @endif
                </a>

                {{-- Badges on image --}}
                <div class="pointer-events-none absolute top-2.5 left-2.5 z-[3] flex flex-wrap items-center gap-1.5">
                    <span class="{{ $pill }} border border-black/12 bg-white/82 text-ink">{{ $post->category?->name ?? 'Hayvon' }}</span>

                    @if($post->isSoldOut())
                        <span class="{{ $pill }} border border-red-500 bg-red-500/90 text-white"><i class="bi bi-x-circle-fill"></i> Sotilgan</span>
                    @elseif($post->totalAvailableCount() > 0)
                        <span class="{{ $pill }} border border-brand bg-brand/90 text-white"><i class="bi bi-box-seam-fill"></i> {{ $post->totalAvailableCount() }} ta</span>
                    @endif

                    @if($isSellerView)
                        @if($post->moderation_status === 'approved')
                            <span class="{{ $pill }} bg-green-600/85 text-white"><i class="bi bi-shield-check"></i> Tasdiqlangan</span>
                        @elseif($post->moderation_status === 'rejected')
                            <span class="{{ $pill }} bg-danger/90 text-white" title="{{ $post->moderation_reason }}"><i class="bi bi-shield-x"></i> Rad etilgan</span>
                        @else
                            <span class="{{ $pill }} bg-amber-400/90 text-ink"><i class="bi bi-clock-history"></i> AI tekshiruvida</span>
                        @endif
                    @endif
                </div>

                {{-- Favourite button --}}
                @if($isBuyer)
                    <form action="{{ route('posts.like', $post) }}" method="POST" class="absolute top-2.5 right-2.5 z-[4]" data-like-form>
                        @csrf
                        <button type="submit" aria-pressed="{{ $liked ? 'true' : 'false' }}" aria-label="Yoqtirish" title="Saqlash"
                                class="flex size-[34px] cursor-pointer items-center justify-center rounded-full border border-black/15 bg-white/75 text-sm text-ink backdrop-blur-md transition hover:scale-110 hover:border-accent hover:bg-accent/20 hover:text-accent aria-pressed:border-accent aria-pressed:bg-accent/25 aria-pressed:text-accent">
                            <i class="bi {{ $liked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                        </button>
                    </form>
                @endif
            </div>

            {{-- Body --}}
            <div class="flex flex-1 flex-col px-4 pt-3.5 pb-4">
                {{-- Author row & actions menu --}}
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="grid size-[26px] shrink-0 place-items-center rounded-full border border-brand/30 bg-brand-tint text-xs font-bold text-brand">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</span>
                        <div class="leading-tight">
                            <span class="block text-xs font-semibold text-ink">{{ $post->user->name }}</span>
                            <small class="text-[11px] text-faint">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if(auth()->check() && (auth()->user()->can('delete posts') || auth()->id() === $post->user_id))
                        <div class="relative">
                            <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="postMenu{{ $post->id }}" aria-label="Amallar"
                                    class="flex size-[26px] cursor-pointer items-center justify-center rounded-md text-muted transition-colors hover:bg-black/6 hover:text-ink">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div id="postMenu{{ $post->id }}" hidden class="{{ $menu }} min-w-[140px]">
                                <a class="{{ $menuItem }}" href="{{ route('posts.edit', $post) }}"><i class="bi bi-pencil"></i>Tahrirlash</a>
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('E\'lonni o\'chirishni xohlaysizmi?')">
                                    @csrf @method('DELETE')
                                    <button class="{{ $menuItem }} text-danger hover:bg-danger/15 hover:text-danger" type="submit"><i class="bi bi-trash"></i>O'chirish</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <h2 class="mt-1 mb-2 text-[17px] leading-snug font-bold tracking-tight">
                    <a class="line-clamp-2 text-ink transition-colors hover:text-brand" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                </h2>

                {{-- Breed, location, gender --}}
                <div class="mb-3 flex flex-wrap gap-1.5">
                    @if($post->breed)
                        <span class="{{ $metaChip }}" title="Zot"><i class="bi bi-award text-brand"></i> {{ Str::limit($post->breed, 15) }}</span>
                    @endif
                    @if($post->location)
                        <span class="{{ $metaChip }}" title="Manzil"><i class="bi bi-geo-alt text-accent"></i> {{ Str::limit($post->location, 14) }}</span>
                    @endif
                    <span class="{{ $metaChip }}" title="Jinsi">
                        @if($post->gender === 'mixed')
                            <i class="bi bi-gender-male text-male"></i>{{ $post->availableMaleCount() }} <i class="bi bi-gender-female ml-1 text-danger"></i>{{ $post->availableFemaleCount() }}
                        @elseif($post->gender === 'female')
                            <i class="bi bi-gender-female text-danger"></i> Urg'ochi
                        @else
                            <i class="bi bi-gender-male text-male"></i> Erkak
                        @endif
                    </span>
                </div>

                {{-- Price --}}
                <div class="mt-auto mb-3 flex items-center justify-between gap-2 pt-2">
                    <div class="flex items-baseline gap-1">
                        @if(!empty($post->price))
                            <span class="text-xl font-extrabold tracking-tight text-brand">{{ number_format($post->price, 0, '.', ' ') }}</span>
                            <span class="text-xs font-semibold text-ink/70">{{ $post->currency ?? 'UZS' }}</span>
                        @else
                            <span class="font-extrabold text-muted">Kelishiladi</span>
                        @endif
                    </div>
                    @if($post->is_negotiable)
                        <span class="rounded-full border border-brand/25 bg-brand/10 px-[7px] py-0.5 text-[11px] font-semibold text-brand">Kelishiladi</span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 border-t border-black/5 pt-2.5">
                    <a class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-[10px] border border-line bg-black/5 text-[13px] font-semibold text-ink transition hover:border-brand hover:bg-brand/12 hover:text-brand" href="{{ route('posts.show', $post) }}">
                        <span>Batafsil</span> <i class="bi bi-arrow-right"></i>
                    </a>
                    @if($isBuyer && !$post->isSoldOut())
                        <a class="inline-flex h-9 items-center justify-center gap-1 rounded-[10px] bg-brand px-3 text-xs font-bold text-white transition hover:-translate-y-px hover:bg-brand-dark hover:shadow-[0_4px_12px_rgba(0,142,204,.25)]" href="{{ route('posts.show', $post) }}" title="Sotib olish">
                            <i class="bi bi-cart-check-fill"></i> Xarid
                        </a>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <x-empty-state class="col-span-full" icon="bi-collection"
            :title="$isSellerView ? 'Sizda hali faol e\'lonlar yo\'q' : 'Hech qanday e\'lon topilmadi'"
            :text="$isSellerView ? 'Birinchi e\'loningizni joylashtiring va xaridorlarga taklif qiling!' : 'Boshqa so\'z bilan qidirib ko\'ring yoki filtrlarni tozalang.'">
            @if($isSellerView)
                <a href="{{ route('posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Yangi e'lon qo'shish</a>
            @else
                <a href="{{ route('posts.index') }}" class="btn btn-primary">Barcha e'lonlarni ko'rish</a>
            @endif
        </x-empty-state>
    @endforelse
    </div>

    @if($posts->hasPages())
        <div class="mt-12">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
