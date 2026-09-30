{{-- Shared MegaMart-style chrome: tokens, top bar, header, category pills, footer. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    :root {
        --blue: #008ecc; --blue-dark: #0073a8; --blue-soft: #f3f9fb; --blue-tint: #e5f4fb;
        --navy: #212844; --green: #249b3e; --hot: #ff4d2e;
        --text: #666666; --soft: #f5f5f5;
    }
    body { margin: 0; background: #fff; color: #222; font-family: 'Mulish', system-ui, sans-serif; }
    .zm-wrap { width: 100%; max-width: 1240px; margin: 0 auto; padding: 0 16px; }
    .zm-topbar a, .zm-header a, .zm-pills a, .zm-footer a { text-decoration: none; color: inherit; }

    /* Top bar */
    .zm-topbar { background: var(--soft); font-size: 13px; color: var(--text); }
    .zm-topbar .zm-wrap { display: flex; align-items: center; justify-content: space-between; height: 40px; gap: 16px; }
    .zm-topbar-right { display: flex; align-items: center; white-space: nowrap; }
    .zm-topbar-right > * { display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; border-left: 1px solid #d9d9d9; }
    .zm-topbar-right > :first-child { border-left: 0; }
    .zm-topbar-right > :last-child { padding-right: 0; }
    .zm-topbar-right a:hover { color: var(--blue); }
    .zm-topbar-right i { color: var(--blue); font-size: 15px; }

    /* Header */
    .zm-header { position: sticky; top: 0; z-index: 1030; background: #fff; border-bottom: 1px solid #ededed; }
    .zm-header-row { display: flex; align-items: center; gap: 32px; height: 76px; }
    .zm-logo { flex-shrink: 0; font-size: 28px; font-weight: 900; color: var(--blue) !important; letter-spacing: -.02em; line-height: 1; }
    .zm-search { flex: 1; position: relative; display: flex; align-items: center; gap: 12px; height: 48px; max-width: 640px; padding: 0 6px 0 16px; border-radius: 12px; background: var(--blue-soft); border: 1px solid transparent; min-width: 0; margin: 0; transition: border-color .15s, background .15s; }
    .zm-search:focus-within { background: #fff; border-color: var(--blue); }
    .zm-search > button i { color: var(--blue); font-size: 18px; }
    .zm-search > input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; color: #222; font-size: 15px; }
    .zm-search > input::placeholder { color: #9a9a9a; }
    .zm-search > button { border: 0; background: none; padding: 0; cursor: pointer; line-height: 1; }

    /* Filter toggle + panel (part of the search form) */
    .zm-filter-btn { position: relative; width: 38px; height: 38px; flex-shrink: 0; display: grid; place-items: center; border-radius: 8px; }
    .zm-search .zm-filter-btn:hover, .zm-filter-btn.open { background: #fff; }
    .zm-filter-btn.has-filters, .zm-filter-btn.open { background: var(--blue) !important; }
    .zm-filter-btn.has-filters i, .zm-filter-btn.open i { color: #fff; }
    .zm-filter-btn .zm-badge { top: -6px; left: auto; right: -6px; }
    .zm-filter-panel { position: absolute; top: calc(100% + 10px); left: 0; right: 0; min-width: 560px; background: #fff; border: 1px solid #ededed; border-radius: 14px; box-shadow: 0 20px 44px rgba(0,0,0,.14); padding: 18px; display: none; z-index: 5; }
    .zm-filter-panel.open { display: block; }
    .zm-filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 18px; }
    .zm-field { display: flex; flex-direction: column; gap: 7px; margin: 0; min-width: 0; }
    .zm-field > span { font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: var(--text); }
    .zm-field > span i { color: var(--blue); margin-right: 3px; }
    .zm-field select, .zm-field input[type=text], .zm-price input, .zm-price select { width: 100%; min-width: 0; height: 42px; padding: 0 12px; border: 1px solid #e3e3e3; border-radius: 8px; background: #fff; color: #222; font-size: 14px; outline: none; }
    .zm-field select:focus, .zm-field input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(0,142,204,.12); }
    .zm-price { display: grid; grid-template-columns: 1fr 1fr 108px; gap: 8px; }
    .zm-segment { display: flex; gap: 4px; height: 42px; padding: 3px; border: 1px solid #e3e3e3; border-radius: 8px; }
    .zm-segment label { flex: 1; margin: 0; cursor: pointer; }
    .zm-segment input { position: absolute; opacity: 0; pointer-events: none; }
    .zm-segment span { height: 100%; display: grid; place-items: center; border-radius: 6px; font-size: 13px; font-weight: 700; color: var(--text); white-space: nowrap; }
    .zm-segment label:hover span { color: var(--blue); }
    .zm-segment input:checked + span { background: var(--blue); color: #fff; }
    .zm-segment input:focus-visible + span { outline: 2px solid var(--blue); outline-offset: 1px; }
    .zm-filter-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 16px; padding-top: 14px; border-top: 1px solid #ededed; font-size: 13px; color: var(--text); }
    .zm-filter-actions > div { display: flex; gap: 8px; }
    .zm-filter-reset { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 14px; border: 1px solid #e3e3e3; border-radius: 8px; font-weight: 700; color: var(--text) !important; }
    .zm-filter-reset:hover { border-color: #dc3545; color: #dc3545 !important; }
    .zm-search .zm-filter-apply { border: 0; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 20px; border-radius: 8px; background: var(--blue); color: #fff; font-weight: 800; font-size: 14px; }
    .zm-search .zm-filter-apply:hover { background: var(--blue-dark); }
    .zm-filter-apply i { color: #fff !important; font-size: 14px !important; }

    .zm-actions { display: flex; align-items: center; gap: 4px; margin-left: auto; flex-shrink: 0; font-weight: 700; color: var(--text); font-size: 14px; }
    .zm-action { position: relative; display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 12px; border: 0; border-radius: 10px; background: none; color: var(--text); font-weight: 700; cursor: pointer; white-space: nowrap; }
    .zm-action:hover, .zm-action.current { color: var(--blue); background: var(--blue-soft); }
    .zm-action i { color: var(--blue); font-size: 21px; line-height: 1; }
    .zm-badge { position: absolute; top: 2px; left: 26px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9px; background: var(--hot); color: #fff; font-size: 11px; font-weight: 800; display: grid; place-items: center; font-style: normal; line-height: 1; }
    .zm-badge[hidden] { display: none !important; }
    .zm-btn-new { display: inline-flex; align-items: center; gap: 6px; height: 44px; margin-right: 8px; padding: 0 18px; border-radius: 10px; background: var(--blue); border: 1px solid var(--blue); color: #fff !important; font-weight: 800; font-size: 14px; white-space: nowrap; }
    .zm-btn-new:hover, .zm-btn-new.active { background: var(--blue-dark); border-color: var(--blue-dark); }
    .zm-btn-new.zm-btn-outline { margin: 0 0 0 4px; background: #fff; color: var(--blue) !important; }
    .zm-btn-new.zm-btn-outline:hover { background: var(--blue-soft); }
    .zm-btn-new.zm-btn-outline span { display: inline !important; }
    @media (max-width: 480px) { .zm-btn-new.zm-btn-outline { display: none; } }

    /* Account menu */
    .zm-profile { position: relative; margin-left: 4px; }
    .zm-avatar-btn { padding: 0 6px 0 4px; gap: 4px; }
    .zm-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--blue); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 15px; }
    .zm-action .zm-caret { font-size: 12px; color: var(--text); }
    .zm-profile-menu { position: absolute; right: 0; top: calc(100% + 10px); width: 260px; background: #fff; border: 1px solid #ededed; border-radius: 14px; box-shadow: 0 16px 36px rgba(0,0,0,.12); padding: 8px; display: none; z-index: 5; }
    .zm-profile-menu.open { display: block; }
    .zm-profile-head { padding: 10px 12px 12px; margin-bottom: 6px; border-bottom: 1px solid #ededed; }
    .zm-profile-head b { display: block; color: #222; font-size: 15px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .zm-profile-head small { display: block; color: var(--text); font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .zm-profile-menu a, .zm-profile-menu button { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 12px; border: 0; background: none; border-radius: 8px; color: #222; font-weight: 600; font-size: 14px; text-align: left; cursor: pointer; }
    .zm-profile-menu a:hover, .zm-profile-menu button:hover { background: var(--blue-soft); color: var(--blue); }
    .zm-profile-menu i { color: var(--blue); font-size: 16px; }
    .zm-profile-menu .danger, .zm-profile-menu .danger i { color: #dc3545; }
    .zm-profile-menu .danger:hover { background: #fff1f0; color: #dc3545; }
    .zm-profile-menu hr { margin: 6px 0; border: 0; border-top: 1px solid #ededed; }
    .zm-profile-menu form { margin: 0; }

    /* Category pills */
    .zm-pills { border-bottom: 1px solid #ededed; background: #fff; }
    .zm-pills .zm-wrap { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; padding-top: 12px; padding-bottom: 12px; }
    .zm-pills .zm-wrap::-webkit-scrollbar { display: none; }
    .zm-pill { display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; height: 38px; padding: 0 16px; border-radius: 19px; background: var(--soft); color: #222; font-size: 14px; font-weight: 600; white-space: nowrap; transition: background .2s, color .2s; position: relative; }
    .zm-pill i { font-size: 13px; color: var(--blue); }
    .zm-pill:hover { background: var(--blue-tint); }
    .zm-pill.active { background: var(--blue); color: #fff; }
    .zm-pill.active i { color: #fff; }
    .zm-pill .zm-badge { position: static; margin-left: 2px; }

    /* Page header (x-page-head): same title rhythm on every page */
    .zm-page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 0 0 24px; }
    .zm-page-head-text { min-width: 0; }
    .zm-page-head h1 { margin: 0; font-family: 'Mulish', system-ui, sans-serif; font-size: 28px; font-weight: 800; letter-spacing: -.02em; line-height: 1.2; color: #222; }
    .zm-page-head p { margin: 6px 0 0; font-size: 15px; color: var(--text); line-height: 1.5; max-width: 640px; }
    .zm-page-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    @media (max-width: 768px) { .zm-page-head { margin-bottom: 18px; } .zm-page-head h1 { font-size: 23px; } .zm-page-head p { font-size: 14px; } }

    /* Breadcrumb row (detail pages) */
    .zm-crumbs { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; font-size: 14px; }
    .zm-crumbs .breadcrumb { flex-wrap: nowrap; min-width: 0; }
    .zm-crumbs .breadcrumb-item { white-space: nowrap; }
    .zm-crumbs .breadcrumb-item.active { overflow: hidden; text-overflow: ellipsis; min-width: 0; }
    .zm-crumbs .breadcrumb-item a { color: var(--blue); text-decoration: none; font-weight: 600; }
    .zm-crumbs-meta { color: var(--text); font-size: 13px; white-space: nowrap; }
    .zm-crumbs-meta i { margin-right: 4px; }

    /* Shared buttons and form card */
    .zm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 22px; border-radius: 10px; border: 1px solid transparent; font-weight: 800; font-size: 15px; text-decoration: none; cursor: pointer; transition: background .15s, border-color .15s, color .15s; }
    .zm-btn-primary { background: var(--blue); color: #fff; }
    .zm-btn-primary:hover { background: var(--blue-dark); color: #fff; }
    .zm-btn-ghost { background: #fff; border-color: #e3e3e3; color: var(--text); }
    .zm-btn-ghost:hover { border-color: #c9c9c9; color: #222; }
    .zm-form-card { max-width: 880px; background: #fff; border: 1px solid #ededed; border-radius: 16px; padding: 32px; }
    .zm-form-actions { display: flex; gap: 12px; flex-wrap: wrap; padding-top: 24px; border-top: 1px solid #ededed; }
    @media (max-width: 768px) { .zm-form-card { padding: 20px; } .zm-form-actions .zm-btn { flex: 1; } }

    /* Toasts */
    .zm-toasts { position: fixed; top: 16px; right: 16px; z-index: 2000; display: flex; flex-direction: column; gap: 10px; width: min(380px, calc(100vw - 32px)); pointer-events: none; }
    .zm-toast { pointer-events: auto; display: flex; align-items: flex-start; gap: 10px; padding: 13px 12px 13px 16px; border-radius: 12px; background: #fff; border: 1px solid #ededed; border-left: 4px solid var(--blue); box-shadow: 0 14px 34px rgba(0,0,0,.14); font-size: 14px; font-weight: 600; color: #222; line-height: 1.4; animation: zm-toast-in .25s ease-out; }
    .zm-toast > i { font-size: 18px; line-height: 1.2; color: var(--blue); }
    .zm-toast > span { flex: 1; }
    .zm-toast-success { border-left-color: var(--green); } .zm-toast-success > i { color: var(--green); }
    .zm-toast-warning { border-left-color: #f0a500; } .zm-toast-warning > i { color: #f0a500; }
    .zm-toast-error { border-left-color: #dc3545; } .zm-toast-error > i { color: #dc3545; }
    .zm-toast-close { border: 0; background: none; padding: 2px 4px; color: #9a9a9a; cursor: pointer; border-radius: 6px; line-height: 1; }
    .zm-toast-close:hover { color: #222; background: var(--soft); }
    .zm-toast.hiding { opacity: 0; transform: translateX(16px); transition: opacity .2s, transform .2s; }
    @keyframes zm-toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }

    /* Pagination (Bootstrap markup, site colours) */
    .pagination { gap: 6px; flex-wrap: wrap; justify-content: center; --bs-pagination-border-width: 0; }
    .pagination .page-link { min-width: 40px; height: 40px; display: grid; place-items: center; padding: 0 12px; border-radius: 10px !important; border: 1px solid #ededed; background: #fff; color: #222; font-weight: 700; box-shadow: none; }
    .pagination .page-link:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-soft); }
    .pagination .page-item.active .page-link { background: var(--blue); border-color: var(--blue); color: #fff; }
    .pagination .page-item.disabled .page-link { color: #c4c4c4; background: #fafafa; }
    nav[role=navigation] p.small { text-align: center; color: var(--text); }

    /* Pending form submits and keyboard focus */
    .zm-busy { opacity: .7; cursor: progress !important; pointer-events: none; }
    .zm-spinner { display: inline-block; width: 1em; height: 1em; margin-right: 6px; vertical-align: -.15em; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: zm-spin .7s linear infinite; }
    @keyframes zm-spin { to { transform: rotate(360deg); } }
    a:focus-visible, button:focus-visible, select:focus-visible, input:focus-visible, textarea:focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; scroll-behavior: auto !important; } }

    .zm-flash { padding: 10px 16px; text-align: center; font-weight: 700; font-size: 14px; background: var(--blue-tint); color: var(--blue-dark); }
    .zm-flash.err { background: #ffecea; color: #c2331b; }

    /* Footer */
    .zm-footer { margin-top: 64px; background: var(--blue); color: #fff; position: relative; overflow: hidden; }
    .zm-footer-grid { display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 40px; padding: 52px 0 40px; position: relative; z-index: 1; }
    .zm-footer .zm-logo { color: #fff !important; font-size: 32px; display: inline-block; margin-bottom: 26px; }
    .zm-footer h5 { font-size: 20px; font-weight: 800; margin: 0 0 16px; padding-bottom: 10px; position: relative; color: #fff; }
    .zm-footer h5::after { content: ''; position: absolute; left: 0; bottom: 0; width: 40px; height: 2px; background: #fff; }
    .zm-contact { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .zm-contact i { font-size: 22px; }
    .zm-contact small { display: block; opacity: .85; font-size: 13px; }
    .zm-contact b { font-size: 15px; }
    .zm-stores { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
    .zm-store { display: inline-flex; align-items: center; gap: 8px; background: #000; color: #fff; padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.4); }
    .zm-store i { font-size: 24px; }
    .zm-store small { display: block; font-size: 10px; opacity: .8; line-height: 1; }
    .zm-store b { font-size: 15px; line-height: 1.1; }
    .zm-footer ul { list-style: disc; margin: 0; padding-left: 18px; display: grid; gap: 10px; font-weight: 600; }
    .zm-footer ul a:hover { text-decoration: underline; }
    .zm-footer-bottom { background: var(--blue-dark); text-align: center; padding: 16px; font-size: 14px; font-weight: 600; position: relative; z-index: 1; }
    .zm-footer-deco { position: absolute; right: -60px; top: -80px; width: 360px; height: 360px; border-radius: 50%; border: 70px solid rgba(255,255,255,.07); }

    @media (max-width: 1024px) {
        .zm-topbar .zm-welcome { display: none; }
        .zm-topbar .zm-wrap { justify-content: center; }
        .zm-action .zm-label { display: none; }
        .zm-btn-new span { display: none; }
    }
    @media (max-width: 768px) {
        .zm-topbar-right a { padding: 0 10px; font-size: 12px; }
        .zm-topbar-right .zm-hide-sm { display: none; }
        .zm-header-row { flex-wrap: wrap; height: auto; gap: 12px; padding-top: 12px; padding-bottom: 12px; }
        .zm-logo { font-size: 24px; }
        .zm-action { padding: 0 8px; }
        .zm-btn-new { margin-right: 6px; padding: 8px 12px; }
        .zm-search { order: 5; flex-basis: 100%; height: 44px; }
        .zm-filter-panel { min-width: 0; }
        .zm-filter-grid { grid-template-columns: 1fr; }
        .zm-footer-grid { grid-template-columns: 1fr; gap: 28px; }
    }
</style>
