@php
    $zmUser = auth()->user();
    $zmIsSeller = $zmUser?->hasRole('seller') ?? false;
    $zmProfileUrl = $zmUser
        ? ($zmUser->hasRole('user') ? route('user.profile.show') : route('profile.show'))
        : route('login');
    $zmCategories = $navCategories ?? collect();
    $zmActiveCategory = (int) request()->query('category_id');
    // On the listing page, switching category keeps the other active filters.
    $zmKeep = request()->routeIs('posts.index') ? request()->except('category_id', 'page') : [];
    // Filters only apply to guests/buyers (the listing controller ignores them for sellers).
    $zmCanFilter = ! $zmIsSeller;
    $zmOnListing = request()->routeIs('posts.index');
    $zmF = [
        'category_id' => $zmOnListing ? (string) request('category_id', '') : '',
        'gender' => $zmOnListing ? (string) request('gender', '') : '',
        'currency' => $zmOnListing ? (string) request('currency', '') : '',
        'price_min' => $zmOnListing ? (string) request('price_min', '') : '',
        'price_max' => $zmOnListing ? (string) request('price_max', '') : '',
        'location' => $zmOnListing ? (string) request('location', '') : '',
    ];
    $zmFilterCount = count(array_filter($zmF, fn ($v) => $v !== ''));
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

    // Shared class lists for the chrome below.
    $zmBadge = 'grid h-[18px] min-w-[18px] place-items-center rounded-full bg-hot px-[5px] text-[11px] leading-none font-extrabold not-italic text-white';
    $zmAction = 'relative inline-flex h-11 cursor-pointer items-center gap-2 rounded-[10px] px-2 font-bold whitespace-nowrap text-muted hover:bg-brand-soft hover:text-brand current:bg-brand-soft current:text-brand md:px-3';
    $zmActionIcon = 'text-[21px] leading-none text-brand';
    $zmMenuItem = 'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-ink hover:bg-brand-soft hover:text-brand';
    $zmPill = 'relative inline-flex h-[38px] shrink-0 items-center gap-2 rounded-full bg-soft px-4 text-sm font-semibold whitespace-nowrap text-ink transition-colors hover:bg-brand-tint current:bg-brand current:text-white';
    $zmFieldTitle = 'text-xs font-extrabold tracking-wider text-muted uppercase [&_.bi]:mr-0.5 [&_.bi]:text-brand';
    $zmFieldInput = 'input h-[42px] rounded-lg px-3';
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
    <div class="wrap flex flex-wrap items-center gap-3 py-3 md:h-[76px] md:flex-nowrap md:gap-8 md:py-0">
        <a href="{{ route('home') }}" class="shrink-0 text-2xl leading-none font-black tracking-tight text-brand md:text-[28px]">ZooMarket</a>

        {{-- Single search + filter form for the whole site --}}
        <form id="zmSearchForm" action="{{ route('posts.index') }}" method="GET" role="search"
              class="relative order-last flex h-11 min-w-0 basis-full items-center gap-3 rounded-xl border border-transparent bg-brand-soft pr-1.5 pl-4 transition focus-within:border-brand focus-within:bg-white md:order-none md:h-12 md:max-w-[640px] md:flex-1 md:basis-auto">
            <button type="submit" aria-label="Qidirish" class="cursor-pointer leading-none"><i class="bi bi-search text-lg text-brand"></i></button>
            <input class="min-w-0 flex-1 bg-transparent text-[15px] text-ink outline-none placeholder:text-faint focus-visible:outline-none" type="search" name="q" value="{{ $zmOnListing ? request('q') : '' }}" placeholder="{{ $zmIsSeller ? "E'lonlaringiz ichidan qidiring..." : "Hayvon, zot yoki shahar bo'yicha qidiring..." }}" aria-label="Qidirish">
            @if($zmOnListing && request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif

            @if($zmCanFilter)
                <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="zmFilterPanel" aria-label="Filtrlar"
                        @class(['group relative grid size-[38px] shrink-0 cursor-pointer place-items-center rounded-lg aria-expanded:bg-brand', $zmFilterCount ? 'bg-brand' : 'hover:bg-white'])>
                    <i @class(['bi bi-sliders text-lg group-aria-expanded:text-white', $zmFilterCount ? 'text-white' : 'text-brand'])></i>
                    @if($zmFilterCount)<em class="{{ $zmBadge }} absolute -top-1.5 -right-1.5">{{ $zmFilterCount }}</em>@endif
                </button>

                <div id="zmFilterPanel" hidden class="absolute top-[calc(100%+10px)] right-0 left-0 z-10 rounded-[14px] border border-line bg-white p-[18px] shadow-[0_20px_44px_rgba(0,0,0,.14)] md:min-w-[560px]">
                    <div class="grid grid-cols-1 gap-x-[18px] gap-y-4 md:grid-cols-2">
                        <label class="flex min-w-0 flex-col gap-[7px]">
                            <span class="{{ $zmFieldTitle }}"><i class="bi bi-grid-3x3-gap"></i> Kategoriya</span>
                            <select name="category_id" class="{{ $zmFieldInput }}">
                                <option value="">Barcha kategoriyalar</option>
                                @foreach($zmCategories as $category)
                                    <option value="{{ $category->id }}" @selected($zmF['category_id'] === (string) $category->id)>{{ $category->emoji }} {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="flex min-w-0 flex-col gap-[7px]">
                            <span class="{{ $zmFieldTitle }}"><i class="bi bi-gender-ambiguous"></i> Jinsi</span>
                            <div class="flex h-[42px] gap-1 rounded-lg border border-field p-[3px]">
                                @foreach(['' => 'Hammasi', 'male' => 'Erkak ♂', 'female' => "Urg'ochi ♀"] as $value => $label)
                                    <label class="flex-1 cursor-pointer"><input class="peer sr-only" type="radio" name="gender" value="{{ $value }}" @checked($zmF['gender'] === $value)><span class="grid h-full place-items-center rounded-md text-[13px] font-bold whitespace-nowrap text-muted peer-checked:bg-brand peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-brand hover:text-brand peer-checked:hover:text-white">{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex min-w-0 flex-col gap-[7px]">
                            <span class="{{ $zmFieldTitle }}"><i class="bi bi-tag"></i> Narx oralig'i</span>
                            <div class="grid grid-cols-[1fr_1fr_108px] gap-2">
                                <input class="{{ $zmFieldInput }}" type="number" name="price_min" min="0" step="0.01" value="{{ $zmF['price_min'] }}" placeholder="Min">
                                <input class="{{ $zmFieldInput }}" type="number" name="price_max" min="0" step="0.01" value="{{ $zmF['price_max'] }}" placeholder="Max">
                                <select name="currency" aria-label="Valyuta" class="{{ $zmFieldInput }}">
                                    <option value="">Valyuta</option>
                                    @foreach(['UZS', 'USD', 'EUR', 'RUB'] as $currency)
                                        <option value="{{ $currency }}" @selected($zmF['currency'] === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <label class="flex min-w-0 flex-col gap-[7px]">
                            <span class="{{ $zmFieldTitle }}"><i class="bi bi-geo-alt"></i> Manzil</span>
                            <input class="{{ $zmFieldInput }}" type="text" name="location" value="{{ $zmF['location'] }}" placeholder="Shahar yoki viloyat">
                        </label>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-3.5 text-[13px] text-muted">
                        <span>{{ $zmFilterCount ? $zmFilterCount . ' ta filtr faol' : "Qo'shimcha parametrlar bo'yicha saralash" }}</span>
                        <div class="flex gap-2">
                            @if($zmFilterCount)
                                <a href="{{ route('posts.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}" class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-field px-3.5 font-bold text-muted hover:border-danger hover:text-danger"><i class="bi bi-arrow-counterclockwise"></i> Tozalash</a>
                            @endif
                            <button type="submit" class="btn btn-primary h-10 rounded-lg"><i class="bi bi-funnel-fill"></i> Qo'llash</button>
                        </div>
                    </div>
                </div>
            @endif
        </form>

        <nav class="ml-auto flex shrink-0 items-center gap-1 text-sm" aria-label="Hisob">
            @auth
                @if($zmIsSeller)
                    {{-- The one and only "new listing" entry point --}}
                    <a href="{{ route('posts.create') }}" @if(request()->routeIs('posts.create')) aria-current="page" @endif class="btn btn-primary mr-1.5 px-3 current:bg-brand-dark md:mr-2 md:px-[18px]"><i class="bi bi-plus-lg"></i><span class="hidden lg:inline">Yangi e'lon</span></a>
                @else
                    <a href="{{ route('user.profile.show') }}#saved" class="{{ $zmAction }}" aria-label="Sevimlilar">
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
                        <span class="grid size-9 place-items-center rounded-full bg-brand text-[15px] font-extrabold text-white">{{ mb_strtoupper(mb_substr($zmUser->name, 0, 1)) }}</span><i class="bi bi-chevron-down text-xs"></i>
                    </button>
                    <div id="zmProfileMenu" hidden class="absolute top-[calc(100%+10px)] right-0 z-10 w-[260px] rounded-[14px] border border-line bg-white p-2 shadow-[0_16px_36px_rgba(0,0,0,.12)]">
                        <div class="mb-1.5 border-b border-line px-3 pt-2.5 pb-3">
                            <b class="block truncate text-[15px] text-ink">{{ $zmUser->name }}</b>
                            <small class="block truncate text-xs text-muted">{{ $zmUser->email }} · {{ $zmIsSeller ? 'Sotuvchi' : 'Xaridor' }}</small>
                        </div>
                        <a href="{{ $zmProfileUrl }}" class="{{ $zmMenuItem }}"><i class="bi bi-person-circle text-base text-brand"></i>Profil va sozlamalar</a>
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

{{-- Tab row: the seller's workspace, or the storefront's categories --}}
<nav class="border-b border-line bg-white" aria-label="{{ $zmIsSeller ? "Sotuvchi bo'limlari" : 'Kategoriyalar' }}">
    <div class="wrap no-scrollbar flex gap-2 overflow-x-auto py-3">
        @if($zmIsSeller)
            @foreach([
                ['posts.index', ['posts.index', 'posts.show', 'posts.edit'], 'bi-collection', "E'lonlarim", 0],
                ['admin.purchase-requests.index', ['admin.purchase-requests.*'], 'bi-inbox', "So'rovlar", $zmPending],
                ['admin.archive.index', ['admin.archive.*', 'admin.sold-animals.*'], 'bi-archive', 'Arxiv', 0],
            ] as [$route, $patterns, $icon, $label, $count])
                @php $active = request()->routeIs(...$patterns); @endphp
                <a href="{{ route($route) }}" @if($active) aria-current="page" @endif class="{{ $zmPill }}"><i @class(['bi text-[13px]', $icon, $active ? 'text-white' : 'text-brand'])></i>{{ $label }}@if($count > 0)<em class="{{ $zmBadge }} ml-0.5">{{ $count > 99 ? '99+' : $count }}</em>@endif</a>
            @endforeach
        @else
            <a href="{{ route('posts.index', $zmKeep) }}" @if(request()->routeIs('posts.index') && ! $zmActiveCategory) aria-current="page" @endif class="{{ $zmPill }}">Barchasi</a>
            @foreach($zmCategories as $category)
                <a href="{{ route('posts.index', array_merge($zmKeep, ['category_id' => $category->id])) }}" @if($zmActiveCategory === $category->id) aria-current="page" @endif class="{{ $zmPill }}"><span aria-hidden="true">{{ $category->emoji }}</span>{{ $category->name }}</a>
            @endforeach
        @endif
    </div>
</nav>

{{-- Toasts: server messages render here; JS adds more (e.g. after a like) via window.zmToast --}}
<div id="zmToasts" aria-live="polite" aria-atomic="false" class="pointer-events-none fixed top-4 right-4 z-[2000] flex w-[min(380px,calc(100vw-32px))] flex-col gap-2.5">
    @foreach($zmToasts as $toast)
        <x-toast :type="$toast['type']" :text="$toast['text']" />
    @endforeach
</div>
<template id="zmToastTpl"><x-toast /></template>
