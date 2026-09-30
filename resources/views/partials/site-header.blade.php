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
@endphp

{{-- Top bar: information only (every destination lives in the header or tab row) --}}
<div class="zm-topbar">
    <div class="zm-wrap">
        <span class="zm-welcome">ZooMarket'ga xush kelibsiz — uy hayvonlari bozori!</span>
        <div class="zm-topbar-right">
            <span><i class="bi bi-shield-check"></i>Har bir e'lon moderatsiyadan o'tadi</span>
            <a href="tel:+998712000000" class="zm-hide-sm"><i class="bi bi-telephone"></i>+998 71 200 00 00</a>
        </div>
    </div>
</div>

{{-- Header --}}
<header class="zm-header">
    <div class="zm-wrap zm-header-row">
        <a href="{{ route('home') }}" class="zm-logo">ZooMarket</a>

        {{-- Single search + filter form for the whole site --}}
        <form class="zm-search" id="zmSearchForm" action="{{ route('posts.index') }}" method="GET" role="search">
            <button type="submit" aria-label="Qidirish"><i class="bi bi-search"></i></button>
            <input type="search" name="q" value="{{ $zmOnListing ? request('q') : '' }}" placeholder="{{ $zmIsSeller ? "E'lonlaringiz ichidan qidiring..." : "Hayvon, zot yoki shahar bo'yicha qidiring..." }}" aria-label="Qidirish">
            @if($zmOnListing && request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif

            @if($zmCanFilter)
                <button type="button" class="zm-filter-btn {{ $zmFilterCount ? 'has-filters' : '' }}" id="zmFilterBtn" aria-expanded="false" aria-controls="zmFilterPanel" aria-label="Filtrlar">
                    <i class="bi bi-sliders"></i>
                    @if($zmFilterCount)<em class="zm-badge">{{ $zmFilterCount }}</em>@endif
                </button>

                <div class="zm-filter-panel" id="zmFilterPanel">
                    <div class="zm-filter-grid">
                        <label class="zm-field">
                            <span><i class="bi bi-grid-3x3-gap"></i> Kategoriya</span>
                            <select name="category_id">
                                <option value="">Barcha kategoriyalar</option>
                                @foreach($zmCategories as $category)
                                    <option value="{{ $category->id }}" @selected($zmF['category_id'] === (string) $category->id)>{{ $category->emoji }} {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="zm-field">
                            <span><i class="bi bi-gender-ambiguous"></i> Jinsi</span>
                            <div class="zm-segment">
                                <label><input type="radio" name="gender" value="" @checked($zmF['gender'] === '')><span>Hammasi</span></label>
                                <label><input type="radio" name="gender" value="male" @checked($zmF['gender'] === 'male')><span>Erkak ♂</span></label>
                                <label><input type="radio" name="gender" value="female" @checked($zmF['gender'] === 'female')><span>Urg'ochi ♀</span></label>
                            </div>
                        </div>

                        <div class="zm-field">
                            <span><i class="bi bi-tag"></i> Narx oralig'i</span>
                            <div class="zm-price">
                                <input type="number" name="price_min" min="0" step="0.01" value="{{ $zmF['price_min'] }}" placeholder="Min">
                                <input type="number" name="price_max" min="0" step="0.01" value="{{ $zmF['price_max'] }}" placeholder="Max">
                                <select name="currency" aria-label="Valyuta">
                                    <option value="">Valyuta</option>
                                    @foreach(['UZS', 'USD', 'EUR', 'RUB'] as $currency)
                                        <option value="{{ $currency }}" @selected($zmF['currency'] === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <label class="zm-field">
                            <span><i class="bi bi-geo-alt"></i> Manzil</span>
                            <input type="text" name="location" value="{{ $zmF['location'] }}" placeholder="Shahar yoki viloyat">
                        </label>
                    </div>

                    <div class="zm-filter-actions">
                        <span>{{ $zmFilterCount ? $zmFilterCount . ' ta filtr faol' : "Qo'shimcha parametrlar bo'yicha saralash" }}</span>
                        <div>
                            @if($zmFilterCount)
                                <a href="{{ route('posts.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}" class="zm-filter-reset"><i class="bi bi-arrow-counterclockwise"></i> Tozalash</a>
                            @endif
                            <button type="submit" class="zm-filter-apply"><i class="bi bi-funnel-fill"></i> Qo'llash</button>
                        </div>
                    </div>
                </div>
            @endif
        </form>

        <nav class="zm-actions" aria-label="Hisob">
            @auth
                @if($zmIsSeller)
                    {{-- The one and only "new listing" entry point --}}
                    <a href="{{ route('posts.create') }}" class="zm-btn-new {{ request()->routeIs('posts.create') ? 'active' : '' }}"><i class="bi bi-plus-lg"></i><span>Yangi e'lon</span></a>
                @else
                    <a href="{{ route('user.profile.show') }}#saved" class="zm-action" aria-label="Sevimlilar">
                        <i class="bi bi-heart"></i><span class="zm-label">Sevimlilar</span>
                        <em class="zm-badge" data-favorites-count @if($zmFavorites === 0) hidden @endif>{{ $zmFavorites }}</em>
                    </a>
                    <a href="{{ route('user.purchase-requests.index') }}" class="zm-action {{ request()->routeIs('user.purchase-requests.*') ? 'current' : '' }}" aria-label="Buyurtmalar">
                        <i class="bi bi-bag-check"></i><span class="zm-label">Buyurtmalar</span>
                    </a>
                @endif
                <a href="{{ route('chats.index') }}" class="zm-action {{ request()->routeIs('chats.*') ? 'current' : '' }}" aria-label="Xabarlar">
                    <i class="bi bi-chat-dots"></i><span class="zm-label">Xabarlar</span>
                    @if($zmUnread > 0)<em class="zm-badge">{{ $zmUnread > 99 ? '99+' : $zmUnread }}</em>@endif
                </a>
                <div class="zm-profile">
                    <button type="button" class="zm-action zm-avatar-btn" id="zmProfileBtn" aria-expanded="false" aria-controls="zmProfileMenu" aria-label="Hisob menyusi">
                        <span class="zm-avatar">{{ mb_strtoupper(mb_substr($zmUser->name, 0, 1)) }}</span><i class="bi bi-chevron-down zm-caret"></i>
                    </button>
                    <div class="zm-profile-menu" id="zmProfileMenu">
                        <div class="zm-profile-head">
                            <b>{{ $zmUser->name }}</b>
                            <small>{{ $zmUser->email }} · {{ $zmIsSeller ? 'Sotuvchi' : 'Xaridor' }}</small>
                        </div>
                        <a href="{{ $zmProfileUrl }}"><i class="bi bi-person-circle"></i>Profil va sozlamalar</a>
                        <hr>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="danger"><i class="bi bi-box-arrow-right"></i>Chiqish</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="zm-action"><i class="bi bi-person"></i><span class="zm-label">Kirish</span></a>
                <a href="{{ route('register') }}" class="zm-btn-new zm-btn-outline"><span>Ro'yxatdan o'tish</span></a>
            @endauth
        </nav>
    </div>
</header>

{{-- Tab row: the seller's workspace, or the storefront's categories --}}
<nav class="zm-pills" aria-label="{{ $zmIsSeller ? "Sotuvchi bo'limlari" : 'Kategoriyalar' }}">
    <div class="zm-wrap">
        @if($zmIsSeller)
            <a href="{{ route('posts.index') }}" class="zm-pill {{ request()->routeIs('posts.index', 'posts.show', 'posts.edit') ? 'active' : '' }}"><i class="bi bi-collection"></i>E'lonlarim</a>
            <a href="{{ route('admin.purchase-requests.index') }}" class="zm-pill {{ request()->routeIs('admin.purchase-requests.*') ? 'active' : '' }}"><i class="bi bi-inbox"></i>So'rovlar @if($zmPending > 0)<em class="zm-badge">{{ $zmPending > 99 ? '99+' : $zmPending }}</em>@endif</a>
            <a href="{{ route('admin.archive.index') }}" class="zm-pill {{ request()->routeIs('admin.archive.*', 'admin.sold-animals.*') ? 'active' : '' }}"><i class="bi bi-archive"></i>Arxiv</a>
        @else
            <a href="{{ route('posts.index', $zmKeep) }}" class="zm-pill {{ request()->routeIs('posts.index') && ! $zmActiveCategory ? 'active' : '' }}">Barchasi</a>
            @foreach($zmCategories as $category)
                <a href="{{ route('posts.index', array_merge($zmKeep, ['category_id' => $category->id])) }}" class="zm-pill {{ $zmActiveCategory === $category->id ? 'active' : '' }}"><span aria-hidden="true">{{ $category->emoji }}</span>{{ $category->name }}</a>
            @endforeach
        @endif
    </div>
</nav>

{{-- Toasts: server messages render here; JS adds more (e.g. after a like) via window.zmToast --}}
<div class="zm-toasts" id="zmToasts" aria-live="polite" aria-atomic="false">
    @foreach($zmToasts as $toast)
        <div class="zm-toast zm-toast-{{ $toast['type'] }}" role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}">
            <i class="bi {{ ['success' => 'bi-check-circle-fill', 'info' => 'bi-info-circle-fill', 'warning' => 'bi-exclamation-triangle-fill', 'error' => 'bi-x-circle-fill'][$toast['type']] }}"></i>
            <span>{{ $toast['text'] }}</span>
            <button type="button" class="zm-toast-close" aria-label="Yopish"><i class="bi bi-x-lg"></i></button>
        </div>
    @endforeach
</div>
