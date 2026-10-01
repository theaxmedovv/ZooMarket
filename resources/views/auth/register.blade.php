@extends('layouts.app')

@section('content')
<x-auth-card title="Ro'yxatdan o'tish" subtitle="Yangi hisob oching va sevimli hayvonlaringiz olamiga qo'shiling" note="Xavfsiz va shifrlangan ma'lumotlar tizimi">
    <form action="{{ route('register.post') }}" method="POST" id="registerForm">
        @csrf

        {{-- Role selection --}}
        <fieldset class="mb-6">
            <legend class="mb-2 block text-[13px] font-semibold text-ink"><i class="bi bi-person-badge mr-1"></i> Hisob turi (Rol)</legend>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([
                    ['seller', 'bi-shop', 'bg-accent/15 text-accent', 'Sotuvchi', "E'lon berish va hayvonlarni sotish"],
                    ['user', 'bi-bag-heart', 'bg-brand/15 text-brand', 'Xaridor', "E'lonlarni ko'rish va sotib olish"],
                ] as [$role, $roleIcon, $iconColors, $roleTitle, $roleDesc])
                    <label for="role_{{ $role }}" class="group flex cursor-pointer flex-col rounded-[14px] border-[1.5px] border-line bg-surface p-3.5 transition select-none hover:border-brand/40 hover:bg-brand/3 has-checked:border-brand has-checked:bg-brand/8 has-checked:shadow-[0_0_0_1px_var(--color-brand),0_4px_16px_rgba(0,142,204,.12)] has-focus-visible:outline-2 has-focus-visible:outline-brand">
                        <input type="radio" name="role" id="role_{{ $role }}" value="{{ $role }}" class="sr-only" @checked(old('role', 'seller') === $role)>
                        <span class="mb-2.5 flex items-center justify-between">
                            <span class="grid size-[34px] place-items-center rounded-[9px] text-base {{ $iconColors }}"><i class="bi {{ $roleIcon }}"></i></span>
                            <i class="bi bi-check-circle-fill text-[17px] text-line transition-colors group-has-checked:text-brand"></i>
                        </span>
                        <span class="mb-0.5 text-sm font-bold text-ink">{{ $roleTitle }}</span>
                        <span class="text-xs leading-snug text-muted">{{ $roleDesc }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

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

    <x-slot:footer>
        <span class="text-muted">Allaqachon hisobingiz bormi?</span>
        <a href="{{ route('login') }}" class="ml-1 font-bold text-brand hover:text-brand-dark hover:underline">Kirish <i class="bi bi-box-arrow-in-right"></i></a>
    </x-slot:footer>
</x-auth-card>
@endsection
