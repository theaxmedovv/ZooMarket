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
                        <h2 class="auth-title">Xush kelibsiz!</h2>
                        <p class="auth-subtitle">Tizimga kirish uchun hisob ma'lumotlaringizni kiriting</p>
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

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label-custom" for="login_email">
                                <i class="bi bi-envelope me-1"></i> Email manzili
                            </label>
                            <div class="auth-input-group">
                                <span class="input-icon"><i class="bi bi-envelope"></i></span>
                                <input id="login_email" type="email" name="email" value="{{ old('email') }}" class="auth-input @error('email') is-invalid @enderror" placeholder="example@mail.com" required autocomplete="email" autofocus>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="form-label-custom" for="login_password">
                                <i class="bi bi-lock me-1"></i> Parol
                            </label>
                            <div class="auth-input-group">
                                <span class="input-icon"><i class="bi bi-lock"></i></span>
                                <input id="login_password" type="password" name="password" class="auth-input @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('login_password', this)" tabindex="-1" title="Parolni ko'rsatish/yashirish">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Remember Me --}}
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <label class="auth-checkbox-label" for="remember">
                                <input class="auth-checkbox" type="checkbox" name="remember" id="remember">
                                <span>Meni eslab qol</span>
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn-auth-submit">
                                <span>Kirish</span>
                                <i class="bi bi-box-arrow-in-right"></i>
                            </button>
                        </div>
                    </form>

                    {{-- Register link footer --}}
                    <div class="auth-card-footer text-center">
                        <span class="text-muted">Hisobingiz yo'qmi?</span>
                        <a href="{{ route('register') }}" class="auth-link fw-bold ms-1">
                            Ro'yxatdan o'ting <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Test Credentials Box --}}
                    <div class="auth-test-credentials mt-4">
                        <div class="test-cred-header">
                            <i class="bi bi-info-circle-fill text-lime me-1"></i>
                            <span>Test hisoblari:</span>
                        </div>
                        <div class="test-cred-list">
                            <div class="test-cred-item">
                                <span class="badge-role seller">Seller</span>
                                <span class="test-cred-text">seller@example.com / password</span>
                            </div>
                            <div class="test-cred-item">
                                <span class="badge-role user">User</span>
                                <span class="test-cred-text">user@example.com / password</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Security badge --}}
                <div class="auth-security-badge text-center mt-3">
                    <i class="bi bi-shield-check me-1 text-lime"></i> ZooMarket xavfsiz autentifikatsiya tizimi
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
        top: 25%;
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

    /* Checkbox */
    .auth-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--muted);
        font-size: 0.82rem;
        cursor: pointer;
        user-select: none;
    }

    .auth-checkbox {
        accent-color: var(--lime);
        width: 16px;
        height: 16px;
        cursor: pointer;
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

    /* Test Credentials Box */
    .auth-test-credentials {
        background: rgba(255, 255, 255, 0.03);
        border: 1px dashed var(--line);
        border-radius: 12px;
        padding: 12px 14px;
    }

    .test-cred-header {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cream);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .test-cred-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .test-cred-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.76rem;
    }

    .badge-role {
        padding: 2px 7px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.7rem;
    }

    .badge-role.seller {
        background: rgba(255, 107, 43, 0.2);
        color: var(--orange);
        border: 1px solid rgba(255, 107, 43, 0.3);
    }

    .badge-role.user {
        background: rgba(194, 240, 60, 0.2);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.3);
    }

    .test-cred-text {
        color: var(--muted);
        font-family: monospace;
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
