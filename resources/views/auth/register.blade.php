@extends('layouts.app')

@section('content')
<x-auth-card title="Ro'yxatdan o'tish" subtitle="Bitta hisob bilan e'lon joylang va hayvonlarni sotib oling" note="Xavfsiz va shifrlangan ma'lumotlar tizimi">
    <form action="{{ route('register.post') }}" method="POST" id="registerForm">
        @csrf

        <x-auth-input id="name" label="To'liq ismingiz" icon="bi-person" type="text" name="name" :value="old('name')"
                      :aria-invalid="$errors->has('name') ? 'true' : false" placeholder="Masalan: Azizbek Rahimov" required autocomplete="name" autofocus />

        <x-auth-input id="email" label="Email manzilingiz" icon="bi-envelope" type="email" name="email" :value="old('email')"
                      :aria-invalid="$errors->has('email') ? 'true' : false" placeholder="example@mail.com" required autocomplete="email" />

        <x-auth-input id="password" label="Maxfiy parol" icon="bi-lock" :password="true" type="password" name="password"
                      :aria-invalid="$errors->has('password') ? 'true' : false" placeholder="Kamida 8 ta belgi" required autocomplete="new-password" />

        <x-auth-input id="password_confirmation" label="Parolni tasdiqlang" icon="bi-shield-lock" :password="true" type="password" name="password_confirmation"
                      placeholder="Parolni qayta kiriting" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary mt-2 mb-6 h-12 w-full rounded-xl text-[15px] font-bold shadow-[0_4px_14px_rgba(0,142,204,.2)] hover:-translate-y-px hover:shadow-[0_8px_22px_rgba(0,142,204,.35)]">
            <span>Ro'yxatdan o'tish</span> <i class="bi bi-arrow-right-circle-fill"></i>
        </button>
    </form>

    {{-- One account type: everyone can both post listings and buy --}}
    <x-google-button id="googleRegister" label="Google orqali ro'yxatdan o'tish" :href="route('auth.google.redirect')" />

    <x-slot:footer>
        <span class="text-muted">Allaqachon hisobingiz bormi?</span>
        <a href="{{ route('login') }}" class="ml-1 font-bold text-brand hover:text-brand-dark hover:underline">Kirish <i class="bi bi-box-arrow-in-right"></i></a>
    </x-slot:footer>
</x-auth-card>
@endsection
