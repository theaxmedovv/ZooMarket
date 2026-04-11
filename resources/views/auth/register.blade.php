@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-primary">Ro'yxatdan o'tish</h3>
                        <p class="text-muted small mb-0">Hisob yarating va rolingizni tanlang</p>
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

                    <form action="{{ route('register.post') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="name">Ism</label>
                            <input id="name" type="text" name="name" class="form-control form-control-lg bg-light border-0" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control form-control-lg bg-light border-0" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="password">Parol</label>
                            <input id="password" type="password" name="password" class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="password_confirmation">Parolni tasdiqlang</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="role">Rol</label>
                            <select id="role" name="role" class="form-select form-select-lg bg-light border-0" required>
                                <option value="">Rol tanlang</option>
                                <option value="seller" @selected(old('role') === 'seller')>Seller</option>
                                <option value="user" @selected(old('role') === 'user')>User</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3">Ro'yxatdan o'tish</button>
                        </div>
                    </form>

                    <p class="text-center text-muted mt-4 mb-0">
                        Hisobingiz bormi?
                        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Kirish</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
