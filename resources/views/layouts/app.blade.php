<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ZooMarket') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --ink:#060d07; --panel:#0d180e; --line:#1a2b1c; --lime:#c2f03c; --orange:#ff6b2b; --cream:#eee9de; --muted:#7a9a7d; --serif:'Space Grotesk',sans-serif; --sans:'DM Sans',sans-serif; }
        * { box-sizing:border-box; } body { margin:0; background:var(--ink); color:var(--cream); font-family:var(--sans); line-height:1.55; } a { color:inherit; } button,input,select { font:inherit; }
        .navbar { height:56px; padding:0; background:rgba(6,13,7,.92)!important; border-bottom:1px solid var(--line); backdrop-filter:blur(18px); z-index:20; }
        .navbar>.container { height:100%; } .navbar-brand { color:var(--cream)!important; font:700 1.25rem var(--serif); letter-spacing:-.05em; display:inline-flex; align-items:center; } .brand-mark { display:inline-flex; width:24px; height:24px; align-items:center; justify-content:center; margin-right:7px; border-radius:6px; background:var(--lime); color:var(--ink); font-size:.82rem; transform:rotate(-7deg); }
        .navbar-nav { gap:4px; } .nav-link { padding:6px 11px!important; border-radius:7px; color:var(--muted)!important; font-size:.78rem; font-weight:600; } .nav-link:hover,.nav-link.active { background:rgba(194,240,60,.09); color:var(--cream)!important; } .nav-link i { margin-right:5px; color:var(--lime); }
        .navbar-toggler { color:var(--cream); border-color:var(--line); padding:4px 8px; font-size:.9rem; } .btn-login,.btn-logout { padding:5px 12px; border:1px solid var(--line); border-radius:7px; background:transparent; color:var(--cream)!important; text-decoration:none; font-size:.78rem; } .btn-register { padding:6px 13px; border-radius:8px; background:var(--lime); color:var(--ink)!important; text-decoration:none; font-size:.78rem; font-weight:700; }
        .user-pill { color:var(--cream); font-size:.78rem; } .user-avatar { display:inline-flex; width:24px; height:24px; align-items:center; justify-content:center; border-radius:50%; background:var(--lime); color:var(--ink); font-weight:700; font-size:.72rem; } .nav-divider { width:1px; height:18px; background:var(--line); } .chat-badge { position:absolute; top:0; right:0; padding:1px 5px; border-radius:100px; background:var(--orange); color:#fff; font-size:9px; }
        .alert-banner { padding:7px; border-bottom:1px solid var(--line); background:var(--panel); color:var(--lime); text-align:center; font-size:.78rem; } main { min-height:calc(100vh - 56px); } footer { padding:24px 0; border-top:1px solid var(--line); background:#080f09; color:var(--muted); }
        .container { max-width: 1280px; }
        .page-shell { padding: 16px 0 60px; }
        
        /* Navbar Filter Button */
        .btn-nav-filter { height: 32px; padding: 0 10px; background: rgba(255,255,255,0.06); border: 1px solid var(--line); border-radius: 7px; color: var(--cream); font-size: 0.76rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; white-space: nowrap; transition: all .2s ease; }
        .btn-nav-filter:hover, .btn-nav-filter.active { border-color: var(--lime); color: var(--lime); background: rgba(194,240,60,0.09); }
        .btn-nav-filter i { font-size: 0.82rem; color: var(--lime); }
        .nav-filter-badge { background: var(--lime); color: var(--ink); border-radius: 9999px; font-size: 0.65rem; font-weight: 700; padding: 1px 5px; }

        /* Filter Modal */
        .nav-filter-modal-content { background: #0A130B !important; border: 1px solid var(--line) !important; border-radius: 16px; color: var(--cream); box-shadow: 0 16px 48px rgba(0,0,0,0.7); }
        .filter-modal-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(194, 240, 60, 0.12); color: var(--lime); display: grid; place-items: center; font-size: 1.1rem; }
        .btn-modal-cancel { height: 38px; padding: 0 16px; border: 1px solid var(--line); background: transparent; color: var(--muted); border-radius: 8px; font-size: 0.8rem; font-weight: 600; transition: all .2s; }
        .btn-modal-cancel:hover { border-color: var(--cream); color: var(--cream); }
        
        /* Category Quick Strip */
        .category-strip { padding: 12px 0 6px; background: transparent; border: 0; }
        .category-strip-inner { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; -ms-overflow-style: none; align-items: center; }
        .category-strip-inner::-webkit-scrollbar { display: none; }
        .category-strip-pill { flex-shrink: 0; padding: 5px 13px; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; background: #0D180E; color: #7A9A7D; border: 1px solid #1A2B1C; transition: all 0.15s ease; white-space: nowrap; line-height: 1.25; }
        .category-strip-pill:hover { border-color: rgba(194, 240, 60, 0.35); color: #EEE9DE; background: rgba(194,240,60,0.05); }
        .category-strip-pill.active { background: #C2F03C; color: #060D07; border-color: #C2F03C; font-weight: 700; box-shadow: 0 2px 10px rgba(194,240,60,0.2); }

        /* Unified Top Search & Filter Bar */
        .search-filter-hero { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 14px; box-shadow: 0 6px 24px rgba(0,0,0,0.25); margin-bottom: 22px; }
        .search-filter-bar { display: flex; align-items: center; gap: 10px; }
        .search-input-group { position: relative; flex: 1; display: flex; align-items: center; }
        .search-group-icon { position: absolute; left: 14px; color: var(--muted); font-size: 0.95rem; pointer-events: none; }
        .search-hero-input { width: 100%; height: 44px; padding: 0 14px 0 42px; background: #081209; border: 1px solid var(--line); border-radius: 10px; color: var(--cream); font-size: 0.88rem; outline: none; transition: border-color .2s, box-shadow .2s; }
        .search-hero-input:focus { border-color: var(--lime); box-shadow: 0 0 0 3px rgba(194, 240, 60, 0.15); }
        .search-hero-input::placeholder { color: var(--muted); }
        
        .btn-filter-trigger { height: 44px; padding: 0 16px; background: #081209; border: 1px solid var(--line); border-radius: 10px; color: var(--cream); font-size: 0.84rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; cursor: pointer; transition: all 0.2s ease; }
        .btn-filter-trigger:hover, .btn-filter-trigger.active, .btn-filter-trigger[aria-expanded="true"] { border-color: var(--lime); color: var(--lime); background: rgba(194, 240, 60, 0.08); }
        .btn-filter-trigger i { font-size: 1rem; color: var(--lime); }
        .filter-badge-count { background: var(--lime); color: var(--ink); border-radius: 9999px; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; }
        
        .btn-search-submit { height: 44px; padding: 0 18px; background: var(--lime); color: var(--ink); border: 0; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; white-space: nowrap; transition: all 0.2s ease; }
        .btn-search-submit:hover { background: #d7ff62; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(194, 240, 60, 0.25); }

        .filter-expand-box { background: #081209; border: 1px solid var(--line); border-radius: 12px; padding: 18px; margin-top: 14px; }

        .filter-section { margin-bottom: 0; }
        .filter-sec-label { display: block; color: var(--muted); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 6px; }
        .filter-input-wrap { position: relative; display: flex; align-items: center; }
        .filter-input-icon { position: absolute; left: 10px; color: var(--muted); font-size: 0.8rem; pointer-events: none; }
        .filter-input.with-icon { padding-left: 32px; }
        
        .filter-input, .filter-select { width: 100%; height: 38px; background: #0D180E; border: 1px solid var(--line); color: var(--cream); border-radius: 8px; padding: 0 10px; outline: none; font-size: 0.82rem; transition: border-color .2s, box-shadow .2s; }
        .filter-input:focus, .filter-select:focus { border-color: var(--lime); box-shadow: 0 0 0 2px rgba(194, 240, 60, 0.12); }
        .filter-select option { background: #0D180E; color: var(--cream); }
        .filter-mini-select { height: 24px; background: #0D180E; border: 1px solid var(--line); color: var(--muted); border-radius: 5px; font-size: 0.7rem; padding: 0 6px; outline: none; cursor: pointer; }
        .filter-mini-select:focus { border-color: var(--lime); color: var(--cream); }
        
        /* Segmented Radio Controls (Gender, etc.) */
        .segmented-control { display: flex; background: #0D180E; border: 1px solid var(--line); border-radius: 8px; padding: 2px; gap: 2px; height: 38px; align-items: center; }
        .segment-btn { flex: 1; height: 100%; text-align: center; margin: 0; cursor: pointer; position: relative; user-select: none; display: flex; }
        .segment-btn input { position: absolute; opacity: 0; pointer-events: none; }
        .segment-btn span { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 0 2px; font-size: 0.74rem; font-weight: 600; color: var(--muted); border-radius: 6px; transition: all 0.15s ease; white-space: nowrap; }
        .segment-btn:hover span { color: var(--cream); }
        .segment-btn.active span, .segment-btn input:checked + span { background: var(--lime); color: var(--ink); font-weight: 700; box-shadow: 0 1px 4px rgba(0,0,0,0.2); }

        /* Filter Action Buttons */
        .btn-filter-apply { background: var(--lime); color: var(--ink); border: 0; border-radius: 8px; height: 38px; font-weight: 700; font-size: 0.82rem; transition: all .2s; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .btn-filter-apply:hover { background: #d7ff62; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(194,240,60,0.25); }
        .btn-filter-reset { border: 1px solid var(--line); border-radius: 8px; color: var(--muted); text-decoration: none; height: 38px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; background: transparent; }
        .btn-filter-reset:hover { border-color: var(--lime); color: var(--lime); background: rgba(194,240,60,0.05); }

        /* Catalog Toolbar */
        .catalog-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .catalog-title { font-family: var(--serif); font-size: clamp(1.3rem, 2.4vw, 1.8rem); letter-spacing: -.04em; line-height: 1.15; margin: 0; font-weight: 700; }
        .result-meta { color: var(--muted); font-size: .8rem; margin-top: 2px; }
        
        .btn-sort-dropdown { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--panel); border: 1px solid var(--line); border-radius: 8px; color: var(--cream); font-size: .78rem; font-weight: 600; text-decoration: none; transition: all .2s; }
        .btn-sort-dropdown:hover, .btn-sort-dropdown[aria-expanded="true"] { border-color: var(--lime); color: var(--lime); }
        .btn-mobile-filter { display: inline-flex; align-items: center; padding: 6px 12px; background: var(--panel); border: 1px solid var(--line); border-radius: 8px; color: var(--cream); font-size: .78rem; font-weight: 600; text-decoration: none; transition: all .2s; }
        .btn-mobile-filter:hover, .btn-mobile-filter.active { border-color: var(--lime); color: var(--lime); }

        /* Active Filter Chips Bar */
        .active-filter-chips { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; padding: 8px 12px; background: rgba(13, 24, 14, 0.7); border: 1px solid var(--line); border-radius: 12px; }
        .chips-label { font-size: 0.73rem; color: var(--muted); font-weight: 600; margin-right: 4px; display: inline-flex; align-items: center; }
        .filter-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; background: rgba(194, 240, 60, 0.09); border: 1px solid rgba(194, 240, 60, 0.25); border-radius: 9999px; color: var(--lime); font-size: 0.73rem; font-weight: 500; text-decoration: none; transition: all 0.15s ease; }
        .filter-chip:hover { background: rgba(255, 107, 43, 0.15); border-color: rgba(255, 107, 43, 0.4); color: var(--orange); }
        .filter-chip i { font-size: 0.75rem; opacity: 0.8; }
        .chip-clear-all { background: rgba(255, 255, 255, 0.05); border-color: var(--line); color: var(--muted); }
        .chip-clear-all:hover { background: rgba(255, 60, 60, 0.15); border-color: #ff5555; color: #ff5555; }

        /* Mobile Offcanvas Filter Drawer */
        .offcanvas-filter { background: #0A130B !important; border-right: 1px solid var(--line) !important; max-width: 320px; color: var(--cream); }
        .offcanvas-filter .offcanvas-header { border-bottom: 1px solid var(--line); padding: 14px 18px; }
        .offcanvas-filter .offcanvas-title { font-family: var(--serif); font-weight: 700; font-size: 1.05rem; letter-spacing: -0.02em; color: var(--cream); }
        .offcanvas-filter .offcanvas-body { padding: 18px; }
        .btn-close-filter { background: transparent; border: 0; color: var(--muted); font-size: 1.2rem; cursor: pointer; transition: color .15s; }
        .btn-close-filter:hover { color: var(--cream); }

        /* Listing Grid & Cards */
        .listing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
        .post-col { display: flex; flex-direction: column; }
        .post-card { height: 100%; min-height: 420px; background: var(--panel); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease; }
        .card-img-wrap { height: 230px; width: 100%; position: relative; overflow: hidden; background: #050c06; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--line); }
        .card-img-backdrop { position: absolute; inset: -14px; background-size: cover; background-position: center; filter: blur(16px) brightness(0.32) saturate(1.2); opacity: 0.75; transform: scale(1.15); pointer-events: none; }
        .card-img { position: relative; z-index: 1; width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain; transition: transform .4s ease; }
        .post-card:hover .card-img { transform: scale(1.05); }
        .card-badge { position: absolute; z-index: 2; left: 12px; top: 12px; background: var(--lime); color: var(--ink); border-radius: 100px; padding: 4px 10px; font-size: .68rem; font-weight: 700; }
        .card-img-placeholder { height: 100%; display: flex; align-items: center; justify-content: center; color: var(--muted); gap: 8px; font-size: .85rem; }
        .card-body-inner { display: flex; flex-direction: column; flex: 1; padding: 15px; }
        .card-author { display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: .75rem; margin-bottom: 10px; }
        .author-avatar { width: 28px; height: 28px; border-radius: 50%; background: #1c2e1e; color: var(--lime); display: grid; place-items: center; font-weight: 700; font-size: .78rem; flex-shrink: 0; }
        .author-time { display: block; font-size: .67rem; color: #6a8c6e; }
        .card-title { font-family: var(--serif); font-size: 1.12rem; line-height: 1.25; margin: 0 0 8px; font-weight: 600; }
        .card-title-link { text-decoration: none; color: var(--cream); transition: color .2s; }
        .card-title-link:hover { color: var(--lime); }
        .card-desc { color: var(--muted); font-size: .8rem; line-height: 1.45; margin: 0 0 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .card-price { color: var(--lime); font-family: var(--serif); font-size: 1.18rem; font-weight: 700; margin-top: auto; padding-top: 6px; }
        .card-price small { font-size: .78rem; font-weight: 500; opacity: .85; }
        .card-footer-inner { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--line); }
        .btn-detail { font-size: .78rem; color: var(--cream); text-decoration: none; font-weight: 500; transition: color .2s; }
        .btn-detail:hover { color: var(--lime); }
        .card-actions { display: flex; align-items: center; gap: 6px; }
        .btn-icon { width: 32px; height: 32px; border: 1px solid var(--line); background: transparent; color: var(--muted); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: .2s; }
        .btn-icon:hover, .btn-icon.liked { color: var(--orange); border-color: var(--orange); }
        .btn-buy, .btn-modal-confirm { border: 0; background: var(--orange); color: #fff; border-radius: 8px; padding: 6px 12px; font-size: .72rem; font-weight: 700; transition: .2s; }
        .btn-buy:hover, .btn-modal-confirm:hover { background: #e05517; }
        .action-tag { font-size: .68rem; border-radius: 100px; padding: 4px 9px; font-weight: 600; }
        .tag-pending { background: rgba(255,107,43,.15); color: var(--orange); border: 1px solid rgba(255,107,43,.3); }
        .empty-state { padding: 60px 20px; text-align: center; border: 1px dashed var(--line); border-radius: 16px; background: rgba(13,24,14,.3); }
        .empty-icon { color: var(--lime); font-size: 2.2rem; margin-bottom: 12px; }
        .empty-title { font-family: var(--serif); font-size: 1.3rem; margin-bottom: 6px; }
        .empty-text { color: var(--muted); font-size: .88rem; margin: 0; }
        .buy-modal { background: var(--panel); color: var(--cream); border: 1px solid var(--line)!important; border-radius: 16px; }
        .buy-modal .text-muted { color: var(--muted)!important; }
        .btn-profile-dropdown { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; background:transparent; border:1px solid var(--line); border-radius:7px; color:var(--cream); font-size:.78rem; font-weight:600; text-decoration:none; transition:all .2s ease; }
        .btn-profile-dropdown:hover, .btn-profile-dropdown[aria-expanded="true"] { border-color:var(--lime); color:var(--lime); background:rgba(194,240,60,.08); }
        .btn-profile-dropdown::after { font-size:.65rem; margin-left:4px; vertical-align:middle; }
        .dropdown-menu-dark { background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:6px; min-width:140px; }
        .dropdown-menu-dark .dropdown-item { font-size:.78rem; padding:6px 12px; border-radius:6px; color:var(--cream); transition:all .15s ease; }
        .dropdown-menu-dark .dropdown-item:hover, .dropdown-menu-dark .dropdown-item.active { background:rgba(194,240,60,.12); color:var(--lime); }
        .dropdown-menu-dark .dropdown-item.text-danger:hover { background:rgba(255,60,60,.15); color:#ff5555!important; }
        .dropdown-menu-dark .dropdown-divider { border-color:var(--line); }
        @media(max-width:991px) {
            .navbar { height: auto; min-height: 52px; padding: 6px 0; }
            .navbar-collapse { margin-top: 8px; padding: 12px; border: 1px solid var(--line); border-radius: 12px; background: var(--panel); }
            .mobile-actions { margin-top: 8px; padding-top: 12px; border-top: 1px solid var(--line); width: 100%; }
        }
        @media(max-width:600px) {
            .listing-grid { grid-template-columns: 1fr; }
            .card-img-wrap { height: 260px; }
            .container { padding-left: 14px; padding-right: 14px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top"><div class="container">
        <a class="navbar-brand" href="{{ url('/') }}"><span class="brand-mark">Z</span>ZooMarket</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Menyu"><i class="bi bi-list"></i></button>
        <div class="collapse navbar-collapse" id="mainNav">
            @auth
                <ul class="navbar-nav mx-auto">
                    <li><a class="nav-link {{ request()->is('posts*') ? 'active' : '' }}" href="{{ route('posts.index') }}"><i class="bi bi-compass"></i>Explore</a></li>
                    <li><a class="nav-link {{ request()->is('chats*') ? 'active' : '' }} position-relative" href="{{ route('chats.index') }}"><i class="bi bi-chat"></i>Messages @if(!empty($globalUnreadCount) && $globalUnreadCount > 0)<span class="chat-badge">{{ $globalUnreadCount > 99 ? '99+' : $globalUnreadCount }}</span>@endif</a></li>
                </ul>
            @else <div class="mx-auto"></div> @endauth
            <div class="d-flex align-items-center gap-2 flex-wrap ms-lg-auto my-2 my-lg-0">
                <div class="mobile-actions d-flex align-items-center gap-2 flex-wrap">
                    @auth
                        <div class="dropdown">
                            <button class="btn-profile-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person"></i> Profile
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item {{ request()->is('*profile*') ? 'active' : '' }}" href="{{ auth()->user()->hasRole('user') ? route('user.profile.show') : route('profile.show') }}">
                                        <i class="bi bi-person-circle me-2"></i>Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button class="dropdown-item text-danger d-flex align-items-center" type="submit">
                                            <i class="bi bi-box-arrow-right me-2"></i>Chiqish
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a class="btn-login" href="{{ route('login') }}">Kirish</a>
                        <a class="btn-register" href="{{ route('register') }}">Ro'yxatdan o'tish</a>
                    @endauth
                </div>
            </div>
        </div>
    </div></nav>
    @if(session('success'))<div class="alert-banner"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert-banner" style="color:var(--orange)"><i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}</div>@endif
    <main>@yield('content')</main>
    <footer>
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 py-2">
            <div class="d-flex align-items-center gap-2">
                <a class="navbar-brand m-0" href="{{ url('/') }}"><span class="brand-mark">Z</span>ZooMarket</a>
                <span class="text-muted ms-2" style="font-size: 0.82rem;">&copy; {{ date('Y') }} ZooMarket. Barcha huquqlar himoyalangan.</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a class="footer-link m-0" href="{{ route('posts.index') }}">Barcha e'lonlar</a>
                @auth
                    <a class="footer-link m-0" href="{{ route('chats.index') }}">Xabarlar</a>
                    <a class="footer-link m-0" href="{{ auth()->user()->hasRole('user') ? route('user.profile.show') : route('profile.show') }}">Profil</a>
                @endauth
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
