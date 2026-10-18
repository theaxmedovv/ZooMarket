@extends('layouts.app')

@section('content')
<x-auth-card title="Parolni unutdingizmi?" subtitle="Emailingizni kiriting, biz unga 6 xonali tasdiqlash kodini yuboramiz" note="Kod faqat sizning emailingizga yuboriladi">
    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <x-auth-input id="forgot_email" label="Email manzili" icon="bi-envelope" type="email" name="email" :value="old('email')"
                      :aria-invalid="$errors->has('email') ? 'true' : false" placeholder="example@gmail.com" required autocomplete="email" autofocus />

        <button type="submit" class="btn btn-primary mb-6 h-12 w-full rounded-xl text-[15px] font-bold shadow-[0_4px_14px_rgba(0,142,204,.2)] hover:-translate-y-px hover:shadow-[0_8px_22px_rgba(0,142,204,.35)]">
            <span>Kod yuborish</span> <i class="bi bi-send"></i>
        </button>
    </form>

    <x-slot:footer>
        <a href="{{ route('login') }}" class="font-bold text-brand hover:text-brand-dark hover:underline"><i class="bi bi-arrow-left"></i> Kirish sahifasiga qaytish</a>
    </x-slot:footer>
</x-auth-card>
@endsection
