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
    .zm-topbar-right a { display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; border-left: 1px solid #d9d9d9; }
    .zm-topbar-right a:first-child { border-left: 0; }
    .zm-topbar-right a:hover { color: var(--blue); }
    .zm-topbar-right i { color: var(--blue); font-size: 15px; }

    /* Header */
    .zm-header { position: sticky; top: 0; z-index: 1030; background: #fff; border-bottom: 1px solid #ededed; }
    .zm-header-row { display: flex; align-items: center; gap: 28px; height: 84px; }
    .zm-brand { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
    .zm-menu-btn { width: 44px; height: 44px; border: 0; border-radius: 8px; background: var(--blue-soft); color: var(--blue); font-size: 22px; cursor: pointer; display: grid; place-items: center; }
    .zm-menu-btn:hover, .zm-menu-btn.open { background: var(--blue); color: #fff; }
    .zm-logo { font-size: 30px; font-weight: 900; color: var(--blue) !important; letter-spacing: -.02em; line-height: 1; }
    .zm-search { flex: 1; display: flex; align-items: center; gap: 12px; height: 50px; padding: 0 18px; border-radius: 10px; background: var(--blue-soft); min-width: 0; margin: 0; }
    .zm-search i { color: var(--blue); font-size: 18px; }
    .zm-search input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; color: #222; font-size: 15px; }
    .zm-search input::placeholder { color: #9a9a9a; }
    .zm-search button { border: 0; background: none; padding: 0; cursor: pointer; line-height: 1; }
    .zm-actions { display: flex; align-items: center; flex-shrink: 0; font-weight: 700; color: var(--text); font-size: 15px; }
    .zm-action { position: relative; display: inline-flex; align-items: center; gap: 8px; padding: 0 16px; border: 0; border-left: 1px solid #d9d9d9; background: none; color: var(--text); font-weight: 700; cursor: pointer; white-space: nowrap; }
    .zm-actions > :first-child { border-left: 0; }
    .zm-action:hover { color: var(--blue); }
    .zm-action i { color: var(--blue); font-size: 22px; }
    .zm-badge { position: absolute; top: -8px; left: 28px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9px; background: var(--hot); color: #fff; font-size: 11px; font-weight: 800; display: grid; place-items: center; font-style: normal; }
    .zm-btn-new { display: inline-flex; align-items: center; gap: 6px; margin-right: 12px; padding: 10px 16px; border-radius: 10px; background: var(--blue); color: #fff !important; font-weight: 800; font-size: 14px; white-space: nowrap; }
    .zm-btn-new:hover { background: var(--blue-dark); }

    /* Profile dropdown */
    .zm-profile { position: relative; border-left: 1px solid #d9d9d9; }
    .zm-profile .zm-action { border-left: 0; }
    .zm-profile-menu { position: absolute; right: 0; top: calc(100% + 14px); min-width: 220px; background: #fff; border: 1px solid #ededed; border-radius: 12px; box-shadow: 0 16px 36px rgba(0,0,0,.12); padding: 8px; display: none; z-index: 5; }
    .zm-profile-menu.open { display: block; }
    .zm-profile-menu a, .zm-profile-menu button { display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border: 0; background: none; border-radius: 8px; color: #222; font-weight: 600; font-size: 14px; text-align: left; cursor: pointer; }
    .zm-profile-menu a:hover, .zm-profile-menu button:hover { background: var(--blue-soft); color: var(--blue); }
    .zm-profile-menu i { color: var(--blue); font-size: 16px; }
    .zm-profile-menu .danger, .zm-profile-menu .danger i { color: #dc3545; }
    .zm-profile-menu .danger:hover { background: #fff1f0; color: #dc3545; }
    .zm-profile-menu hr { margin: 6px 0; border: 0; border-top: 1px solid #ededed; }
    .zm-profile-menu form { margin: 0; }

    /* Category drawer */
    .zm-drawer { position: absolute; top: 100%; left: 0; right: 0; display: none; }
    .zm-drawer.open { display: block; }
    .zm-drawer-panel { width: 300px; background: #fff; border: 1px solid #ededed; border-radius: 0 0 12px 12px; box-shadow: 0 20px 40px rgba(0,0,0,.1); padding: 10px; }
    .zm-drawer-panel a { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; font-weight: 600; color: #222; }
    .zm-drawer-panel a:hover { background: var(--blue-soft); color: var(--blue); }
    .zm-drawer-panel i { color: var(--blue); font-size: 18px; width: 22px; text-align: center; }
    .zm-drawer-panel .emoji { font-size: 20px; }

    /* Category pills */
    .zm-pills .zm-wrap { display: flex; gap: 12px; overflow-x: auto; scrollbar-width: none; padding-top: 16px; padding-bottom: 16px; }
    .zm-pills .zm-wrap::-webkit-scrollbar { display: none; }
    .zm-pill { display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; padding: 9px 18px; border-radius: 22px; background: var(--soft); color: #222; font-size: 14px; font-weight: 600; transition: background .2s, color .2s; position: relative; }
    .zm-pill i { font-size: 13px; color: var(--blue); }
    .zm-pill:hover { background: var(--blue-tint); }
    .zm-pill.active { background: var(--blue); color: #fff; }
    .zm-pill.active i { color: #fff; }
    .zm-pill .zm-badge { position: static; margin-left: 2px; }

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
        .zm-menu-btn { width: 40px; height: 40px; }
        .zm-actions { margin-left: auto; }
        .zm-action { padding: 0 10px; }
        .zm-btn-new { margin-right: 6px; padding: 8px 12px; }
        .zm-search { order: 5; flex-basis: 100%; height: 44px; }
        .zm-drawer-panel { width: 100%; }
        .zm-footer-grid { grid-template-columns: 1fr; gap: 28px; }
    }
</style>
