@php
    $zmUser = auth()->user();
    $zmIsAdmin = $zmUser?->hasRole('admin') ?? false;
    $zmProfileUrl = $zmUser ? route('profile.show') : route('login');
    // The home page is the one storefront: search and category links all show their results there.
    // "My listings" has its own page and searches only the user's own listings.
    $zmMine = request()->routeIs('posts.mine');
    $zmCategories = $navCategories ?? collect();
    $zmOnListing = request()->routeIs('home');
    $zmActiveCategory = $zmOnListing ? (int) request()->query('category_id') : 0;
    // On the home page, switching category keeps the other active filters.
    $zmKeep = $zmOnListing ? request()->except('category_id', 'page') : [];
    $zmUnread = (int) ($globalUnreadCount ?? 0);
    $zmFavorites = (int) ($favoritesCount ?? 0);

    // Every message a redirect can carry, as toasts. Error keys listed here are ones
    // no page renders inline (e.g. a failed purchase request bounced back to the listing).
    $zmToasts = [];
    foreach (['success', 'info', 'warning', 'error'] as $zmType) {
        if (session($zmType)) {
            $zmToasts[] = ['type' => $zmType, 'text' => session($zmType)];
        }
    }
    $zmErrorBag = session('errors')?->getBag('default');
    foreach (['purchase_request', 'quantity', 'gender', 'animal_id', 'error'] as $zmKey) {
        if ($zmErrorBag?->has($zmKey)) {
            $zmToasts[] = ['type' => 'error', 'text' => $zmErrorBag->first($zmKey)];
        }
    }
    $zmPending = (int) ($pendingRequestsCount ?? 0);
    $zmModeration = (int) ($pendingModerationCount ?? 0);
    $zmPendingBanners = (int) ($pendingBannersCount ?? 0);

    // Shared class lists for the chrome below.
    $zmBadge = 'grid h-[18px] min-w-[18px] place-items-center rounded-full bg-hot px-[5px] text-[11px] leading-none font-extrabold not-italic text-white';
    $zmAction = 'relative inline-flex h-10 cursor-pointer items-center gap-2 rounded-[10px] px-2 font-bold whitespace-nowrap text-muted hover:bg-brand-soft hover:text-brand current:bg-brand-soft current:text-brand md:px-3';
    $zmActionIcon = 'text-xl leading-none text-brand';
    $zmMenuItem = 'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-ink hover:bg-brand-soft hover:text-brand';
@endphp

{{-- Top bar: information only (every destination lives in the header or tab row) --}}
<div class="bg-soft text-[13px] text-muted">
    <div class="wrap flex h-10 items-center justify-center gap-4 lg:justify-between">
        <span class="hidden lg:inline">ZooMarket'ga xush kelibsiz — uy hayvonlari bozori!</span>
        <div class="flex items-center divide-x divide-[#d9d9d9] whitespace-nowrap *:inline-flex *:items-center *:gap-1.5 *:px-2.5 md:*:px-4 [&>:first-child]:pl-0 [&>:last-child]:pr-0 [&_.bi]:text-[15px] [&_.bi]:text-brand">
            <span><i class="bi bi-shield-check"></i>Har bir e'lon moderatsiyadan o'tadi</span>
            <a href="tel:+998712000000" class="hover:text-brand max-md:hidden"><i class="bi bi-telephone"></i>+998 71 200 00 00</a>
        </div>
    </div>
</div>

