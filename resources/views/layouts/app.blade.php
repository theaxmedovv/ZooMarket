<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'E-Market') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --brand: #16a34a;
            --brand-mid: #15803d;
            --brand-dark: #14532d;
            --brand-soft: #f0fdf4;
            --brand-pale: #dcfce7;
            --surface: #ffffff;
            --bg: #f8fafc;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: rgba(15, 23, 42, 0.08);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-pill: 100px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: rgba(255,255,255,0.85) !important;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 0;
            height: 64px;
        }

        .navbar > .container {
            height: 100%;
            display: flex;
            align-items: center;
        }

        .navbar-brand {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 1.35rem;
            color: var(--brand) !important;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0;
            flex-shrink: 0;
        }

        .brand-dot {
            width: 8px;
            height: 8px;
            background: var(--brand);
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* ── NAV LINKS ── */
        .navbar-nav {
            gap: 2px;
        }

        .nav-link {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted) !important;
            padding: 6px 14px !important;
            border-radius: var(--radius-sm);
            transition: color 0.15s, background 0.15s;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .nav-link i { font-size: 1rem; }

        .nav-link:hover {
            color: var(--brand) !important;
            background: var(--brand-soft);
        }

        .nav-link.active {
            color: var(--brand) !important;
            background: var(--brand-pale);
            font-weight: 600;
        }

        /* ── RIGHT SIDE ── */
        .user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--brand-soft);
            border: 1px solid var(--brand-pale);
            border-radius: var(--radius-pill);
            padding: 5px 14px 5px 8px;
            font-size: 0.85rem;
        }

        .user-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--brand);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .user-name {
            font-weight: 600;
            color: var(--brand-dark);
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            color: var(--text-muted) !important;
            font-weight: 500;
            font-size: 0.85rem;
            border: 1px solid var(--border);
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-logout:hover {
            color: #dc2626 !important;
            border-color: #fecaca;
            background: #fef2f2;
        }

        .btn-login {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted) !important;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            transition: all 0.15s;
        }

        .btn-login:hover {
            color: var(--brand) !important;
            background: var(--brand-soft);
        }

        .btn-register {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--brand);
            color: white !important;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: var(--radius-pill);
            transition: background 0.15s, transform 0.1s;
            white-space: nowrap;
        }

        .btn-register:hover {
            background: var(--brand-mid);
            transform: translateY(-1px);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        /* ── DIVIDER ── */
        .nav-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
            flex-shrink: 0;
        }

        /* ── TOGGLER ── */
        .navbar-toggler {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 6px 10px;
            color: var(--text);
            background: none;
            box-shadow: none !important;
        }

        /* ── MAIN ── */
        main {
            min-height: calc(100vh - 64px - 72px);
        }

        /* ── FLASH MESSAGES ── */
        .alert-banner {
            border-radius: 0;
            border: none;
            border-bottom: 1px solid transparent;
            padding: 10px 0;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* ── FOOTER ── */
        footer {
            background: white;
            border-top: 1px solid var(--border);
        }

        footer p {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* ── CHAT BADGE ── */
        .chat-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            line-height: 1;
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--brand); }

        /* ── MOBILE ── */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: white;
                border-top: 1px solid var(--border);
                margin: 0 -12px;
                padding: 12px 16px 16px;
                box-shadow: var(--shadow-md);
            }

            .navbar-nav {
                gap: 2px;
                margin-bottom: 12px;
            }

            .nav-link {
                padding: 9px 12px !important;
            }

            .mobile-actions {
                display: flex;
                flex-direction: column;
                gap: 8px;
                padding-top: 12px;
                border-top: 1px solid var(--border);
            }

            .user-pill {
                justify-content: center;
            }

            .btn-logout, .btn-login, .btn-register {
                justify-content: center;
                width: 100%;
            }
        }

        @media (min-width: 992px) {
            .mobile-actions { display: contents; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <span class="brand-dot"></span>
                {{ config('app.name', 'E-Market') }}
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNav"
                    aria-controls="mainNav"
                    aria-expanded="false"
                    aria-label="Menyu">
                <i class="bi bi-list" style="font-size:1.3rem;"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                @auth
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a href="{{ auth()->user()->hasRole('user') ? route('user.profile.show') : route('profile.show') }}"
                           class="nav-link {{ request()->is('profile*') || request()->is('user/profile*') ? 'active' : '' }}">
                            <i class="bi bi-person"></i> Profil
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('posts.index') }}"
                           class="nav-link {{ request()->is('posts*') ? 'active' : '' }}">
                            <i class="bi bi-grid-3x3-gap"></i> Postlar
                        </a>
                    </li>

                    @if(auth()->user()->hasRole('user'))
                    <li class="nav-item">
                        <a href="{{ route('user.purchase-requests.index') }}"
                           class="nav-link {{ request()->is('user/purchase-requests*') ? 'active' : '' }}">
                            <i class="bi bi-bag-check"></i> So'rovlarim
                        </a>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('chats.index') }}"
                           class="nav-link {{ request()->is('chats*') ? 'active' : '' }} position-relative">
                            <i class="bi bi-chat-dots"></i> Xabarlar
                            @if(!empty($globalUnreadCount) && $globalUnreadCount > 0)
                                <span class="chat-badge">{{ $globalUnreadCount > 99 ? '99+' : $globalUnreadCount }}</span>
                            @endif
                        </a>
                    </li>

                    @if(auth()->user()->hasRole('seller'))
                    <li class="nav-item">
                        <a href="{{ route('admin.purchase-requests.index') }}"
                           class="nav-link {{ request()->is('admin/purchase-requests*') ? 'active' : '' }}">
                            <i class="bi bi-bag-check"></i> So'rovlar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.sold-animals.index') }}"
                           class="nav-link {{ request()->is('admin/sold-animals*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i> Sotilganlar
                        </a>
                    </li>
                    @endif
                </ul>
                @else
                <div class="mx-auto"></div>
                @endauth

                <div class="mobile-actions">
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap">
                        @auth
                            <div class="user-pill d-none d-lg-flex">
                                <div class="user-avatar" aria-hidden="true">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="user-name">{{ auth()->user()->name }}</span>
                            </div>

                            <div class="nav-divider d-none d-lg-block" aria-hidden="true"></div>

                            <form action="{{ route('logout') }}" method="POST" class="d-inline w-100 w-lg-auto">
                                @csrf
                                <button type="submit" class="btn-logout w-100 w-lg-auto">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Chiqish
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-login">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Kirish
                            </a>
                            <a href="{{ route('register') }}" class="btn-register">
                                A'zo bo'lish
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
    <div class="alert-banner alert alert-success text-center py-2 rounded-0 border-0 border-bottom border-success border-opacity-25 bg-success bg-opacity-10 text-success mb-0">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert-banner alert alert-danger text-center py-2 rounded-0 border-0 border-bottom border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger mb-0">
        <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
    </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="py-4 mt-auto">
        <div class="container text-center">
            <p>
                &copy; {{ date('Y') }} <strong>{{ config('app.name') }}</strong>
                &mdash; Barcha huquqlar himoyalangan.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
