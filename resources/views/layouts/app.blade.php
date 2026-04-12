<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'E-Market') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-blue: #4361ee;
            --soft-bg: #f8fafc;
            --text-main: #1e293b;
            --nav-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--soft-bg);
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        /* Glassmorphism Navbar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: var(--nav-shadow);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--primary-blue) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            font-weight: 600;
            font-size: 0.95rem;
            color: #64748b !important;
            padding: 0.5rem 1rem !important;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary-blue) !important;
            background-color: rgba(67, 97, 238, 0.05);
        }

        .nav-link.active {
            color: var(--primary-blue) !important;
        }

        /* User Profile Dropdown style in Nav */
        .user-greeting {
            background: #f1f5f9;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            color: #475569;
        }

        .btn-logout {
            background: #fff1f2;
            color: #e11d48 !important;
            font-weight: 700;
            font-size: 0.9rem;
            border: none;
            padding: 8px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #e11d48;
            color: white !important;
            transform: scale(1.05);
        }

        main {
            min-height: 80vh;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <div class="bg-primary bg-gradient text-white rounded-3 p-1 px-2 me-1">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <span>{{ config('app.name', 'E-Market') }}</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto align-items-lg-center gap-2">
                    @auth
                        <div class="user-greeting me-lg-2 d-none d-lg-block">
                            <span class="opacity-75">Salom,</span> <strong>{{ auth()->user()->name }}</strong>
                        </div>

                        <a href="{{ auth()->user()->hasRole('user') ? route('user.profile.show') : route('profile.show') }}"
                                    class="nav-link {{ request()->is('profile*') || request()->is('user/profile*') ? 'active' : '' }}">
                            <i class="bi bi-person-circle me-1"></i> Profil
                        </a>

                        <a href="{{ route('posts.index') }}" class="nav-link {{ request()->is('posts*') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2 me-1"></i> Postlar
                        </a>

                        @if(auth()->user()->hasRole('user'))
                            <a href="{{ route('user.purchase-requests.index') }}" class="nav-link {{ request()->is('user/purchase-requests*') ? 'active' : '' }}">
                                <i class="bi bi-bag-check me-1"></i> So'rovlarim
                            </a>
                        @endif

                        @if(auth()->user()->hasRole('seller'))
                            <a href="{{ route('admin.purchase-requests.index') }}" class="nav-link {{ request()->is('admin/purchase-requests*') ? 'active' : '' }}">
                                <i class="bi bi-bag-check me-1"></i> So'rovlar
                            </a>
                        @endif

                        <div class="ms-lg-2 ps-lg-2 border-start-lg">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-logout">
                                    <i class="bi bi-power me-1"></i> Chiqish
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Kirish
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm ms-lg-2">
                            A'zo bo'lish
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="py-4 mt-5 border-top bg-white text-center">
        <p class="text-muted small mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. Barcha huquqlar himoyalangan.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
