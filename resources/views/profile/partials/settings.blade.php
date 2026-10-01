{{-- Profile details + password forms, shared by the seller and buyer profile pages. --}}
@php $profileLabel = 'mb-[7px] block text-sm font-semibold text-ink'; @endphp
<x-panel class="mb-6" title="Profil ma'lumotlari" subtitle="Ism, telefon, telegram va avatar">
    <form action="{{ $updateRoute }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
        @csrf
        <div>
            <label for="name" class="{{ $profileLabel }}">{{ $nameLabel }}</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="input border-line" required>
        </div>
        <div>
            <label for="phone" class="{{ $profileLabel }}">Telefon</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="input border-line" placeholder="+998 90 123 45 67">
        </div>
        <div>
            <label for="telegram_username" class="{{ $profileLabel }}">Telegram username</label>
            <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username', $user->telegram_username) }}" class="input border-line" placeholder="username">
        </div>
        <div>
            <label for="avatar" class="{{ $profileLabel }}">Profil rasmi</label>
            <input type="file" name="avatar" id="avatar" accept="image/*"
                   class="w-full cursor-pointer rounded-[10px] border border-line text-sm text-muted file:mr-3 file:cursor-pointer file:border-0 file:bg-brand-tint file:px-3 file:py-2.5 file:font-semibold file:text-brand hover:file:bg-brand/20">
        </div>
        <button type="submit" class="btn btn-primary mt-2 h-[38px] w-full rounded-lg text-[13px]">
            <i class="bi bi-floppy2"></i> Saqlash
        </button>
    </form>
</x-panel>

<x-panel title="Xavfsizlik" subtitle="Parolni yangilash">
    <form action="{{ route('profile.password.update') }}" method="POST" class="flex flex-col gap-4">
        @csrf
        @foreach([['current_password', 'Joriy parol'], ['password', 'Yangi parol'], ['password_confirmation', 'Yangi parolni tasdiqlang']] as [$field, $fieldLabel])
            <div>
                <label for="{{ $field }}" class="{{ $profileLabel }}">{{ $fieldLabel }}</label>
                <input type="password" name="{{ $field }}" id="{{ $field }}" class="input border-line" @error($field) aria-invalid="true" @enderror required>
                @error($field)
                    <div class="mt-1 text-sm text-danger">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
        <button type="submit" class="btn mt-2 h-[38px] w-full rounded-lg border-accent/35 bg-white text-[13px] font-semibold text-danger hover:border-danger hover:bg-danger/5">
            <i class="bi bi-shield-lock"></i> Parolni yangilash
        </button>
    </form>
</x-panel>