{{-- Header --}}
<header id="zmHeader" class="sticky top-0 z-30 border-b border-line bg-white">
    <div class="wrap flex flex-wrap items-center gap-3 py-3 md:h-16 md:flex-nowrap md:gap-4 md:py-0">
        <a href="{{ route('home') }}" class="shrink-0 text-2xl leading-none font-black tracking-tight text-brand md:text-2xl">ZooMarket</a>

        {{-- Katalog (category menu) sits left of the one search form for the whole site --}}
        <div class="relative order-2 shrink-0 md:order-none">
            <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="zmCatalog" class="btn btn-primary h-10 gap-1.5 rounded-lg px-3 text-[13px] md:px-4">
                <i class="bi bi-grid-fill"></i><span class="hidden sm:inline">Katalog</span>
            </button>
            {{-- Mega menu: categories on the left; hovering one shows its breeds, cities and genders on the right
                 (counts come from listings on sale). Narrow screens get just the category list. --}}
            <div id="zmCatalog" hidden data-catalog class="absolute top-[calc(100%+10px)] left-0 z-20 w-[min(980px,calc(100vw-32px))] overflow-hidden rounded-[14px] border border-line bg-white shadow-[0_16px_36px_rgba(0,0,0,.12)]">
                <div class="grid md:grid-cols-[272px_1fr]">
                    <ul class="max-h-[70vh] overflow-y-auto p-2 md:border-r md:border-line">
                        @foreach($zmCategories as $category)
                            <li>
                                <a href="{{ route('home', ['category_id' => $category->id]) }}" data-catalog-tab="{{ $category->id }}" @if($zmActiveCategory === $category->id) aria-current="page" @endif
                                   class="flex items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-ink hover:bg-soft aria-selected:bg-soft current:text-brand">
                                    <span>{{ $category->name }}</span><i class="bi bi-chevron-right hidden text-xs text-faint md:inline"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @foreach($zmCategories as $category)
                        @php
                            $zmData = $catalogData[$category->id] ?? [];
                            $zmGroups = array_filter([
                                'Turi' => ['param' => 'breed', 'items' => $zmData['breeds'] ?? []],
                                'Manzil' => ['param' => 'region', 'items' => $zmData['regions'] ?? []],
                                'Jinsi' => ['param' => 'gender', 'items' => $zmData ? [['name' => 'male', 'label' => 'Erkak'], ['name' => 'female', 'label' => "Urg'ochi"]] : []],
                            ], fn ($g) => count($g['items']) > 0);
                        @endphp
                        <section data-catalog-panel="{{ $category->id }}" hidden class="hidden max-h-[70vh] overflow-y-auto p-6 md:block">
                            <a href="{{ route('home', ['category_id' => $category->id]) }}" class="mb-5 inline-flex items-center gap-1.5 text-lg font-extrabold text-ink hover:text-brand">{{ $category->name }} <i class="bi bi-chevron-right text-sm"></i></a>

                            @if($zmGroups)
                                <div class="grid grid-cols-3 gap-x-8 gap-y-6">
                                    @foreach($zmGroups as $zmTitle => $zmGroup)
                                        <div>
                                            <h4 class="mb-2.5 text-sm font-extrabold text-ink">{{ $zmTitle }}</h4>
                                            <ul class="flex flex-col gap-1.5">
                                                @foreach($zmGroup['items'] as $zmItem)
                                                    <li @if($loop->index >= 4) data-catalog-extra hidden @endif>
                                                        <a href="{{ route('home', ['category_id' => $category->id, $zmGroup['param'] => $zmItem['name']]) }}" class="text-sm text-muted hover:text-brand">{{ $zmItem['label'] ?? $zmItem['name'] }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if(count($zmGroup['items']) > 4)
                                                <button type="button" data-catalog-more class="mt-1.5 inline-flex cursor-pointer items-center gap-1 text-sm text-muted hover:text-brand">Yana {{ count($zmGroup['items']) - 4 }} <i class="bi bi-chevron-down text-xs"></i></button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-muted">Bu kategoriyada hozircha e'lon yo'q.</p>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>
        </div>

        <form id="zmSearchForm" action="{{ $zmMine ? route('posts.mine') : route('home') }}" method="GET" role="search"
              class="relative order-last flex h-11 min-w-0 basis-full items-center gap-3 rounded-xl border border-transparent bg-brand-soft pr-1.5 pl-4 transition focus-within:border-brand focus-within:bg-white md:order-none md:h-10 md:max-w-[560px] md:flex-1 md:basis-auto">
            <button type="submit" aria-label="Qidirish" class="cursor-pointer leading-none"><i class="bi bi-search text-base text-brand"></i></button>
            <input class="min-w-0 flex-1 bg-transparent text-sm text-ink outline-none placeholder:text-faint focus-visible:outline-none" type="search" name="q" value="{{ ($zmOnListing || $zmMine) ? request('q') : '' }}" placeholder="{{ $zmMine ? "E'lonlaringiz ichidan qidiring..." : "Hayvon, tur yoki shahar bo'yicha qidiring..." }}" aria-label="Qidirish">
            @if($zmOnListing && request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
        </form>

        <nav class="ml-auto flex shrink-0 items-center gap-1 text-sm" aria-label="Hisob">
            @auth
                @if($zmIsAdmin)
                    <a href="{{ route('moderation.index') }}" @if(request()->routeIs('moderation.*')) aria-current="page" @endif class="{{ $zmAction }}" aria-label="E'lonlarni tekshirish">
                        <i class="bi bi-shield-check {{ $zmActionIcon }}"></i><span class="hidden lg:inline">Tekshiruv</span>
                        @if($zmModeration > 0)<em class="{{ $zmBadge }} absolute top-0.5 left-[26px]">{{ $zmModeration > 99 ? '99+' : $zmModeration }}</em>@endif
                    </a>
                    <a href="{{ route('banners.index') }}" @if(request()->routeIs('banners.*')) aria-current="page" @endif class="{{ $zmAction }}" aria-label="Bosh sahifa bannerlari">
                        <i class="bi bi-images {{ $zmActionIcon }}"></i><span class="hidden lg:inline">Bannerlar</span>
                        @if($zmPendingBanners > 0)<em class="{{ $zmBadge }} absolute top-0.5 left-[26px]">{{ $zmPendingBanners > 99 ? '99+' : $zmPendingBanners }}</em>@endif
                    </a>
                @else
                    {{-- The one and only "new listing" entry point --}}
                    <a href="{{ route('posts.create') }}" @if(request()->routeIs('posts.create')) aria-current="page" @endif class="btn btn-primary mr-1 h-10 rounded-lg px-3 text-[13px] current:bg-brand-dark md:px-4"><i class="bi bi-plus-lg"></i><span class="hidden lg:inline">Yangi e'lon</span></a>
                    <a href="{{ route('favorites.index') }}" @if(request()->routeIs('favorites.*')) aria-current="page" @endif class="{{ $zmAction }}" aria-label="Sevimlilar">
                        <i class="bi bi-heart {{ $zmActionIcon }}"></i><span class="hidden lg:inline">Sevimlilar</span>
                        <em class="{{ $zmBadge }} absolute top-0.5 left-[26px]" data-favorites-count @if($zmFavorites === 0) hidden @endif>{{ $zmFavorites }}</em>
                    </a>
                    <a href="{{ route('user.purchase-requests.index') }}" @if(request()->routeIs('user.purchase-requests.*')) aria-current="page" @endif class="{{ $zmAction }}" aria-label="Buyurtmalar">
                        <i class="bi bi-bag-check {{ $zmActionIcon }}"></i><span class="hidden lg:inline">Buyurtmalar</span>
                    </a>
                @endif
                <a href="{{ route('chats.index') }}" @if(request()->routeIs('chats.*')) aria-current="page" @endif class="{{ $zmAction }}" aria-label="Xabarlar">
                    <i class="bi bi-chat-dots {{ $zmActionIcon }}"></i><span class="hidden lg:inline">Xabarlar</span>
                    @if($zmUnread > 0)<em class="{{ $zmBadge }} absolute top-0.5 left-[26px]">{{ $zmUnread > 99 ? '99+' : $zmUnread }}</em>@endif
                </a>
                <div class="relative ml-1">
                    <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="zmProfileMenu" aria-label="Hisob menyusi" class="{{ $zmAction }} gap-1 pr-1.5 pl-1 md:pr-1.5 md:pl-1">
                        <span class="grid size-8 place-items-center rounded-full bg-brand text-sm font-extrabold text-white">{{ mb_strtoupper(mb_substr($zmUser->name, 0, 1)) }}</span><i class="bi bi-chevron-down text-xs"></i>
                        @if(! $zmIsAdmin && $zmPending > 0)<em class="{{ $zmBadge }} absolute top-0.5 left-[26px]" title="Yangi so'rovlar">{{ $zmPending > 99 ? '99+' : $zmPending }}</em>@endif
                    </button>
                    <div id="zmProfileMenu" hidden class="absolute top-[calc(100%+10px)] right-0 z-10 w-[260px] rounded-[14px] border border-line bg-white p-2 shadow-[0_16px_36px_rgba(0,0,0,.12)]">
                        <div class="mb-1.5 border-b border-line px-3 pt-2.5 pb-3">
                            <b class="block truncate text-[15px] text-ink">{{ $zmUser->name }}</b>
                            <small class="block truncate text-xs text-muted">{{ $zmUser->email }}{{ $zmIsAdmin ? ' · Bosh admin' : '' }}</small>
                        </div>
                        <a href="{{ $zmProfileUrl }}" class="{{ $zmMenuItem }}"><i class="bi bi-person-circle text-base text-brand"></i>Profil va sozlamalar</a>
                        {{-- Selling: the only entry points to my listings, incoming requests and the archive --}}
                        @unless($zmIsAdmin)
                            <a href="{{ route('posts.mine') }}" class="{{ $zmMenuItem }}"><i class="bi bi-collection text-base text-brand"></i>Mening e'lonlarim</a>
                            <a href="{{ route('admin.purchase-requests.index') }}" class="{{ $zmMenuItem }}"><i class="bi bi-inbox text-base text-brand"></i>Kelgan so'rovlar
                                @if($zmPending > 0)<em class="{{ $zmBadge }} ml-auto">{{ $zmPending > 99 ? '99+' : $zmPending }}</em>@endif</a>
                            <a href="{{ route('admin.archive.index') }}" class="{{ $zmMenuItem }}"><i class="bi bi-archive text-base text-brand"></i>Arxiv</a>
                            <a href="{{ route('banners.index') }}" class="{{ $zmMenuItem }}"><i class="bi bi-images text-base text-brand"></i>Mening bannerlarim</a>
                        @endunless
                        <hr class="my-1.5 border-line">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="{{ $zmMenuItem }} text-danger hover:bg-[#fff1f0] hover:text-danger"><i class="bi bi-box-arrow-right text-base"></i>Chiqish</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="{{ $zmAction }}"><i class="bi bi-person {{ $zmActionIcon }}"></i><span class="hidden lg:inline">Kirish</span></a>
                <a href="{{ route('register') }}" class="btn ml-1 border-brand bg-white text-brand hover:bg-brand-soft max-[480px]:hidden">Ro'yxatdan o'tish</a>
            @endauth
        </nav>
    </div>
</header>

{{-- Category row: plain text links like a marketplace menu. Links that don't fit move under "Yana"
     (resources/js/app.js; without JS the row scrolls sideways). Clicking the active category again clears it. --}}
@php
    $zmCatLinks = $zmCategories->map(fn ($category) => [
        'name' => $category->name,
        'active' => $zmActiveCategory === $category->id,
        'url' => route('home', $zmActiveCategory === $category->id ? $zmKeep : array_merge($zmKeep, ['category_id' => $category->id])),
    ]);
    $zmCatLink = 'inline-flex shrink-0 items-center gap-1.5 text-[15px] whitespace-nowrap text-muted transition-colors hover:text-ink current:font-semibold current:text-brand';
    $zmCatMenuLink = 'flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-sm text-ink hover:bg-brand-soft hover:text-brand current:font-semibold current:text-brand';
@endphp
<nav class="border-b border-line bg-white" aria-label="Kategoriyalar">
    <div class="wrap flex h-12 items-center gap-7" data-overflow-nav>
        <div class="no-scrollbar flex min-w-0 flex-1 items-center gap-7 overflow-x-auto" data-overflow-items>
            @foreach($zmCatLinks as $link)
                <a href="{{ $link['url'] }}" @if($link['active']) aria-current="page" title="Kategoriyani bekor qilish" @endif class="{{ $zmCatLink }}">{{ $link['name'] }}@if($link['active'])<i class="bi bi-x-lg text-[11px]"></i>@endif</a>
            @endforeach
        </div>
        <div class="relative shrink-0" data-overflow-more hidden>
            <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="zmCatMore" class="inline-flex cursor-pointer items-center gap-1 text-sm text-muted transition-colors hover:text-ink aria-expanded:text-ink">
                Yana <i class="bi bi-chevron-down text-xs"></i>
            </button>
            <div id="zmCatMore" hidden class="absolute top-[calc(100%+12px)] right-0 z-20 max-h-[60vh] w-[240px] overflow-y-auto rounded-[14px] border border-line bg-white p-2 shadow-[0_16px_36px_rgba(0,0,0,.12)]">
                @foreach($zmCatLinks as $link)
                    <a href="{{ $link['url'] }}" @if($link['active']) aria-current="page" @endif data-overflow-copy hidden class="{{ $zmCatMenuLink }}">{{ $link['name'] }}@if($link['active'])<i class="bi bi-x-lg ml-auto text-[11px]"></i>@endif</a>
                @endforeach
            </div>
        </div>
    </div>
</nav>

{{-- Toasts: server messages render here; JS adds more (e.g. after a like) via window.zmToast --}}
<div id="zmToasts" aria-live="polite" aria-atomic="false" class="pointer-events-none fixed top-4 right-4 z-[2000] flex w-[min(380px,calc(100vw-32px))] flex-col gap-2.5">
    @foreach($zmToasts as $toast)
        <x-toast :type="$toast['type']" :text="$toast['text']" />
    @endforeach
</div>
<template id="zmToastTpl"><x-toast /></template>
