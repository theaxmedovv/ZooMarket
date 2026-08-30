@extends('layouts.app')

@section('content')
@php
    $isSeller = $user->hasRole('seller');
    $roleLabel = $isSeller ? 'Sotuvchi' : 'Xaridor';
    $roleBadge = $isSeller ? 'bg-warning text-dark' : 'bg-primary';
    $accent = $isSeller ? 'seller' : 'user';
@endphp

<div class="container py-5 profile-page profile-{{ $accent }}">
    <div class="profile-hero p-4 p-lg-5 rounded-5 text-white mb-4 position-relative overflow-hidden shadow-lg">
        <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
            <div class="col-lg-8 text-center text-lg-start">
                <div class="d-flex align-items-center justify-content-center justify-content-lg-start flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill {{ $roleBadge }} px-3 py-2 fw-bold shadow-sm text-uppercase tracking-wider">
                        <i class="bi {{ $isSeller ? 'bi-shop' : 'bi-person-badge' }} me-1"></i> {{ $roleLabel }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light px-3 py-1 rounded-pill fw-semibold">
                            <i class="bi bi-box-arrow-right me-1"></i> Chiqish
                        </button>
                    </form>
                </div>
                <h1 class="display-4 fw-black mb-2">{{ $user->name }}</h1>
                <p class="mb-0 opacity-75 fs-5 fw-light">Sizning shaxsiy kabinetingiz. Ma'lumotlarni boshqaring va faoliyatingizni kuzatib boring.</p>
            </div>

            <div class="col-lg-4 text-center text-lg-end">
                <div class="profile-avatar-wrapper mx-auto ms-lg-auto border border-4 border-white border-opacity-25 rounded-circle shadow-lg overflow-hidden" style="width: 130px; height: 130px;">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-10 fs-1 fw-black">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="hero-decor-1"></div>
        <div class="hero-decor-2"></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <div class="icon-circle bg-primary-soft text-primary mx-auto mb-2">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="text-muted x-small text-uppercase fw-bold">Email</div>
                <div class="fw-bold truncate px-2">{{ $user->email }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <div class="icon-circle bg-success-soft text-success mx-auto mb-2">
                    <i class="bi bi-newspaper"></i>
                </div>
                <div class="text-muted x-small text-uppercase fw-bold">Postlar</div>
                <div class="fw-bold fs-5">{{ $user->posts_count ?? 0 }} ta</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <div class="icon-circle bg-warning-soft text-warning mx-auto mb-2">
                    <i class="bi bi-telephone"></i>
                </div>
                <div class="text-muted x-small text-uppercase fw-bold">Telefon</div>
                <div class="fw-bold fs-5">{{ $user->phone ?: '—' }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <div class="icon-circle bg-info-soft text-info mx-auto mb-2">
                    <i class="bi bi-send"></i>
                </div>
                <div class="text-muted x-small text-uppercase fw-bold">Telegram</div>
                <div class="fw-bold fs-5">{{ $user->telegram_username ? '@' . ltrim($user->telegram_username, '@') : '—' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-5 mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-dark text-white rounded-4 me-3">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">Ma'lumotlarni tahrirlash</h3>
                            <p class="text-muted small mb-0">Profil ma'lumotlarini doimo yangilab turing.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-4">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">To'liq ism</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control form-control-lg bg-light border-0 fs-6" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Telefon nomer</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-phone"></i></span>
                                <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 fs-6" value="{{ old('phone', $user->phone) }}" placeholder="+998 90 123 45 67">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Telegram username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-telegram"></i></span>
                                <input type="text" name="telegram_username" class="form-control form-control-lg bg-light border-0 fs-6" value="{{ old('telegram_username', $user->telegram_username) }}" placeholder="username">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Profil rasmi</label>
                            <input type="file" name="avatar" class="form-control form-control-lg bg-light border-0 fs-6" accept="image/*">
                        </div>

                        <div class="col-12 mt-4 pt-2">
                            <button type="submit" class="btn btn-dark btn-lg rounded-pill px-5 shadow-sm hover-lift">
                                O'zgarishlarni saqlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-5 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Sizning postlaringiz</h4>
                        <a href="{{ route('posts.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Hammasi</a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @forelse($recentPosts as $post)
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-4 d-flex align-items-center justify-content-between border border-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white rounded-3 shadow-sm p-2" style="width: 45px; height: 45px;">
                                        <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ Str::limit($post->title, 40) }}</h6>
                                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <a href="{{ route('posts.show', $post) }}" class="btn btn-white btn-sm rounded-circle shadow-sm border"><i class="bi bi-eye"></i></a>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 py-4 text-center">
                            <p class="text-muted mb-0 small">Hozircha hech qanday post yaratmagansiz.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-5 mb-4 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-shield-lock me-2 text-danger"></i> Xavfsizlik
                    </h5>
                    <form action="{{ route('profile.password.update') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label small fw-bold">Hozirgi parol</label>
                            <input type="password" name="current_password" class="form-control bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Yangi parol</label>
                            <input type="password" name="password" class="form-control bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Tasdiqlash</label>
                            <input type="password" name="password_confirmation" class="form-control bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-danger-soft text-danger w-100 rounded-pill fw-bold">
                                Parolni yangilash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Global Overrides */
    body { background-color: #f0f2f5; }
    .fw-black { font-weight: 900; }
    .x-small { font-size: 0.7rem; }
    .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Hero Background Decors */
    .hero-decor-1 { position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }
    .hero-decor-2 { position: absolute; bottom: -30px; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%; }

    /* Profile Specific Colors */
    .profile-user .profile-hero { background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%); }
    .profile-seller .profile-hero { background: linear-gradient(135deg, #f72585 0%, #7209b7 100%); }

    /* Stats Icons */
    .icon-circle { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .bg-primary-soft { background: #eef2ff; }
    .bg-success-soft { background: #ecfdf5; }
    .bg-warning-soft { background: #fffbeb; }
    .bg-info-soft { background: #f0f9ff; }
    .btn-danger-soft { background: #fff1f2; border: 1px solid #ffe4e6; }
    .btn-danger-soft:hover { background: #e11d48; color: white !important; }

    /* Cards and Inputs */
    .card { transition: transform 0.3s ease; }
    .form-control:focus { background-color: #fff !important; box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15) !important; }
    .input-group-text { color: #94a3b8; }

    /* Animations */
    .hover-lift { transition: 0.3s; }
    .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>
@endsection
