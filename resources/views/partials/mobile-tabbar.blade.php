{{-- Phone-only bottom tab bar: Bosh sahifa | Qidirish | Yangi e'lon | Xabarlar | Profil.
     Hidden from md up, and on a listing page or an open chat, where the page brings its own price / call / message bar.
     Pages leave room for it with `max-md:pb-16` on <body>. Variables come from site-header.blade.php. --}}
@unless(request()->routeIs('posts.show', 'chats.show'))
    @php
        $zmTab = 'relative flex h-16 min-w-11 flex-1 cursor-pointer flex-col items-center justify-center gap-0.5 text-xs font-semibold text-muted hover:text-brand current:text-brand';
        $zmTabIcon = 'text-[22px] leading-none';
    @endphp
    <nav aria-label="Asosiy menyu" class="fixed inset-x-0 bottom-0 z-30 flex border-t border-line bg-white pb-[env(safe-area-inset-bottom)] md:hidden">
        <a href="{{ route('home') }}" @if(request()->routeIs('home') && ! request()->query()) aria-current="page" @endif class="{{ $zmTab }}">
            <i class="bi bi-house-door {{ $zmTabIcon }}"></i><span>Bosh sahifa</span>
        </a>
        <button type="button" data-focus-search class="{{ $zmTab }}">
            <i class="bi bi-search {{ $zmTabIcon }}"></i><span>Qidirish</span>
        </button>

        {{-- The one action that matters: a raised accent button in the middle --}}
        @if($zmIsAdmin)
            <a href="{{ route('moderation.index') }}" @if(request()->routeIs('moderation.*')) aria-current="page" @endif class="{{ $zmTab }}">
                <i class="bi bi-shield-check {{ $zmTabIcon }}"></i><span>Tekshiruv</span>
                @if($zmModeration > 0)<em class="{{ $zmBadge }} absolute top-2 left-1/2 ml-2">{{ $zmModeration > 99 ? '99+' : $zmModeration }}</em>@endif
            </a>
        @else
            <a href="{{ route('posts.create') }}" aria-label="Yangi e'lon" class="flex h-16 flex-1 flex-col items-center justify-center gap-0.5 text-xs font-semibold text-ink">
                <span class="-mt-5 grid size-14 place-items-center rounded-full bg-gold text-2xl text-night ring-4 ring-white"><i class="bi bi-plus-lg"></i></span>
                <span>E'lon</span>
            </a>
        @endif

        <a href="{{ auth()->check() ? route('chats.index') : route('login') }}" @if(request()->routeIs('chats.*')) aria-current="page" @endif class="{{ $zmTab }}">
            <i class="bi bi-chat-dots {{ $zmTabIcon }}"></i><span>Xabarlar</span>
            @if($zmUnread > 0)<em class="{{ $zmBadge }} absolute top-2 left-1/2 ml-2">{{ $zmUnread > 99 ? '99+' : $zmUnread }}</em>@endif
        </a>
        @auth
            <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="zmProfileMenu" class="{{ $zmTab }}">
                <i class="bi bi-person-circle {{ $zmTabIcon }}"></i><span>Profil</span>
                @if(! $zmIsAdmin && $zmPending > 0)<em class="{{ $zmBadge }} absolute top-2 left-1/2 ml-2">{{ $zmPending > 99 ? '99+' : $zmPending }}</em>@endif
            </button>
        @else
            <a href="{{ route('login', $zmNext) }}" @if(request()->routeIs('login')) aria-current="page" @endif class="{{ $zmTab }}">
                <i class="bi bi-person-circle {{ $zmTabIcon }}"></i><span>Kirish</span>
            </a>
        @endauth
    </nav>
@endunless
