@extends('layouts.app')

@section('content')
@php
    $accent = 'user';
@endphp

<div class="container py-5 profile-page profile-{{ $accent }}">
    <div class="profile-hero p-4 p-lg-5 rounded-5 text-white mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8 animate-fade-in">
                <span class="badge rounded-pill bg-white text-primary px-3 py-2 fw-bold mb-3 shadow-sm">User Profile</span>
                <h1 class="display-5 fw-black mb-2 tracking-tight">{{ $user->name }}</h1>
                <p class="mb-0 opacity-75 fs-5">Shaxsiy profil sahifangiz. Bu yerda ma'lumotlaringizni boshqarishingiz va yoqtirgan mahsulotlaringizni kuzatishingiz mumkin.</p>
            </div>

            <div class="col-lg-4 text-lg-end animate-fade-in">
                <div class="profile-avatar mx-lg-auto ms-lg-auto shadow-lg">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-100 h-100 rounded-4 object-fit-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-custom border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 p-3 bg-white border-start border-success border-4 slide-down">
            <div class="icon-box bg-success text-white rounded-circle me-3 p-2 d-flex align-items-center justify-content-center" style="width:30px; height:30px;">
                <i class="bi bi-check2"></i>
            </div>
            <div class="fw-semibold text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-5">
        <div class="col-md-4 animate-up" style="animation-delay: 0.1s">
            <div class="card border-0 shadow-sm rounded-4 h-100 info-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                            <i class="bi bi-envelope-fill fs-5"></i>
                        </div>
                        <div class="text-muted small fw-bold text-uppercase tracking-wider">Email Manzil</div>
                    </div>
                    <div class="fw-bold fs-5 text-dark">{{ $user->email }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 animate-up" style="animation-delay: 0.2s">
            <div class="card border-0 shadow-sm rounded-4 h-100 info-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-shape bg-success bg-opacity-10 text-success rounded-3 p-2 me-3">
                            <i class="bi bi-telephone-fill fs-5"></i>
                        </div>
                        <div class="text-muted small fw-bold text-uppercase tracking-wider">Telefon</div>
                    </div>
                    <div class="fw-bold fs-5 text-dark">{{ $user->phone ?: 'Kiritilmagan' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 animate-up" style="animation-delay: 0.3s">
            <div class="card border-0 shadow-sm rounded-4 h-100 info-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-3 p-2 me-3">
                            <i class="bi bi-heart-fill fs-5"></i>
                        </div>
                        <div class="text-muted small fw-bold text-uppercase tracking-wider">Yoqtirilganlar</div>
                    </div>
                    <div class="fw-bold fs-4 text-dark">{{ $user->liked_posts_count }} <span class="fs-6 fw-normal text-muted">ta post</span></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 animate-up" style="animation-delay: 0.4s">
            <div class="card border-0 shadow-sm rounded-4 h-100 info-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-2 me-3">
                            <i class="bi bi-telegram fs-5"></i>
                        </div>
                        <div class="text-muted small fw-bold text-uppercase tracking-wider">Telegram</div>
                    </div>
                    <div class="fw-bold fs-5 text-dark">{{ $user->telegram_username ? '@' . ltrim($user->telegram_username, '@') : 'Kiritilmagan' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-5 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 p-lg-5 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-black mb-1 tracking-tight">Saqlangan mahsulotlar</h3>
                            <p class="text-muted mb-0 small">Sizga yoqqan e'lonlar ro'yxati</p>
                        </div>
                        <a href="{{ route('posts.index') }}" class="btn btn-light rounded-pill px-4 fw-bold border shadow-sm hover-lift">
                            <i class="bi bi-plus-lg me-1"></i> Ko'proq
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="liked-posts-list">
                        @forelse($likedPosts as $post)
                            <div class="recent-post d-flex justify-content-between align-items-center gap-3 py-4 {{ ! $loop->last ? 'border-bottom' : '' }} transition-all hover-bg-light rounded-4 px-3 mx-n3">
                                <div class="d-flex align-items-center gap-3">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" class="rounded-3 object-fit-cover" style="width: 60px; height: 60px;" alt="">
                                    @else
                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-1 fw-bold">
                                            <a href="{{ route('posts.show', $post) }}" class="text-decoration-none text-dark post-detail-link">
                                                {{ $post->title }}
                                            </a>
                                        </h6>
                                        <div class="text-muted x-small fw-semibold">
                                            <i class="bi bi-person me-1"></i> {{ $post->user->name }} •
                                            <i class="bi bi-calendar3 me-1 ms-1"></i> {{ optional($post->pivot->created_at)->format('d M') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <form action="{{ route('posts.like', $post) }}" method="POST" onsubmit="return confirm('Yoqtirilganlardan o\'chirilsinmi?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger border p-2" title="O'chirish">
                                            <i class="bi bi-heart-fill"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                        Batafsil
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state rounded-5 p-5 text-center bg-light border-dashed">
                                <i class="bi bi-heart-break display-4 text-muted opacity-25 mb-3 d-block"></i>
                                <h5 class="fw-bold text-dark">Hozircha hech narsa yo'q</h5>
                                <p class="text-muted small mb-4">Sizga yoqqan mahsulotlarni keyinroq tez topish uchun ularga "Like" bosing.</p>
                                <a href="{{ route('posts.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Mahsulotlarni ko'rish</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-5 mb-4 overflow-hidden">
                <div class="card-header bg-primary py-4 px-5 border-0">
                    <h4 class="text-white fw-black mb-0 tracking-tight">Profil ma'lumotlari</h4>
                    <p class="text-white-50 x-small mb-0 text-uppercase tracking-wider">Ism, telefon, telegram va rasm</p>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label for="name" class="form-label fw-bold text-dark small ms-1">Ism</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none" required>
                        </div>

                        <div>
                            <label for="phone" class="form-label fw-bold text-dark small ms-1">Telefon</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none" placeholder="+998 90 123 45 67">
                        </div>

                        <div>
                            <label for="telegram_username" class="form-label fw-bold text-dark small ms-1">Telegram username</label>
                            <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username', $user->telegram_username) }}" class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none" placeholder="username">
                        </div>

                        <div>
                            <label for="avatar" class="form-label fw-bold text-dark small ms-1">Profil rasmi</label>
                            <input type="file" name="avatar" id="avatar" class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-black shadow-sm mt-1 hover-lift">
                            <i class="bi bi-floppy2 me-2"></i> Profilni saqlash
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-5 mb-4 overflow-hidden">
                <div class="card-header bg-dark py-4 px-5 border-0">
                    <h4 class="text-white fw-black mb-0 tracking-tight">Xavfsizlik</h4>
                    <p class="text-white-50 x-small mb-0 text-uppercase tracking-wider">Parolni yangilash</p>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('profile.password.update') }}" method="POST" class="d-grid gap-4">
                        @csrf

                        <div class="form-group">
                            <label for="current_password" class="form-label fw-bold text-dark small ms-1">Joriy parol</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback ps-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label fw-bold text-dark small ms-1">Yangi parol</label>
                            <input type="password" name="password" id="password"
                                   class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback ps-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label fw-bold text-dark small ms-1">Yangi parolni tasdiqlang</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control form-control-lg bg-light border-0 rounded-4 shadow-none" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-black shadow-sm mt-2 hover-lift">
                            <i class="bi bi-shield-lock me-2"></i> Saqlash
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-5 bg-primary text-white overflow-hidden">
                <div class="card-body p-4 p-lg-5">
                    <h4 class="fw-black mb-3 tracking-tight">Yordam kerakmi?</h4>
                    <p class="opacity-75 small mb-4">Profil bilan bog'liq muammolar bo'lsa, qo'llab-quvvatlash xizmatiga murojaat qiling.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('posts.index') }}" class="btn btn-white bg-white text-primary rounded-pill py-2 fw-bold shadow-sm">
                            <i class="bi bi-grid-1x2 me-1"></i> E'lonlarni ko'rish
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root { --market-primary: #2563eb; }
    .fw-black { font-weight: 800; }
    .tracking-tight { letter-spacing: -1px; }
    .x-small { font-size: 0.75rem; }

    .profile-hero {
        background: linear-gradient(135deg, #0f172a 0%, #2563eb 60%, #38bdf8 100%);
        box-shadow: 0 20px 45px rgba(37, 99, 235, 0.2);
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 35px;
        display: grid;
        place-items: center;
        font-size: 3rem;
        font-weight: 800;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(15px);
        color: white;
    }

    .info-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
    }

    .hover-bg-light:hover {
        background-color: #f8fafc;
    }

    .post-detail-link:hover {
        color: var(--market-primary) !important;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .border-dashed {
        border: 2px dashed #e2e8f0 !important;
    }

    /* Form Controls */
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1) !important;
        border-color: var(--market-primary) !important;
    }

    /* Animations */
    .animate-up { animation: fadeInUp 0.6s ease forwards; opacity: 0; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hover-lift { transition: all 0.2s; }
    .hover-lift:hover { transform: translateY(-2px); shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>
@endsection
