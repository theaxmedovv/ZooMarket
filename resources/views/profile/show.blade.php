@extends('layouts.app')

@section('content')
@php
    $isSeller = $user->hasRole('seller');
    $roleLabel = $isSeller ? 'Seller' : 'User';
    $roleBadge = $isSeller ? 'bg-warning text-dark' : 'bg-primary';
    $accent = $isSeller ? 'seller' : 'user';
@endphp

<div class="container py-5 profile-page profile-{{ $accent }}">
    <div class="profile-hero p-4 p-lg-5 rounded-5 text-white mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill {{ $roleBadge }} px-3 py-2 fw-semibold mb-3">{{ $roleLabel }} Profile</span>
                <h1 class="display-5 fw-bold mb-2">{{ $user->name }}</h1>
                <p class="mb-0 opacity-75">Bu sahifada akkauntingiz haqida asosiy ma'lumotlar va oxirgi postlar ko'rsatiladi.</p>
            </div>

            <div class="col-lg-4 text-lg-end">
                <div class="profile-avatar mx-lg-auto ms-lg-auto">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Email</div>
                    <div class="fw-semibold fs-5">{{ $user->email }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Rol</div>
                    <div class="fw-semibold fs-5">{{ $roleLabel }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Postlar</div>
                    <div class="fw-semibold fs-5">{{ $user->posts_count }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1">So'nggi postlar</h3>
                            <p class="text-muted mb-0">Siz yaratgan eng oxirgi postlar.</p>
                        </div>
                        <a href="{{ route('posts.index') }}" class="btn btn-outline-primary rounded-pill px-3">Postlar</a>
                    </div>

                    @forelse($recentPosts as $post)
                        <div class="recent-post d-flex justify-content-between align-items-start gap-3 py-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <h5 class="mb-1 fw-semibold">{{ $post->title }}</h5>
                                <div class="text-muted small">{{ $post->created_at->format('d M Y, H:i') }}</div>
                            </div>
                            <span class="badge text-bg-light border">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="empty-state rounded-4 p-4 text-center bg-light">
                            <div class="text-muted fw-semibold">Hozircha post yo'q</div>
                            <p class="text-muted small mb-0 mt-2">Yangi post yaratgandan keyin ular shu yerda ko'rinadi.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="fw-bold mb-3">Tezkor amallar</h3>
                    <p class="text-muted mb-4">Profilingizdan bevosita kerakli bo'limlarga o'ting.</p>

                    <div class="d-grid gap-3">
                        <a href="{{ route('posts.index') }}" class="btn btn-primary btn-lg rounded-4">
                            Postlar sahifasi
                        </a>

                        @if($isSeller)
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-lg rounded-4">
                                Seller Dashboard
                            </a>
                        @else
                            <a href="{{ route('posts.index') }}" class="btn btn-outline-dark btn-lg rounded-4">
                                Kontentni ko'rish
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="fw-bold mb-3">Parolni almashtirish</h3>
                    <p class="text-muted mb-4">Xavfsizlik uchun joriy parolni tasdiqlang.</p>

                    <form action="{{ route('profile.password.update') }}" method="POST" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label for="current_password" class="form-label fw-semibold">Joriy parol</label>
                            <input
                                type="password"
                                name="current_password"
                                id="current_password"
                                class="form-control form-control-lg bg-light border-0"
                                required
                            >
                        </div>

                        <div>
                            <label for="password" class="form-label fw-semibold">Yangi parol</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control form-control-lg bg-light border-0"
                                required
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label fw-semibold">Yangi parolni tasdiqlang</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control form-control-lg bg-light border-0"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-dark btn-lg rounded-4">
                            Parolni yangilash
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-page {
        color: #0f172a;
    }

    .profile-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 60%, #2563eb 100%);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
    }

    .profile-user .profile-hero {
        background: linear-gradient(135deg, #0f172a 0%, #2563eb 60%, #38bdf8 100%);
    }

    .profile-seller .profile-hero {
        background: linear-gradient(135deg, #111827 0%, #f59e0b 55%, #fb7185 100%);
    }

    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 28px;
        display: grid;
        place-items: center;
        font-size: 2.5rem;
        font-weight: 800;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(10px);
    }

    .recent-post:last-child {
        border-bottom: 0 !important;
    }

    .empty-state {
        border: 1px dashed rgba(148, 163, 184, 0.35);
    }
</style>
@endsection
