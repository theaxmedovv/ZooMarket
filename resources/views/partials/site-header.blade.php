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
    $zmUnread = (int) ($globalUnreadCount ?? 0);
    $zmPending = (int) ($pendingRequestsCount ?? 0);
@endphp

{{-- Top bar --}}
<div class="zm-topbar">
    <div class="zm-wrap">
        <span class="zm-welcome">ZooMarket'ga xush kelibsiz — uy hayvonlari bozori!</span>
        <div class="zm-topbar-right">
            <a href="{{ route('posts.index', ['location' => 'Toshkent']) }}"><i class="bi bi-geo-alt"></i>Toshkent</a>
            @if($zmIsSeller)
                <a href="{{ route('admin.purchase-requests.index') }}"><i class="bi bi-inbox"></i>So'rovlar</a>
            @else
                <a href="{{ $zmUser ? route('user.purchase-requests.index') : route('login') }}"><i class="bi bi-truck"></i>Buyurtmani kuzatish</a>
            @endif
            <a href="{{ route('posts.index', ['sort' => 'price_asc']) }}" class="zm-hide-sm"><i class="bi bi-percent"></i>Barcha takliflar</a>
        </div>
    </div>
</div>

{{-- Header --}}
<header class="zm-header">
    <div class="zm-wrap zm-header-row">
        <div class="zm-brand">
            <button type="button" class="zm-menu-btn" id="zmMenuBtn" aria-expanded="false" aria-controls="zmDrawer" aria-label="Menyu"><i class="bi bi-list"></i></button>
            <a href="{{ route('home') }}" class="zm-logo">ZooMarket</a>
        </div>

        <form class="zm-search" action="{{ route('posts.index') }}" method="GET" role="search">
            <button type="submit" aria-label="Qidirish"><i class="bi bi-search"></i></button>
            <input type="search" name="q" value="{{ request()->routeIs('posts.index') ? request('q') : '' }}" placeholder="Hayvon, zot yoki shahar bo'yicha qidiring..." aria-label="Qidirish">
            <a href="{{ route('posts.index') }}" aria-label="Barcha e'lonlar"><i class="bi bi-sliders"></i></a>
        </form>

        <nav class="zm-actions">
            @auth
                @if($zmIsSeller)
                    <a href="{{ route('posts.create') }}" class="zm-btn-new"><i class="bi bi-plus-lg"></i><span>Yangi e'lon</span></a>
                @endif
                <a href="{{ route('chats.index') }}" class="zm-action">
                    <i class="bi bi-chat-dots"></i><span class="zm-label">Xabarlar</span>
                    @if($zmUnread > 0)<em class="zm-badge">{{ $zmUnread > 99 ? '99+' : $zmUnread }}</em>@endif
                </a>
                <div class="zm-profile">
                    <button type="button" class="zm-action" id="zmProfileBtn" aria-expanded="false" aria-controls="zmProfileMenu">
                        <i class="bi bi-person"></i><span class="zm-label">{{ \Illuminate\Support\Str::limit($zmUser->name, 14) }}</span><i class="bi bi-chevron-down" style="font-size: 12px"></i>
                    </button>
                    <div class="zm-profile-menu" id="zmProfileMenu">
                        <a href="{{ $zmProfileUrl }}"><i class="bi bi-person-circle"></i>Profil</a>
                        @if($zmIsSeller)
                            <a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i>Boshqaruv paneli</a>
                            <a href="{{ route('posts.index') }}"><i class="bi bi-collection"></i>Mening e'lonlarim</a>
                            <a href="{{ route('admin.purchase-requests.index') }}"><i class="bi bi-inbox"></i>So'rovlar @if($zmPending > 0)<em class="zm-badge" style="position: static">{{ $zmPending }}</em>@endif</a>
                            <a href="{{ route('admin.archive.index') }}"><i class="bi bi-archive"></i>Arxiv</a>
                        @else
                            <a href="{{ route('user.purchase-requests.index') }}"><i class="bi bi-bag-check"></i>Buyurtmalarim</a>
                        @endif
                        <a href="{{ route('chats.index') }}"><i class="bi bi-chat-dots"></i>Xabarlar</a>
                        <hr>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="danger"><i class="bi bi-box-arrow-right"></i>Chiqish</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="zm-action"><i class="bi bi-person"></i><span class="zm-label">Kirish</span></a>
                <a href="{{ route('register') }}" class="zm-action"><i class="bi bi-person-plus"></i><span class="zm-label">Ro'yxatdan o'tish</span></a>
            @endauth
        </nav>
    </div>

    {{-- Menu drawer --}}
    <div class="zm-drawer" id="zmDrawer">
        <div class="zm-wrap">
            <div class="zm-drawer-panel">
                @if($zmIsSeller)
                    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i>Boshqaruv paneli</a>
                    <a href="{{ route('posts.index') }}"><i class="bi bi-collection"></i>Mening e'lonlarim</a>
                    <a href="{{ route('posts.create') }}"><i class="bi bi-plus-circle"></i>Yangi e'lon</a>
                    <a href="{{ route('admin.purchase-requests.index') }}"><i class="bi bi-inbox"></i>So'rovlar</a>
                    <a href="{{ route('admin.archive.index') }}"><i class="bi bi-archive"></i>Arxiv</a>
                    <a href="{{ route('chats.index') }}"><i class="bi bi-chat-dots"></i>Xabarlar</a>
                @else
                    @foreach($zmCategories as $category)
                        <a href="{{ route('posts.index', ['category_id' => $category->id]) }}"><span class="emoji">{{ $category->emoji }}</span>{{ $category->name }}</a>
                    @endforeach
                    <a href="{{ route('posts.index') }}"><span class="emoji">📋</span>Barcha e'lonlar</a>
                @endif
            </div>
        </div>
    </div>
