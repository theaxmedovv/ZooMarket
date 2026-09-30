<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ZooMarket') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.site-styles')
    <style>
        /* Page tokens: the old dark palette names, remapped to the light MegaMart theme. */
        :root { --ink:#ffffff; --panel:#ffffff; --line:#ededed; --lime:#008ecc; --orange:#ff6b2b; --cream:#222222; --muted:#666666; --serif:'Mulish',sans-serif; --sans:'Mulish',sans-serif; }
        * { box-sizing:border-box; } body { line-height:1.55; } a { color:inherit; } button,input,select,textarea { font:inherit; }
        .brand-mark { display:inline-flex; width:24px; height:24px; align-items:center; justify-content:center; margin-right:7px; border-radius:6px; background:var(--lime); color:#fff; font-size:.82rem; transform:rotate(-7deg); }
        .user-avatar { display:inline-flex; width:24px; height:24px; align-items:center; justify-content:center; border-radius:50%; background:var(--lime); color:#fff; font-weight:700; font-size:.72rem; }
        .alert-banner { padding:7px; border-bottom:1px solid var(--line); background:var(--blue-soft); color:var(--blue-dark); text-align:center; font-size:.78rem; }
        .text-lime { color:var(--lime) !important; }
        main { min-height:60vh; }
        .text-muted { color:var(--muted) !important; }
        .form-control, .form-select { color:#222; }
        .container { max-width: 1240px; padding-left: 16px; padding-right: 16px; }
        /* Profile header: avatar + identity, no badges */
        .profile-header.justify-content-start { gap: 20px; padding: 24px; }
        .profile-header.justify-content-start .profile-avatar-wrap { width: 76px; height: 76px; border-radius: 50%; box-shadow: none; flex-shrink: 0; }
        .profile-header.justify-content-start .profile-avatar-image,
        .profile-header.justify-content-start .profile-avatar-fallback { border-radius: 50%; font-size: 1.9rem; color: #fff; }
        .profile-name { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: #222; }
        .profile-meta { margin: 4px 0 0; font-size: 14px; font-weight: 600; color: var(--muted); overflow-wrap: anywhere; }
        .profile-sub { margin: 6px 0 0; font-size: 14px; color: var(--muted); }
        @media (max-width: 991.98px) { .profile-header.justify-content-start { flex-direction: row !important; align-items: center !important; } }

        /* One vertical rhythm for every page: 32px under the tab row, 64px before the footer. */
        .page-shell { padding-top: 32px !important; padding-bottom: 64px !important; }
        .zm-footer { margin-top: 0; }
        @media (max-width: 768px) { .page-shell { padding-top: 20px !important; padding-bottom: 48px !important; } }

        

        
        
        .btn-search-submit { height: 44px; padding: 0 18px; background: var(--lime); color: var(--ink); border: 0; border-radius: 10px; font-weight: 700; font-size: 0.84rem; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; white-space: nowrap; transition: all 0.2s ease; }
        .btn-search-submit:hover { background: #0073a8; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0, 142, 204, 0.25); }

        
        
        /* Segmented Radio Controls (Gender, etc.) */
        .segmented-control { display: flex; background: #ffffff; border: 1px solid var(--line); border-radius: 8px; padding: 2px; gap: 2px; height: 38px; align-items: center; }
        .segment-btn { flex: 1; height: 100%; text-align: center; margin: 0; cursor: pointer; position: relative; user-select: none; display: flex; }
        .segment-btn input { position: absolute; opacity: 0; pointer-events: none; }
        .segment-btn span { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 0 2px; font-size: 0.74rem; font-weight: 600; color: var(--muted); border-radius: 6px; transition: all 0.15s ease; white-space: nowrap; }
        .segment-btn:hover span { color: var(--cream); }
        .segment-btn.active span, .segment-btn input:checked + span { background: var(--lime); color: var(--ink); font-weight: 700; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12); }

        /* Filter Action Buttons */
        .btn-filter-apply { background: var(--lime); color: var(--ink); border: 0; border-radius: 8px; height: 38px; font-weight: 700; font-size: 0.82rem; transition: all .2s; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .btn-filter-apply:hover { background: #0073a8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 142, 204, 0.25); }

        /* Catalog Toolbar */
        .catalog-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .catalog-title { font-family: var(--serif); font-size: clamp(1.3rem, 2.4vw, 1.8rem); letter-spacing: -.04em; line-height: 1.15; margin: 0; font-weight: 700; }
        .result-meta { color: var(--muted); font-size: .8rem; margin-top: 2px; }
        
        .btn-sort-dropdown { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--panel); border: 1px solid var(--line); border-radius: 8px; color: var(--cream); font-size: .78rem; font-weight: 600; text-decoration: none; transition: all .2s; }
        .btn-sort-dropdown:hover, .btn-sort-dropdown[aria-expanded="true"] { border-color: var(--lime); color: var(--lime); }

        /* Active Filter Chips Bar */
        .active-filter-chips { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; padding: 8px 12px; background: rgba(255, 255, 255, 0.7); border: 1px solid var(--line); border-radius: 12px; }
        .chips-label { font-size: 0.73rem; color: var(--muted); font-weight: 600; margin-right: 4px; display: inline-flex; align-items: center; }
        .filter-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; background: rgba(0, 142, 204, 0.09); border: 1px solid rgba(0, 142, 204, 0.25); border-radius: 9999px; color: var(--lime); font-size: 0.73rem; font-weight: 500; text-decoration: none; transition: all 0.15s ease; }
        .filter-chip:hover { background: rgba(255, 107, 43, 0.15); border-color: rgba(255, 107, 43, 0.4); color: var(--orange); }
        .filter-chip i { font-size: 0.75rem; opacity: 0.8; }
        .chip-clear-all { background: rgba(0, 0, 0, 0.05); border-color: var(--line); color: var(--muted); }
        .chip-clear-all:hover { background: rgba(255, 60, 60, 0.15); border-color: #dc3545; color: #dc3545; }


        /* Listing Grid & Cards */
        /* Modern Marketplace Card Styles */
        .listing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 20px; }
        .post-col { display: flex; flex-direction: column; }
        .listing-grid > .col-12 { grid-column: 1 / -1; }
        .post-card { height: 100%; min-height: 430px; background: linear-gradient(180deg, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0.95) 100%); border: 1px solid var(--line); border-radius: 18px; overflow: hidden; display: flex; flex-direction: column; position: relative; transition: transform 0.28s cubic-bezier(0.2, 0, 0, 1), border-color 0.28s ease, box-shadow 0.28s ease; }
        .post-card:hover { transform: translateY(-5px); border-color: rgba(0, 142, 204, 0.45); box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 142, 204, 0.15); }
        .card-img-wrap { height: 225px; width: 100%; position: relative; overflow: hidden; background: #f5f5f5; border-bottom: 1px solid var(--line); }
        .card-img-link { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; text-decoration: none; position: relative; overflow: hidden; }
        .card-img-backdrop { position: absolute; inset: -14px; background-size: cover; background-position: center; filter: blur(18px) brightness(0.28) saturate(1.3); opacity: 0.85; transform: scale(1.15); pointer-events: none; }
        .card-img { position: relative; z-index: 1; width: 100%; height: 100%; object-fit: cover; transition: transform 0.45s cubic-bezier(0.2, 0, 0, 1); }
        .post-card:hover .card-img { transform: scale(1.07); }
        .card-img-placeholder { height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; color: var(--muted); gap: 8px; font-size: 0.85rem; background: #f5f5f5; }
        .card-badges-top { position: absolute; top: 10px; left: 10px; display: flex; align-items: center; gap: 6px; z-index: 3; pointer-events: none; }
        .card-tag-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; padding: 3px 9px; border-radius: 9999px; letter-spacing: 0.02em; backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12); }
        .card-tag-pill.tag-cat { background: rgba(255, 255, 255, 0.82); border: 1px solid rgba(0, 0, 0, 0.12); color: var(--cream); }
        .card-tag-pill.tag-stock { background: rgba(0, 142, 204, 0.9); border: 1px solid rgba(0, 142, 204, 1); color: var(--ink); }
        .card-tag-pill.tag-sold { background: rgba(239, 68, 68, 0.9); border: 1px solid rgba(239, 68, 68, 1); color: #fff; }
        .card-heart-form { position: absolute; top: 10px; right: 10px; z-index: 4; }
        .btn-card-heart { width: 34px; height: 34px; border-radius: 50%; background: rgba(255, 255, 255, 0.75); border: 1px solid rgba(0, 0, 0, 0.15); backdrop-filter: blur(10px); color: var(--cream); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; cursor: pointer; transition: all 0.2s ease; }
        .btn-card-heart:hover { background: rgba(255, 107, 43, 0.2); color: var(--orange); border-color: var(--orange); transform: scale(1.1); }
        .btn-card-heart.liked { background: rgba(255, 107, 43, 0.25); color: var(--orange); border-color: var(--orange); }
        .card-body-inner { display: flex; flex-direction: column; flex: 1; padding: 14px 16px 16px; }
        .card-author { display: flex; align-items: center; gap: 8px; }
        .author-avatar { width: 26px; height: 26px; border-radius: 50%; background: #e5f4fb; border: 1px solid rgba(0, 142, 204, 0.3); color: var(--lime); display: grid; place-items: center; font-weight: 700; font-size: 0.72rem; flex-shrink: 0; }
        .author-meta { line-height: 1.25; }
        .author-name { font-size: 0.76rem; font-weight: 600; color: var(--cream); display: block; }
        .author-time { font-size: 0.65rem; color: #8a8a8a; }
        .btn-card-menu { width: 26px; height: 26px; background: transparent; border: none; color: var(--muted); display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: color 0.15s ease; }
        .btn-card-menu:hover { color: var(--cream); background: rgba(0, 0, 0, 0.06); }
        .card-title { font-family: var(--serif); font-size: 1.05rem; font-weight: 700; line-height: 1.35; margin: 4px 0 8px; letter-spacing: -0.01em; }
        .card-title-link { color: var(--cream); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s ease; }
        .card-title-link:hover { color: var(--lime); }
        .card-meta-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
        .meta-chip { font-size: 0.72rem; color: var(--muted); background: rgba(0, 0, 0, 0.035); border: 1px solid rgba(0, 0, 0, 0.06); border-radius: 6px; padding: 2px 7px; display: inline-flex; align-items: center; gap: 4px; }
        .card-price-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
        .card-price-num { font-family: var(--serif); font-size: 1.25rem; font-weight: 800; color: var(--lime); letter-spacing: -0.02em; }
        .card-price-curr { font-size: 0.76rem; font-weight: 600; color: rgba(34, 34, 34, 0.7); }
        .card-negotiable-tag { font-size: 0.65rem; font-weight: 600; color: var(--lime); background: rgba(0, 142, 204, 0.1); border: 1px solid rgba(0, 142, 204, 0.25); padding: 2px 7px; border-radius: 9999px; }
        .card-footer-inner { display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid rgba(0, 0, 0, 0.05); }
        .btn-card-view { flex: 1; height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: rgba(0, 0, 0, 0.05); border: 1px solid var(--line); border-radius: 10px; color: var(--cream); font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; }
        .btn-card-view:hover { background: rgba(0, 142, 204, 0.12); border-color: var(--lime); color: var(--lime); }
        .btn-card-quick-buy { height: 36px; padding: 0 12px; display: inline-flex; align-items: center; justify-content: center; background: var(--lime); color: var(--ink); border-radius: 10px; font-size: 0.76rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease; }
        .btn-card-quick-buy:hover { background: #0073a8; color: var(--ink); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 142, 204, 0.25); }
        .empty-state { padding: 60px 20px; text-align: center; border: 1px dashed var(--line); border-radius: 16px; background: rgba(255, 255, 255, .3); }
        .empty-icon { color: var(--lime); font-size: 2.2rem; margin-bottom: 12px; }
        .empty-title { font-family: var(--serif); font-size: 1.3rem; margin-bottom: 6px; }
        .empty-text { color: var(--muted); font-size: .88rem; margin: 0; }
        .buy-modal { background: var(--panel); color: var(--cream); border: 1px solid var(--line)!important; border-radius: 16px; }
        .buy-modal .text-muted { color: var(--muted)!important; }
        .btn-profile-dropdown { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; background:transparent; border:1px solid var(--line); border-radius:7px; color:var(--cream); font-size:.78rem; font-weight:600; text-decoration:none; transition:all .2s ease; }
        .btn-profile-dropdown:hover, .btn-profile-dropdown[aria-expanded="true"] { border-color:var(--lime); color:var(--lime); background:rgba(0, 142, 204, .08); }
        .btn-profile-dropdown::after { font-size:.65rem; margin-left:4px; vertical-align:middle; }
        .dropdown-menu-dark { background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:6px; min-width:140px; }
        .dropdown-menu-dark .dropdown-item { font-size:.78rem; padding:6px 12px; border-radius:6px; color:var(--cream); transition:all .15s ease; }
        .dropdown-menu-dark .dropdown-item:hover, .dropdown-menu-dark .dropdown-item.active { background:rgba(0, 142, 204, .12); color:var(--lime); }
        .dropdown-menu-dark .dropdown-item.text-danger:hover { background:rgba(255,60,60,.15); color:#dc3545!important; }
        .dropdown-menu-dark .dropdown-divider { border-color:var(--line); }
        @media(max-width:600px) {
            .listing-grid { grid-template-columns: 1fr; }
            .card-img-wrap { height: 260px; }
            .container { padding-left: 14px; padding-right: 14px; }
        }
    </style>
</head>
<body>
    @include('partials.site-header')
    <main>@yield('content')</main>
    @include('partials.site-footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
