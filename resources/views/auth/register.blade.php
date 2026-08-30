@extends('layouts.app')

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-ambient-glow"></div>
    <div class="container py-5 position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card">
                    {{-- Header --}}
                    <div class="text-center mb-4">
                        <div class="auth-brand-badge mb-3">
                            <span class="auth-brand-mark">Z</span>
                            <span class="auth-brand-text">ZooMarket</span>
                        </div>
                        <h2 class="auth-title">Ro'yxatdan o'tish</h2>
                        <p class="auth-subtitle">Yangi hisob oching va sevimli hayvonlaringiz olamiga qo'shiling</p>
                    </div>

                    {{-- Validation Errors Alert --}}
                    @if($errors->any())
                        <div class="auth-alert-error mb-4">
                            <div class="d-flex align-items-center gap-2 mb-1 fw-bold">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>Iltimos, xatoliklarni to'g'rilang:</span>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('register.post') }}" method="POST" id="registerForm">
                        @csrf

                        {{-- Role Selection (Interactive Cards) --}}
                        <div class="mb-4">
                            <label class="form-label-custom mb-2">
                                <i class="bi bi-person-badge me-1"></i> Hisob turi (Rol)
                            </label>
                            <div class="role-selector-grid">
                                {{-- Seller Option --}}
                                <label class="role-option-card {{ old('role', 'seller') === 'seller' ? 'active' : '' }}" for="role_seller">
                                    <input type="radio" name="role" id="role_seller" value="seller" {{ old('role', 'seller') === 'seller' ? 'checked' : '' }} class="d-none role-radio">
                                    <div class="role-card-header">
                                        <div class="role-icon-box seller-icon">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                        <span class="role-check-mark"><i class="bi bi-check-circle-fill"></i></span>
                                    </div>
                                    <div class="role-title">Sotuvchi</div>
                                    <div class="role-desc">E'lon berish va hayvonlarni sotish</div>
                                </label>

                                {{-- User/Buyer Option --}}
                                <label class="role-option-card {{ old('role') === 'user' ? 'active' : '' }}" for="role_user">
                                    <input type="radio" name="role" id="role_user" value="user" {{ old('role') === 'user' ? 'checked' : '' }} class="d-none role-radio">
                                    <div class="role-card-header">
                                        <div class="role-icon-box user-icon">
                                            <i class="bi bi-bag-heart"></i>
                                        </div>
                                        <span class="role-check-mark"><i class="bi bi-check-circle-fill"></i></span>
                                    </div>
                                    <div class="role-title">Xaridor</div>
                                    <div class="role-desc">E'lonlarni ko'rish va sotib olish</div>
                                </label>
                            </div>
                        </div>

                        {{-- Full Name --}}
                        <div class="mb-3">
                            <label class="form-label-custom" for="name">
                                <i class="bi bi-person me-1"></i> To'liq ismingiz
                            </label>
                            <div class="auth-input-group">
                                <span class="input-icon"><i class="bi bi-person"></i></span>
                                <input id="name" type="text" name="name" class="auth-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Masalan: Azizbek Rahimov" required autocomplete="name" autofocus>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label-custom" for="email">
                                <i class="bi bi-envelope me-1"></i> Email manzilingiz
                            </label>
                            <div class="auth-input-group">
                                <span class="input-icon"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" name="email" class="auth-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="example@mail.com" required autocomplete="email">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="form-label-custom" for="password">
                                <i class="bi bi-lock me-1"></i> Maxfiy parol
                            </label>
                            <div class="auth-input-group">
                                <span class="input-icon"><i class="bi bi-lock"></i></span>
                                <input id="password" type="password" name="password" class="auth-input @error('password') is-invalid @enderror" placeholder="Kamida 8 ta belgi" required autocomplete="new-password">
                                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('password', this)" tabindex="-1" title="Parolni ko'rsatish/yashirish">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Password Confirmation --}}
                        <div class="mb-4">
                            <label class="form-label-custom" for="password_confirmation">
                                <i class="bi bi-shield-lock me-1"></i> Parolni tasdiqlang
                            </label>
                            <div class="auth-input-group">
                                <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="auth-input" placeholder="Parolni qayta kiriting" required autocomplete="new-password">
                                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('password_confirmation', this)" tabindex="-1" title="Parolni ko'rsatish/yashirish">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn-auth-submit">
                                <span>Ro'yxatdan o'tish</span>
                                <i class="bi bi-arrow-right-circle-fill"></i>
                            </button>
                        </div>
                    </form>

                    {{-- Login redirect footer --}}
                    <div class="auth-card-footer text-center">
                        <span class="text-muted">Allaqachon hisobingiz bormi?</span>
                        <a href="{{ route('login') }}" class="auth-link fw-bold ms-1">
                            Kirish <i class="bi bi-box-arrow-in-right"></i>
                        </a>
                    </div>
                </div>

                {{-- Reassurance badge --}}
                <div class="auth-security-badge text-center mt-3">
                    <i class="bi bi-shield-check me-1 text-lime"></i> Xavfsiz va shifrlangan ma'lumotlar tizimi
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Auth Page Styles (ZooMarket Dark Luxury Theme) */
    .auth-page-wrapper {
        min-height: calc(100vh - 56px);
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        background: var(--ink);
        padding: 20px 0;
    }

    .auth-ambient-glow {
        position: absolute;
        top: 20%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 550px;
        height: 550px;
        background: radial-gradient(circle, rgba(194, 240, 60, 0.08) 0%, rgba(6, 13, 7, 0) 70%);
        pointer-events: none;
        z-index: 1;
    }

    .auth-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 32px 28px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(12px);
    }

    @media (min-width: 576px) {
        .auth-card {
            padding: 40px 36px;
        }
    }

    .auth-brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--line);
        padding: 5px 14px;
        border-radius: 9999px;
    }

    .auth-brand-mark {
        display: inline-flex;
        width: 22px;
        height: 22px;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: var(--lime);
        color: var(--ink);
        font-weight: 800;
        font-size: 0.78rem;
        transform: rotate(-6deg);
    }

    .auth-brand-text {
        font-family: var(--serif);
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--cream);
        letter-spacing: -0.03em;
    }

    .auth-title {
        font-family: var(--serif);
        font-weight: 800;
        color: var(--cream);
        font-size: 1.65rem;
        letter-spacing: -0.04em;
        margin-bottom: 6px;
    }

    .auth-subtitle {
        color: var(--muted);
        font-size: 0.86rem;
        margin-bottom: 0;
        line-height: 1.45;
    }

    .auth-alert-error {
        background: rgba(255, 107, 43, 0.1);
        border: 1px solid rgba(255, 107, 43, 0.35);
        color: #ffaa80;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.83rem;
    }

    .form-label-custom {
        display: block;
        color: var(--cream);
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 6px;
    }

    /* Role Selector Cards */
    .role-selector-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .role-option-card {
        background: #081209;
        border: 1.5px solid var(--line);
        border-radius: 14px;
        padding: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        user-select: none;
    }

    .role-option-card:hover {
        border-color: rgba(194, 240, 60, 0.4);
        background: rgba(194, 240, 60, 0.03);
    }

    .role-option-card.active {
        border-color: var(--lime);
        background: rgba(194, 240, 60, 0.08);
        box-shadow: 0 0 0 1px var(--lime), 0 4px 16px rgba(194, 240, 60, 0.12);
    }

    .role-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .role-icon-box {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 1rem;
    }

    .seller-icon {
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
    }

    .user-icon {
        background: rgba(194, 240, 60, 0.15);
        color: var(--lime);
    }

    .role-check-mark {
        color: var(--line);
        font-size: 1.05rem;
        transition: color 0.2s;
    }

    .role-option-card.active .role-check-mark {
        color: var(--lime);
    }

    .role-title {
        font-family: var(--serif);
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--cream);
        margin-bottom: 2px;
    }

    .role-desc {
        font-size: 0.72rem;
        color: var(--muted);
        line-height: 1.35;
    }

    /* Auth Inputs */
    .auth-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .auth-input-group .input-icon {
        position: absolute;
        left: 14px;
        color: var(--muted);
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.2s;
    }

    .auth-input {
        width: 100%;
        height: 46px;
        padding: 0 14px 0 42px;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--cream);
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .auth-input:focus {
        border-color: var(--lime);
        box-shadow: 0 0 0 3px rgba(194, 240, 60, 0.15);
    }

    .auth-input-group:focus-within .input-icon {
        color: var(--lime);
    }

    .auth-input::placeholder {
        color: rgba(122, 154, 125, 0.6);
    }

    .auth-input.is-invalid {
        border-color: #ff6b2b;
    }

    .btn-toggle-pwd {
        position: absolute;
        right: 12px;
        background: transparent;
        border: 0;
        color: var(--muted);
        cursor: pointer;
        padding: 4px 6px;
        font-size: 0.95rem;
        transition: color 0.2s;
    }

    .btn-toggle-pwd:hover {
        color: var(--cream);
    }

    /* Submit Button */
    .btn-auth-submit {
        height: 48px;
        background: var(--lime);
        color: var(--ink);
        border: 0;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(194, 240, 60, 0.2);
    }

    .btn-auth-submit:hover {
        background: #d7ff62;
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(194, 240, 60, 0.35);
    }

    .btn-auth-submit:active {
        transform: translateY(0);
    }

    /* Footer and links */
    .auth-card-footer {
        padding-top: 18px;
        border-top: 1px solid var(--line);
        font-size: 0.84rem;
    }

    .auth-link {
        color: var(--lime);
        text-decoration: none;
        transition: all 0.2s;
    }

    .auth-link:hover {
        color: #d7ff62;
        text-decoration: underline;
    }

    .auth-security-badge {
        font-size: 0.76rem;
        color: var(--muted);
    }

    .text-lime {
        color: var(--lime) !important;
    }
</style>

<script>
    // Handle Interactive Role Card Selection
    document.addEventListener('DOMContentLoaded', function () {
        const roleCards = document.querySelectorAll('.role-option-card');

        roleCards.forEach(card => {
            card.addEventListener('click', function () {
                roleCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
    });

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endsection
