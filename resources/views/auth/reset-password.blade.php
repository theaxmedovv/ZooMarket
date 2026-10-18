@extends('layouts.app')

@section('content')
<x-auth-card title="Yangi parol" subtitle="Kod tasdiqlandi. Endi hisobingiz uchun yangi parol o'rnating" note="Parol kamida 8 ta belgidan iborat bo'lsin">
    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        <x-auth-input id="new_password" label="Yangi parol" icon="bi-lock" :password="true" type="password" name="password"
                      :aria-invalid="$errors->has('password') ? 'true' : false" placeholder="Kamida 8 ta belgi" required autocomplete="new-password" autofocus />

        <x-auth-input id="new_password_confirmation" label="Parolni tasdiqlang" icon="bi-shield-lock" :password="true" type="password" name="password_confirmation"
                      placeholder="Parolni qayta kiriting" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary mt-2 mb-6 h-12 w-full rounded-xl text-[15px] font-bold shadow-[0_4px_14px_rgba(0,142,204,.2)] hover:-translate-y-px hover:shadow-[0_8px_22px_rgba(0,142,204,.35)]">
            <span>Parolni yangilash</span> <i class="bi bi-check-circle-fill"></i>
        </button>
    </form>

    <x-slot:footer>
        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center gap-1 font-bold text-brand hover:text-brand-dark hover:underline"><i class="bi bi-arrow-left"></i> Kirish sahifasiga qaytish</a>
    </x-slot:footer>
</x-auth-card>
@endsection
