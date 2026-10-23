@extends('layouts.app')

@section('content')
<x-auth-card title="Kodni kiriting" subtitle="{{ $email }} manziliga 6 xonali kod yuborildi. Kod {{ $minutes }} daqiqa amal qiladi." note="Xat kelmasa, «Spam» papkasini ham tekshiring">
    <form action="{{ route('password.verify') }}" method="POST">
        @csrf

        <x-auth-input id="reset_code" label="Tasdiqlash kodi" icon="bi-shield-lock" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                      autocomplete="one-time-code" placeholder="000000" required autofocus
                      class="text-center text-xl font-bold tracking-[.5em]"
                      :aria-invalid="$errors->has('code') ? 'true' : false" />

        <button type="submit" class="btn btn-primary mb-4 h-12 w-full rounded-xl text-[15px] font-bold hover:-translate-y-px ">
            <span>Tasdiqlash</span> <i class="bi bi-check2-circle"></i>
        </button>
    </form>

    <form action="{{ route('password.resend') }}" method="POST" class="mb-6 text-center text-sm">
        @csrf
        <span class="text-muted">Kod kelmadimi?</span>
        <button type="submit" class="ml-1 inline-flex min-h-11 cursor-pointer items-center font-bold text-brand hover:text-brand-dark hover:underline">Qayta yuborish</button>
    </form>

    <x-slot:footer>
        <a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center gap-1 font-bold text-brand hover:text-brand-dark hover:underline"><i class="bi bi-arrow-left"></i> Boshqa email kiritish</a>
    </x-slot:footer>
</x-auth-card>
@endsection
