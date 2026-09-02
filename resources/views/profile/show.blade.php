@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell">
    <div class="profile-shell">
        <div class="profile-header">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 w-100">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-primary-soft text-primary fw-bold px-3 py-2 rounded-pill">
                            <i class="bi bi-person-badge me-1"></i> Admin profil
                        </span>
                        <span class="badge bg-panel-soft text-muted fw-semibold px-2 py-1 rounded-pill">Seller dashboard</span>
                    </div>
                    <h1 class="h2 fw-bold mb-2 text-cream font-serif m-0">{{ $user->name }}</h1>
                    <p class="text-muted small mb-0">Sotuvchi kabinetingiz. E'lonlarni boshqaring, so'rovlarni kuzatib boring va profil ma'lumotlarini yangilang.</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                    <span class="badge bg-panel-soft text-muted fw-semibold px-2 py-1 rounded-pill align-self-start">Admin</span>
                </div>
            </div>

            <div class="profile-avatar-wrap">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="profile-avatar-image">
                @else
                    <div class="profile-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert-banner mb-4 rounded-3 py-2 px-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-lime fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-icon icon-mail"><i class="bi bi-envelope-fill"></i></div>
                    <div class="metric-label">Email</div>
                    <div class="metric-value">{{ $user->email }}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-icon icon-posts"><i class="bi bi-postcard-fill"></i></div>
                    <div class="metric-label">Postlar</div>
                    <div class="metric-value">{{ $user->posts_count ?? 0 }} <span class="metric-small">ta</span></div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-icon icon-phone"><i class="bi bi-telephone-fill"></i></div>
                    <div class="metric-label">Telefon</div>
                    <div class="metric-value">{{ $user->phone ?: 'Kiritilmagan' }}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-icon icon-telegram"><i class="bi bi-telegram"></i></div>
                    <div class="metric-label">Telegram</div>
                    <div class="metric-value">{{ $user->telegram_username ? '@' . ltrim($user->telegram_username, '@') : 'Kiritilmagan' }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-7">
                <div class="panel-box">
                    <div class="panel-header">
                        <div>
                            <h3 class="panel-title">Mening e'lonlarim</h3>
                            <p class="panel-subtitle">Yaratilgan postlar ro'yxati</p>
                        </div>
                        <a href="{{ route('posts.index') }}" class="btn-panel-link small-link">
                            <i class="bi bi-plus-lg me-1"></i> Hammasi
                        </a>
                    </div>

                    <div class="profile-list">
                        @forelse($recentPosts as $post)
                            <div class="profile-list-item {{ !$loop->last ? 'has-border' : '' }}">
                                <div class="d-flex align-items-center gap-3 flex-grow-1">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="liked-post-image">
                                    @else
                                        <div class="liked-post-placeholder">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="profile-post-title mb-1">
                                            <a href="{{ route('posts.show', $post) }}" class="text-decoration-none text-cream hover-lime">
                                                {{ $post->title }}
                                            </a>
                                        </h6>
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar3 me-1"></i> {{ $post->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('posts.show', $post) }}" class="profile-action-btn detail-btn" title="Ko'rish">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <h3 class="empty-title">Hozircha e'lon yo'q</h3>
                                <p class="empty-text">Yangi hayvon yoki mahsulot e'lonini qo'shish uchun "Yangi e'lon" tugmasini bosing.</p>
                                <a href="{{ route('posts.index') }}" class="btn-filter-apply d-inline-flex mt-3 text-decoration-none px-4">
                                    <i class="bi bi-plus-circle me-1"></i> Yangi e'lon yaratish
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="panel-box mb-4">
                    <div class="panel-header compact-header">
                        <div>
                            <h3 class="panel-title">Profil ma'lumotlari</h3>
                            <p class="panel-subtitle">Ism, telefon, telegram va avatar</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-3 mt-0">
                        @csrf
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small text-cream">To'liq ism</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control profile-input" required>
                        </div>

                        <div class="col-12">
                            <label for="phone" class="form-label fw-semibold small text-cream">Telefon</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="form-control profile-input" placeholder="+998 90 123 45 67">
                        </div>

                        <div class="col-12">
                            <label for="telegram_username" class="form-label fw-semibold small text-cream">Telegram username</label>
                            <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username', $user->telegram_username) }}" class="form-control profile-input" placeholder="username">
                        </div>

                        <div class="col-12">
                            <label for="avatar" class="form-label fw-semibold small text-cream">Profil rasmi</label>
                            <input type="file" name="avatar" id="avatar" class="form-control profile-input" accept="image/*">
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn-filter-apply w-100 text-decoration-none">
                                <i class="bi bi-floppy2 me-1"></i> Saqlash
                            </button>
                        </div>
                    </form>
                </div>

                <div class="panel-box">
                    <div class="panel-header compact-header">
                        <div>
                            <h3 class="panel-title">Xavfsizlik</h3>
                            <p class="panel-subtitle">Parolni yangilash</p>
                        </div>
                    </div>

                    <form action="{{ route('profile.password.update') }}" method="POST" class="row g-3 mt-0">
                        @csrf
                        <div class="col-12">
                            <label for="current_password" class="form-label fw-semibold small text-cream">Joriy parol</label>
                            <input type="password" name="current_password" id="current_password" class="form-control profile-input @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="password" class="form-label fw-semibold small text-cream">Yangi parol</label>
                            <input type="password" name="password" id="password" class="form-control profile-input @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="password_confirmation" class="form-label fw-semibold small text-cream">Yangi parolni tasdiqlang</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control profile-input" required>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn-panel-link w-100 justify-content-center text-decoration-none border-danger-subtle text-danger">
                                <i class="bi bi-shield-lock me-1"></i> Parolni yangilash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-lime { color: var(--lime) !important; }
    .text-muted { color: var(--muted) !important; }
    .bg-primary-soft { background: rgba(194, 240, 60, 0.12) !important; color: var(--lime) !important; }
    .bg-panel-soft { background: rgba(255,255,255,0.04) !important; color: var(--muted) !important; }
    .hover-lime:hover { color: var(--lime) !important; }
    .border-danger-subtle { border-color: rgba(255, 107, 43, 0.35) !important; }

    .profile-shell {
        display: block;
    }

    .profile-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        background: linear-gradient(135deg, rgba(12, 18, 13, 0.96), rgba(16, 29, 18, 0.96));
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 24px 24px 20px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .profile-header::before {
        content: "";
        position: absolute;
        inset: -40% auto auto 65%;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(194, 240, 60, 0.08);
        filter: blur(12px);
    }

    .profile-avatar-wrap {
        position: relative;
        z-index: 1;
        width: 110px;
        height: 110px;
        border-radius: 28px;
        border: 1px solid rgba(194, 240, 60, 0.35);
        background: rgba(194, 240, 60, 0.08);
        display: grid;
        place-items: center;
        overflow: hidden;
        box-shadow: 0 16px 30px rgba(0,0,0,0.2);
    }

    .profile-avatar-image,
    .profile-avatar-fallback {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 28px;
    }

    .profile-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--lime), #dff77c);
        color: var(--ink);
        font-size: 2.4rem;
        font-weight: 800;
    }

    .metric-card {
        min-height: 146px;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 18px 16px 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.12);
    }

    .metric-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.05rem;
    }

    .icon-mail { background: rgba(194, 240, 60, 0.12); color: var(--lime); }
    .icon-posts { background: rgba(94, 164, 255, 0.12); color: #8ab9ff; }
    .icon-phone { background: rgba(255, 107, 43, 0.12); color: var(--orange); }
    .icon-telegram { background: rgba(94, 164, 255, 0.12); color: #7bb8ff; }

    .metric-label {
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .metric-value {
        color: var(--cream);
        font-weight: 700;
        font-size: 0.98rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .metric-small {
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 600;
    }

    .panel-box {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 18px;
    }

    .compact-header {
        margin-bottom: 12px;
    }

    .panel-title {
        margin: 0;
        color: var(--cream);
        font-size: 1.05rem;
        font-weight: 700;
    }

    .panel-subtitle {
        margin: 4px 0 0;
        color: var(--muted);
        font-size: 0.74rem;
    }

    .small-link {
        padding: 7px 12px;
        font-size: 0.72rem;
    }

    .profile-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .profile-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 0;
    }

    .profile-list-item.has-border {
        border-bottom: 1px solid var(--line);
    }

    .liked-post-image {
        width: 62px;
        height: 62px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid var(--line);
        flex-shrink: 0;
    }

    .liked-post-placeholder {
        width: 62px;
        height: 62px;
        border-radius: 12px;
        background: rgba(194, 240, 60, 0.08);
        border: 1px solid var(--line);
        display: grid;
        place-items: center;
        color: var(--muted);
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .profile-post-title {
        font-size: 0.98rem;
        font-weight: 700;
        line-height: 1.3;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .profile-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid var(--line);
        background: transparent;
        color: var(--muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .profile-action-btn:hover {
        border-color: var(--lime);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.08);
    }

    .profile-input {
        width: 100%;
        border-radius: 10px;
        background: #0a130b;
        border: 1px solid var(--line);
        color: var(--cream);
        min-height: 42px;
        padding: 0.7rem 0.9rem;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .profile-input:focus {
        background: #0a130b;
        border-color: var(--lime);
        box-shadow: 0 0 0 3px rgba(194, 240, 60, 0.12);
        color: var(--cream);
    }

    .profile-input::placeholder {
        color: var(--muted);
    }

    .empty-state {
        padding: 46px 20px 30px;
        border: 1px dashed var(--line);
        border-radius: 16px;
        background: rgba(255,255,255,0.02);
        text-align: center;
    }

    .empty-icon {
        font-size: 2.1rem;
        color: var(--lime);
        margin-bottom: 10px;
    }

    .empty-title {
        margin: 0;
        font-size: 1.2rem;
        color: var(--cream);
        font-weight: 700;
    }

    .empty-text {
        margin: 8px 0 0;
        color: var(--muted);
        font-size: 0.85rem;
    }

    @media (max-width: 991.98px) {
        .profile-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .container {
            padding-left: 14px;
            padding-right: 14px;
        }

        .profile-header,
        .panel-box {
            padding-left: 16px;
            padding-right: 16px;
        }

        .profile-list-item {
            align-items: flex-start;
        }
    }
</style>
@endsection
