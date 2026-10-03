@extends('layouts.app')

@section('content')
<x-auth-card title="Xush kelibsiz!" subtitle="Tizimga kirish uchun hisob ma'lumotlaringizni kiriting" note="ZooMarket xavfsiz autentifikatsiya tizimi">
    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <x-auth-input id="login_email" label="Email manzili" icon="bi-envelope" type="email" name="email" :value="old('email')"
                      :aria-invalid="$errors->has('email') ? 'true' : false" placeholder="example@mail.com" required autocomplete="email" autofocus />

        <x-auth-input id="login_password" label="Parol" icon="bi-lock" :password="true" type="password" name="password"
                      :aria-invalid="$errors->has('password') ? 'true' : false" placeholder="••••••••" required autocomplete="current-password" />

        <label class="mb-6 inline-flex cursor-pointer items-center gap-2 text-[13px] text-muted select-none" for="remember">
            <input class="size-4 cursor-pointer accent-brand" type="checkbox" name="remember" id="remember">
            <span>Meni eslab qol</span>
        </label>

        <button type="submit" class="btn btn-primary mb-6 h-12 w-full rounded-xl text-[15px] font-bold shadow-[0_4px_14px_rgba(0,142,204,.2)] hover:-translate-y-px hover:shadow-[0_8px_22px_rgba(0,142,204,.35)]">
            <span>Kirish</span> <i class="bi bi-box-arrow-in-right"></i>
        </button>
    </form>

    <x-google-button :href="route('auth.google.redirect')" />

    <x-slot:footer>
        <span class="text-muted">Hisobingiz yo'qmi?</span>
        <a href="{{ route('register') }}" class="ml-1 font-bold text-brand hover:text-brand-dark hover:underline">Ro'yxatdan o'ting <i class="bi bi-arrow-right"></i></a>
    </x-slot:footer>
</x-auth-card>
@endsection
