{{-- Profile details + password forms, shared by every account's profile page. --}}
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
            <div class="mb-2.5 flex items-center gap-3">
                <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-full border border-brand/35 bg-brand/8">
                    <img src="{{ $user->avatarUrl() }}" alt="" data-avatar-preview class="size-full object-cover @unless($user->avatar) hidden @endunless">
                    @unless($user->avatar)
                        <span data-avatar-initial class="text-xl font-extrabold text-brand">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    @endunless
                </div>
                <p class="text-xs leading-snug text-muted">JPG, PNG yoki WEBP, 2 MB gacha. Tanlagan rasmingiz saqlashdan oldin shu yerda ko'rinadi.</p>
            </div>
            <input type="file" name="avatar" id="avatar" data-avatar-input accept="image/*"
                   class="w-full cursor-pointer rounded-[10px] border border-line text-sm text-muted file:mr-3 file:cursor-pointer file:border-0 file:bg-brand-tint file:px-3 file:py-2.5 file:font-semibold file:text-brand hover:file:bg-brand/20">
        </div>
        <button type="submit" class="btn btn-primary mt-2 h-11 w-full rounded-lg text-[13px]">
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
        <button type="submit" class="btn mt-2 h-11 w-full rounded-lg border-accent/35 bg-white text-[13px] font-semibold text-danger hover:border-danger hover:bg-danger/5">
            <i class="bi bi-shield-lock"></i> Parolni yangilash
        </button>
    </form>
</x-panel>