</header>

{{-- Pills: categories for buyers/guests, workspace links for sellers --}}
<nav class="zm-pills" aria-label="Bo'limlar">
    <div class="zm-wrap">
        @if($zmIsSeller)
            <a href="{{ route('posts.index') }}" class="zm-pill {{ request()->routeIs('posts.index', 'posts.show', 'posts.edit') ? 'active' : '' }}"><i class="bi bi-collection"></i>E'lonlarim</a>
            <a href="{{ route('posts.create') }}" class="zm-pill {{ request()->routeIs('posts.create') ? 'active' : '' }}"><i class="bi bi-plus-circle"></i>Yangi e'lon</a>
            <a href="{{ route('admin.purchase-requests.index') }}" class="zm-pill {{ request()->routeIs('admin.purchase-requests.*') ? 'active' : '' }}"><i class="bi bi-inbox"></i>So'rovlar @if($zmPending > 0)<em class="zm-badge">{{ $zmPending > 99 ? '99+' : $zmPending }}</em>@endif</a>
            <a href="{{ route('admin.archive.index') }}" class="zm-pill {{ request()->routeIs('admin.archive.*', 'admin.sold-animals.*') ? 'active' : '' }}"><i class="bi bi-archive"></i>Arxiv</a>
            <a href="{{ route('chats.index') }}" class="zm-pill {{ request()->routeIs('chats.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i>Xabarlar @if($zmUnread > 0)<em class="zm-badge">{{ $zmUnread > 99 ? '99+' : $zmUnread }}</em>@endif</a>
            <a href="{{ route('admin.dashboard') }}" class="zm-pill {{ request()->routeIs('admin.dashboard', 'profile.show') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Panel</a>
        @else
            <a href="{{ route('posts.index', $zmKeep) }}" class="zm-pill {{ request()->routeIs('posts.index') && ! $zmActiveCategory ? 'active' : '' }}">Barchasi <i class="bi bi-chevron-down"></i></a>
            @foreach($zmCategories as $category)
                <a href="{{ route('posts.index', array_merge($zmKeep, ['category_id' => $category->id])) }}" class="zm-pill {{ $zmActiveCategory === $category->id ? 'active' : '' }}">{{ $category->name }} <i class="bi bi-chevron-down"></i></a>
            @endforeach
            @auth
                <a href="{{ route('user.purchase-requests.index') }}" class="zm-pill {{ request()->routeIs('user.purchase-requests.*') ? 'active' : '' }}"><i class="bi bi-bag-check"></i>Buyurtmalarim</a>
            @endauth
        @endif
    </div>
</nav>

@if(session('success'))<div class="zm-flash"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="zm-flash err"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>@endif
