@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">

                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-primary">Xush Kelibsiz!</h3>
                        <p class="text-muted small">Tizimga kirish uchun ma'lumotlarni kiriting</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger border-0 small rounded-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email manzili</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg bg-light border-0" placeholder="example@mail.com" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Parol</label>
                            <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="••••••••" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Meni eslab qol</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm rounded-3">
                                Kirish
                            </button>
                        </div>
                    </form>

                    <p class="text-center text-muted mt-4 mb-0">
                        Hisobingiz yo'qmi?
                        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Ro'yxatdan o'ting</a>
                    </p>

                    <div class="mt-5 p-3 bg-light rounded-3 border-start border-primary border-4">
                        <p class="small fw-bold mb-2 text-secondary"><i class="bi bi-info-circle"></i> Test ma'lumotlari:</p>
                        <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                            <li><span class="badge bg-white text-dark border">Seller</span> seller@example.com / password</li>
                            <li><span class="badge bg-white text-dark border mt-1">User</span> user@example.com / password</li>
                        </ul>
                    </div>

                </div>
            </div>

            <p class="text-center text-muted mt-4 small">
                © {{ date('Y') }} Barcha huquqlar himoyalangan.
            </p>
        </div>
    </div>
</div>

<style>
    /* Bir oz estetika qo'shamiz */
    body {
        background-color: #f8f9fa;
    }
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        border-color: #0d6efd;
    }
    .card {
        transition: transform 0.3s ease;
    }
</style>
@endsection
