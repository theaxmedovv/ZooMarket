{{-- Profile identity: avatar, name, email · role, one-line description. --}}
<div class="mb-5 flex items-center gap-5 rounded-[18px] border border-line bg-white p-4 sm:p-6">
    <div class="grid size-[76px] shrink-0 place-items-center overflow-hidden rounded-full border border-brand/35 bg-brand/8">
        @if($user->avatar)
            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="size-full object-cover">
        @else
            <div class="flex size-full items-center justify-center bg-linear-135 from-brand to-brand-dark text-[1.9rem] font-extrabold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        @endif
    </div>
    <div class="min-w-0">
        <h1 class="text-[26px] font-extrabold tracking-tight text-ink">{{ $user->name }}</h1>
        <p class="mt-1 text-sm font-semibold [overflow-wrap:anywhere] text-muted">{{ $user->email }} · {{ $role }}</p>
        <p class="mt-1.5 text-sm text-muted">{{ $description }}</p>
    </div>
</div>
