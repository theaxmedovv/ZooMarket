@extends('layouts.app')

@section('content')
@php
    $allImages = $post->allImages();
    $maleAvail = $post->availableMaleCount();
    $femaleAvail = $post->availableFemaleCount();
    $defaultGender = $maleAvail > 0 ? 'male' : ($femaleAvail > 0 ? 'female' : 'male');
    $canModerate = auth()->check() && ((int) auth()->id() === (int) $post->user_id || auth()->user()->hasRole('admin'));
    $isBuyer = auth()->check() && auth()->user()->hasRole('user');

    $panel = 'rounded-[20px] border border-line bg-white p-5 shadow-[0_8px_24px_rgba(0,0,0,.12)]';
    $sectionTitle = 'mb-4 flex items-center gap-2 text-[17px] font-bold text-ink [&_.bi]:text-brand';
    $glassPill = 'inline-flex items-center gap-1 rounded-full border px-3 py-[5px] text-xs font-bold shadow-[0_4px_14px_rgba(0,0,0,.12)] backdrop-blur-md';
    $chip = 'inline-flex items-center gap-1 rounded-lg border border-black/7 bg-black/4 px-[9px] py-[3px] text-xs text-muted';
    $stockCard = 'flex items-center gap-2.5 rounded-xl border border-line bg-surface px-3 py-2.5 transition data-[depleted]:border-[#fff1f0] data-[depleted]:bg-[#fff5f4] data-[depleted]:opacity-45';
    $stockIcon = 'grid size-8 shrink-0 place-items-center rounded-lg text-base';
    $subBtn = 'inline-flex h-[42px] cursor-pointer items-center justify-center gap-1.5 rounded-xl border text-[13px] font-semibold transition';
@endphp

<div class="page">

    {{-- Breadcrumb is the only "back" navigation; posting time is the only meta worth showing here. --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 text-sm">
        <nav aria-label="breadcrumb" class="min-w-0">
            <ol class="flex min-w-0 items-center [&>li+li]:before:px-1.5 [&>li+li]:before:text-muted [&>li+li]:before:content-['›']">
                <li class="whitespace-nowrap"><a href="{{ route('posts.index') }}" class="font-semibold text-brand">{{ auth()->user()?->hasRole('seller') ? "E'lonlarim" : "E'lonlar" }}</a></li>
                @if($post->category)
                    <li class="whitespace-nowrap"><a href="{{ route('posts.index', ['category_id' => $post->category_id]) }}" class="font-semibold text-brand">{{ $post->category->name }}</a></li>
                @endif
                <li class="min-w-0 truncate font-semibold text-ink" aria-current="page">{{ Str::limit($post->title, 40) }}</li>
            </ol>
        </nav>
        <span class="text-[13px] whitespace-nowrap text-muted"><i class="bi bi-clock mr-1"></i>{{ $post->created_at->diffForHumans() }}</span>
    </div>

    {{-- AI moderation status banner for the seller --}}
    @if($canModerate)
        @if($post->moderation_status === 'rejected')
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-danger/50 bg-danger/8 p-6 shadow-sm">
                <div class="grid size-12 shrink-0 place-items-center rounded-full bg-danger/25 text-danger"><i class="bi bi-shield-x text-2xl"></i></div>
                <div class="flex-1">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <h5 class="text-lg font-bold text-danger"><i class="bi bi-robot mr-1"></i> E'lon Groq AI moderatsiyasidan o'tmadi</h5>
                        <span class="rounded-md bg-danger px-2 py-1 text-xs font-bold text-white">Ommaga ko'rsatilmaydi</span>
                    </div>
                    <p class="mb-2 text-sm text-ink">
                        Ushbu e'lon yoki yuklangan fotosuratlar hayvonlar xavfsizligi, taqiqlangan turlar yoki sifat qoidalariga mos kelmadi.
                    </p>
                    <div class="mb-3 rounded-lg border-l-[3px] border-danger bg-black/5 p-3">
                        <strong class="mb-1 block text-sm text-danger"><i class="bi bi-info-circle-fill mr-1"></i> Rad etilish sababi:</strong>
                        <span class="text-ink">{{ $post->moderation_reason ?? 'Tavsif yoki rasm xavfsizlik talablariga mos kelmadi.' }}</span>
                    </div>
                    <a href="{{ route('posts.edit', $post) }}" class="inline-flex items-center gap-1 rounded-md border border-danger px-2.5 py-1 text-sm font-semibold text-danger hover:bg-danger hover:text-white">
                        <i class="bi bi-pencil-square"></i> E'lonni tahrirlash va qayta topshirish
                    </a>
                </div>
            </div>
        @elseif($post->moderation_status === 'pending')
            <div class="mb-6 flex items-center gap-3 rounded-2xl border border-amber-400/50 bg-amber-400/8 p-4 shadow-sm">
                <div class="grid size-11 shrink-0 place-items-center rounded-full bg-amber-400/25 text-amber-500"><i class="bi bi-robot text-xl"></i></div>
                <div class="flex-1">
                    <h6 class="font-bold text-amber-600"><i class="bi bi-shield-check mr-1"></i> E'lon AI moderatsiyasida</h6>
                    <small class="text-ink/70">Groq AI tomonidan xavfsizlik tekshiruvi amalga oshirilmoqda.</small>
                </div>
                <span class="rounded-md bg-amber-400 px-2 py-1 text-xs font-bold text-ink">Tekshirilmoqda</span>
            </div>
        @endif
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-12">

        {{-- Left column: gallery, description, trust --}}
        <div class="flex flex-col gap-6 lg:col-span-7">

            {{-- Gallery --}}
            <div class="overflow-hidden rounded-[20px] border border-line bg-white p-3.5 shadow-[0_10px_30px_rgba(0,0,0,.12)]">
                <div class="relative flex h-[280px] items-center justify-center overflow-hidden rounded-2xl bg-soft sm:h-[360px] lg:h-[460px]">
                    @if(!empty($allImages))
                        <div id="ambientBackdrop" class="pointer-events-none absolute -inset-3.5 scale-115 bg-cover bg-center opacity-90 blur-[24px] brightness-[.24] saturate-[1.4]" style="background-image: url('{{ route('images.show', ['path' => $allImages[0]]) }}');"></div>
                        <img id="mainImg" src="{{ route('images.show', ['path' => $allImages[0]]) }}" alt="{{ $post->title }}"
                             class="relative z-[2] max-h-full max-w-full object-contain transition-opacity duration-200">
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 text-sm text-muted">
                            <i class="bi bi-image text-[2.8rem] text-line"></i>
                            <span>Rasm yuklanmagan</span>
                        </div>
                    @endif

                    {{-- Status & category badges on the image --}}
                    <div class="pointer-events-none absolute top-3.5 right-3.5 left-3.5 z-[3] flex items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $glassPill }} border-black/15 bg-white/85 text-ink"><i class="bi bi-tag-fill text-brand"></i> {{ $post->category?->name ?? 'Hayvon' }}</span>
                            @if($canModerate)
                                @if($post->moderation_status === 'approved')
                                    <span class="{{ $glassPill }} border-green-600/50 bg-white/85 text-green-700"><i class="bi bi-shield-check"></i> Tasdiqlangan</span>
                                @elseif($post->moderation_status === 'rejected')
                                    <span class="{{ $glassPill }} border-danger/50 bg-white/85 text-danger"><i class="bi bi-shield-x"></i> Rad etilgan</span>
                                @else
                                    <span class="{{ $glassPill }} border-amber-400/50 bg-white/85 text-amber-600"><i class="bi bi-clock"></i> AI tekshiruvida</span>
                                @endif
                            @endif
                            @if($post->isSoldOut())
                                <span class="{{ $glassPill }} border-red-500/60 bg-red-500/25 text-danger"><i class="bi bi-x-circle-fill"></i> Sotilgan</span>
                            @elseif($post->status === 'reserved')
                                <span class="{{ $glassPill }} border-accent/50 bg-accent/20 text-accent"><i class="bi bi-hourglass-split"></i> Rezerv qilingan</span>
                            @else
                                <span class="{{ $glassPill }} border-brand/50 bg-brand/18 text-brand"><i class="bi bi-check-circle-fill"></i> Sotuvda faol</span>
                            @endif
                        </div>

                        @if(!empty($allImages))
                            <span class="{{ $glassPill }} shrink-0 border-black/15 bg-white/85 text-ink">
                                <i class="bi bi-camera-fill text-brand"></i>
                                <span id="galleryCounter">1 / {{ count($allImages) }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Thumbnails --}}
                @if(count($allImages) > 1)
                    <div class="mt-3 flex gap-2.5 overflow-x-auto pb-1 [scrollbar-width:thin]">
                        @foreach($allImages as $i => $img)
                            <button type="button" data-gallery-thumb="{{ $i + 1 }}" data-src="{{ route('images.show', ['path' => $img]) }}" @if($i === 0) aria-current="true" @endif
                                    class="size-16 shrink-0 cursor-pointer overflow-hidden rounded-xl border-2 border-line bg-surface transition hover:-translate-y-0.5 hover:border-brand/50 current:border-brand current:shadow-[0_0_14px_rgba(0,142,204,.35)] sm:size-[76px]">
                                <img src="{{ route('images.show', ['path' => $img]) }}" alt="Rasm {{ $i + 1 }}" class="block size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="{{ $panel }} p-[22px]">
                <div class="{{ $sectionTitle }}"><i class="bi bi-card-text"></i> E'lon tavsifi</div>
                @if($post->description ?? $post->content)
                    <div class="text-[15px] leading-relaxed text-ink/88">
                        {!! nl2br(e($post->description ?? $post->content)) !!}
                    </div>
                @else
                    <p class="text-muted italic">Sotuvchi ushbu e'lon uchun alohida tavsif qoldirmagan.</p>
                @endif
            </div>

            {{-- Safe marketplace notice --}}
            <div class="{{ $panel }} p-[22px]">
                <div class="{{ $sectionTitle }}"><i class="bi bi-shield-check"></i> Xavfsiz xarid qoidalari</div>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach([
                        ['bi-camera-video', "Jonli ko'rik", "Xarid qilishdan avval videochat orqali hayvonning sog'lig'i va holatini ko'ring."],
                        ['bi-chat-heart', "To'g'ridan-to'g'ri aloqa", "Vositachilarsiz bevosita sotuvchi bilan chat yoki telefon orqali bog'laning."],
                        ['bi-patch-check', "Xavfsiz to'lov", "To'lovni hayvonni o'z ko'zingiz bilan ko'rib, qabul qilganingizdan so'ng to'lang."],
                    ] as [$icon, $heading, $text])
                        <div class="h-full rounded-[14px] border border-black/5 bg-black/[.03] p-4">
                            <div class="mb-2.5 grid size-9 place-items-center rounded-[10px] bg-brand/12 text-lg text-brand"><i class="bi {{ $icon }}"></i></div>
                            <h6 class="mb-1.5 text-sm font-bold text-ink">{{ $heading }}</h6>
                            <p class="text-xs leading-snug text-muted">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right column: sticky purchase & seller panel --}}
        <div class="lg:sticky lg:top-[92px] lg:col-span-5">
            <div class="flex flex-col gap-3">

                {{-- Price & title --}}
                <div class="{{ $panel }}">
                    <div class="mb-3.5 border-b border-line pb-3.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-[1.65rem] leading-tight font-extrabold tracking-tight text-brand lg:text-[1.95rem]">
                                {{ number_format((float) $post->price, 0, '.', ' ') }}
                                <small class="text-[0.95rem] font-semibold text-ink/75">{{ $post->currency }}</small>
                            </div>
                            @if($post->is_negotiable)
                                <span class="rounded-full border border-brand/35 bg-brand/12 px-2.5 py-[3px] text-xs font-bold text-brand"><i class="bi bi-check2-circle mr-1"></i> Kelishiladi</span>
                            @endif
                        </div>
                        <div class="text-sm text-muted">Bir dona hayvon uchun ko'rsatilgan narx</div>
                    </div>

                    <h1 class="text-[1.35rem] leading-snug font-bold text-ink">{{ $post->title }}</h1>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @if($post->breed)
                            <span class="{{ $chip }}"><i class="bi bi-award text-brand"></i> {{ $post->breed }}</span>
                        @endif
                        @if($post->location)
                            <span class="{{ $chip }}"><i class="bi bi-geo-alt text-accent"></i> {{ $post->location }}</span>
                        @endif
                    </div>
                </div>

                {{-- Stock & gender breakdown --}}
                <div class="{{ $panel }}">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="text-[13px] font-bold text-ink"><i class="bi bi-boxes mr-1 text-brand"></i> Zaxira va jins taqsimoti:</span>
                        <span @class(['rounded-full border px-2.5 py-[3px] text-xs font-bold', $post->isSoldOut() ? 'border-red-500/40 bg-red-500/15 text-danger' : 'border-brand/30 bg-brand/14 text-brand'])>
                            {{ $post->isSoldOut() ? 'Tugagan (Sold out)' : 'Jami: ' . $post->totalAvailableCount() . ' ta mavjud' }}
                        </span>
                    </div>

                    @php
                        $maleCard = ['bi-gender-male', 'bg-male/12 text-male', $maleAvail];
                        $femaleCard = ['bi-gender-female', 'bg-danger/12 text-danger', $femaleAvail];
                    @endphp
                    @if($post->gender === 'mixed' || ($post->male_quantity > 0 && $post->female_quantity > 0))
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([[$maleCard, 'Erkak ♂'], [$femaleCard, "Urg'ochi ♀"]] as [[$icon, $iconColors, $avail], $label])
                                <div class="{{ $stockCard }}" @if($avail <= 0) data-depleted @endif>
                                    <div class="{{ $stockIcon }} {{ $iconColors }}"><i class="bi {{ $icon }}"></i></div>
                                    <div>
                                        <div class="text-[13px] font-bold text-ink">{{ $label }}</div>
                                        <div @class(['text-xs font-semibold', $avail > 0 ? 'text-brand' : 'text-danger'])>{{ $avail > 0 ? $avail . ' ta mavjud' : 'Tugagan' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php [$icon, $iconColors, $avail] = ($maleAvail > 0 || $post->gender === 'male') ? $maleCard : $femaleCard; @endphp
                        <div class="{{ $stockCard }}" @if($avail <= 0) data-depleted @endif>
                            <div class="{{ $stockIcon }} {{ $iconColors }}"><i class="bi {{ $icon }}"></i></div>
                            <div class="flex flex-1 items-center justify-between">
                                <div class="text-[13px] font-bold text-ink">Jinsi: {{ $icon === 'bi-gender-male' ? 'Erkak ♂' : "Urg'ochi ♀" }}</div>
                                <div @class(['text-xs font-semibold', $avail > 0 ? 'text-brand' : 'text-danger'])>Mavjud: {{ $avail }} ta</div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Key specifications --}}
                <div class="{{ $panel }}">
                    <span class="mb-2 block text-[13px] font-bold text-ink"><i class="bi bi-ui-checks-grid mr-1 text-brand"></i> Asosiy xususiyatlar:</span>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach([
                            ['bi-tag', 'Kategoriya', $post->category?->name ?? '—'],
                            ['bi-award', 'Zot', $post->breed ?: '—'],
                            ['bi-gender-ambiguous', 'Jinsi', match ($post->gender) { 'mixed' => 'Aralash (Mixed)', 'female' => "Urg'ochi ♀", default => 'Erkak ♂' }],
                            ['bi-calendar3', 'Yoshi', $post->age ?: '—'],
                            ['bi-palette', 'Rangi', $post->color ?: '—'],
                            ['bi-geo-alt', 'Manzil', $post->location ?: '—'],
                        ] as [$icon, $label, $value])
                            <div class="flex flex-col gap-0.5 rounded-[10px] border border-line bg-surface px-3 py-[9px]">
                                <span class="text-[11px] font-semibold tracking-wide text-muted uppercase"><i class="bi {{ $icon }} mr-1"></i> {{ $label }}</span>
                                <span class="text-sm font-bold text-ink">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Seller --}}
                <div class="{{ $panel }}">
                    <div class="flex items-center gap-3">
                        <div class="grid size-11 shrink-0 place-items-center rounded-full border-[1.5px] border-brand/40 bg-brand-tint text-lg font-bold text-brand">
                            {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h6 class="text-[15px] font-bold text-ink">{{ $post->user->name }}</h6>
                                <span class="rounded-full border border-brand/30 bg-brand/12 px-1.5 py-px text-[11px] font-bold text-brand">Sotuvchi</span>
                            </div>
                            <div class="text-xs text-muted">Ro'yxatdan o'tgan: {{ $post->user->created_at->format('d.m.Y') }}</div>
                        </div>
                    </div>

                    @if($post->user->phone || $post->user->telegram_username)
                        <div class="mt-3 flex gap-2 border-t border-line pt-3">
                            @if($post->user->phone)
                                <a href="tel:{{ $post->user->phone }}" class="inline-flex h-[38px] flex-1 items-center justify-center gap-1.5 rounded-[10px] border border-brand/35 bg-brand/12 text-[13px] font-semibold text-brand transition hover:bg-brand hover:font-bold hover:text-white">
                                    <i class="bi bi-telephone-fill"></i><span>{{ $post->user->phone }}</span>
                                </a>
                            @endif
                            @if($post->user->telegram_username)
                                <a href="https://t.me/{{ ltrim($post->user->telegram_username, '@') }}" target="_blank" class="inline-flex h-[38px] flex-1 items-center justify-center gap-1.5 rounded-[10px] border border-male/35 bg-male/12 text-[13px] font-semibold text-male transition hover:bg-male hover:font-bold hover:text-white">
                                    <i class="bi bi-telegram"></i><span>Telegram</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-1 flex flex-col gap-2">
                    @if($isBuyer)
                        @if($post->isSoldOut() || $post->status === 'sold')
                            <button class="inline-flex h-[50px] w-full cursor-not-allowed items-center justify-center gap-2 rounded-[14px] border border-[#ffd6d1] bg-[#fff1f0] text-[15px] font-extrabold text-danger" disabled>
                                <i class="bi bi-x-circle-fill"></i> E'lon sotilgan (Mavjud emas)
                            </button>
                        @else
                            <button type="button" data-dialog-open="buyModalDetail" class="inline-flex h-[50px] w-full cursor-pointer items-center justify-center gap-2 rounded-[14px] bg-brand text-[15px] font-extrabold text-white shadow-[0_6px_20px_rgba(0,142,204,.28)] transition hover:-translate-y-0.5 hover:bg-brand-dark hover:shadow-[0_8px_26px_rgba(0,142,204,.4)]">
                                <i class="bi bi-cart-check-fill"></i> Sotib olish so'rovini yuborish
                            </button>
                        @endif

                        <div class="flex gap-2">
                            <form action="{{ route('posts.like', $post) }}" method="POST" class="flex-1" data-like-form>
                                @csrf
                                <button type="submit" aria-pressed="{{ $isLiked ? 'true' : 'false' }}" class="{{ $subBtn }} w-full border-line bg-white text-ink hover:border-accent hover:bg-accent/12 hover:text-accent aria-pressed:border-accent aria-pressed:bg-accent/12 aria-pressed:text-accent">
                                    <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                    <span data-like-label data-on="Yoqtirilgan" data-off="Yoqtirish">{{ $isLiked ? 'Yoqtirilgan' : 'Yoqtirish' }}</span>
                                    <span class="text-xs opacity-75">(<span data-like-count>{{ $post->liked_by_users_count }}</span>)</span>
                                </button>
                            </form>

                            @if($chat)
                                @php $unread = $chat->unreadCountFor(auth()->id()); @endphp
                                <a href="{{ route('chats.show', $chat) }}" class="{{ $subBtn }} border-brand/30 bg-brand/8 px-4 text-brand hover:bg-brand/18">
                                    <i class="bi bi-chat-dots-fill"></i>
                                    <span>Chat</span>
                                    @if($unread > 0)
                                        <span class="rounded-full bg-brand px-1.5 text-[11px] font-bold text-white">{{ $unread }}</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    @endif

                    @if(auth()->check() && (auth()->user()->can('edit posts') || auth()->id() === $post->user_id))
                        @if(! $post->isArchived())
                            <a href="{{ route('posts.edit', $post) }}" class="{{ $subBtn }} w-full border-line bg-black/4 text-muted hover:border-ink hover:text-ink">
                                <i class="bi bi-pencil-square"></i> E'lonni tahrirlash
                            </a>
                        @else
                            <div class="rounded-lg border border-line bg-black/4 p-2 text-center text-sm text-muted">
                                <i class="bi bi-lock-fill mr-1"></i> Ushbu e'lon arxivlangan / sotilgan (Read-only)
                            </div>
                        @endif
                    @endif

                    @guest
                        <div class="rounded-lg border border-dashed border-line bg-white/75 p-4 text-center">
                            <div class="mb-2 text-sm text-muted">Hayvonni sotib olish yoki sotuvchi bilan bog'lanish uchun tizimga kiring:</div>
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('login') }}" class="rounded-full border border-brand px-3 py-1 text-sm font-semibold text-brand hover:bg-brand hover:text-white">Kirish</a>
                                <a href="{{ route('register') }}" class="rounded-full bg-ok px-3 py-1 text-sm font-semibold text-white hover:brightness-95">Ro'yxatdan o'tish</a>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Buy dialog with gender & quantity selection --}}
@if($isBuyer && ! $post->isSoldOut())
    <dialog id="buyModalDetail" aria-labelledby="buyModalLabel"
            class="m-auto w-[min(500px,calc(100vw-32px))] rounded-2xl border border-line bg-white p-0 text-ink shadow-[0_20px_60px_rgba(0,0,0,.12)] backdrop:bg-black/50">
        <div class="flex items-center justify-between px-6 pt-5">
            <h5 class="text-lg font-bold text-ink" id="buyModalLabel"><i class="bi bi-cart-check-fill mr-2 text-brand"></i> Sotib olish so'rovi</h5>
            <button type="button" data-dialog-close aria-label="Yopish" class="grid size-8 cursor-pointer place-items-center rounded-lg text-muted hover:bg-soft hover:text-ink"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('purchase-requests.store') }}" method="POST" id="buyerPurchaseForm">
            @csrf
            <input type="hidden" name="animal_id" value="{{ $post->id }}">
            <div class="flex flex-col gap-4 p-6">

                {{-- Post summary --}}
                <div class="rounded-xl border border-line bg-surface px-3.5 py-3">
                    <div class="font-bold text-ink">{{ $post->title }}</div>
                    <div class="text-sm text-muted">{{ $post->breed }} &bull; {{ $post->location }}</div>
                    <div class="mt-1 font-bold text-brand">
                        {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                        <span class="text-sm font-normal text-muted">/ 1 ta uchun</span>
                    </div>
                </div>

                {{-- Step 1: gender --}}
                <div>
                    <span class="mb-2 block text-sm font-bold text-ink">1. Hayvon jinsini tanlang:</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([['male', 'buyerGenderMale', $maleAvail, 'bi-gender-male text-male', 'Erkak'], ['female', 'buyerGenderFemale', $femaleAvail, 'bi-gender-female text-danger', "Urg'ochi"]] as [$value, $id, $avail, $icon, $label])
                            <label for="{{ $id }}" class="block cursor-pointer has-disabled:cursor-not-allowed has-disabled:opacity-40">
                                <input type="radio" name="gender" value="{{ $value }}" class="peer sr-only" id="{{ $id }}" data-available="{{ $avail }}"
                                       @checked($defaultGender === $value) @disabled($avail <= 0) required>
                                <div class="flex flex-col items-center gap-1 rounded-xl border-[1.5px] border-line bg-surface p-3 text-center transition peer-checked:border-brand peer-checked:bg-brand/12 peer-checked:shadow-[0_0_12px_rgba(0,142,204,.2)] peer-focus-visible:outline-2 peer-focus-visible:outline-brand">
                                    <i class="bi {{ $icon }} text-xl"></i>
                                    <span class="text-sm font-bold text-ink">{{ $label }}</span>
                                    <span @class(['text-xs font-semibold', $avail > 0 ? 'text-brand' : 'text-danger'])>{{ $avail > 0 ? $avail . ' ta mavjud' : 'Tugagan' }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Step 2: quantity --}}
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label for="buyerQuantityInput" class="text-sm font-bold text-ink">2. Miqdorni tanlang:</label>
                        <span class="text-sm text-muted" id="buyerAvailHint">
                            Mavjud: <strong class="text-brand" id="buyerMaxCount">{{ $defaultGender === 'male' ? $maleAvail : $femaleAvail }}</strong> ta
                        </span>
                    </div>
                    @php $stepBtn = 'flex h-full w-12 cursor-pointer items-center justify-center text-lg font-bold text-brand transition hover:bg-brand/15 disabled:cursor-not-allowed disabled:opacity-25'; @endphp
                    <div class="flex h-12 items-center overflow-hidden rounded-xl border-[1.5px] border-line bg-surface">
                        <button type="button" class="{{ $stepBtn }}" id="btnBuyerMinus" aria-label="Kamaytirish"><i class="bi bi-dash-lg"></i></button>
                        <input type="number" name="quantity" id="buyerQuantityInput"
                               class="h-full min-w-0 flex-1 bg-transparent text-center text-lg font-extrabold text-ink outline-none"
                               value="1" min="1" max="{{ $defaultGender === 'male' ? $maleAvail : $femaleAvail }}" required>
                        <button type="button" class="{{ $stepBtn }}" id="btnBuyerPlus" aria-label="Ko'paytirish"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                {{-- Total --}}
                <div class="rounded-lg border border-line bg-surface p-3">
                    <div class="mb-1 flex items-center justify-between">
                        <span class="text-sm text-muted">Jami to'lov:</span>
                        <span class="text-xl font-bold text-brand" id="buyerTotalPrice">
                            {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                        </span>
                    </div>
                    <div class="text-sm text-muted" id="buyerSummaryText">
                        1 ta &times; {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                    </div>
                </div>
            </div>
            <div class="flex gap-2 px-6 pb-6">
                <button type="button" data-dialog-close class="btn flex-1 rounded-full border-field bg-white text-muted hover:border-muted hover:text-ink">Bekor qilish</button>
                <button type="submit" class="btn btn-primary flex-1 rounded-full disabled:bg-slate-500 disabled:text-slate-300" id="btnSubmitOrder">
                    <i class="bi bi-send-check"></i> So'rov yuborish
                </button>
            </div>
        </form>
    </dialog>
@endif

<script>
// Gallery: thumbnails swap the main image, its blurred backdrop and the counter.
document.querySelectorAll('[data-gallery-thumb]').forEach(btn => btn.addEventListener('click', () => {
    const mainImg = document.getElementById('mainImg');
    const src = btn.dataset.src;
    mainImg.style.opacity = '0';
    setTimeout(() => { mainImg.src = src; mainImg.style.opacity = '1'; }, 120);
    document.getElementById('ambientBackdrop').style.backgroundImage = `url('${src}')`;
    document.getElementById('galleryCounter').textContent = `${btn.dataset.galleryThumb} / {{ max(1, count($allImages)) }}`;
    document.querySelectorAll('[data-gallery-thumb]').forEach(b => b.removeAttribute('aria-current'));
    btn.setAttribute('aria-current', 'true');
}));

document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('buyerQuantityInput');
    const btnMinus = document.getElementById('btnBuyerMinus');
    const btnPlus = document.getElementById('btnBuyerPlus');
    const buyerMaxCount = document.getElementById('buyerMaxCount');
    const buyerTotalPrice = document.getElementById('buyerTotalPrice');
    const buyerSummaryText = document.getElementById('buyerSummaryText');
    const btnSubmitOrder = document.getElementById('btnSubmitOrder');

    const maleRadio = document.getElementById('buyerGenderMale');
    const femaleRadio = document.getElementById('buyerGenderFemale');

    const unitPrice = {{ (float) $post->price }};
    const currency = "{{ $post->currency }}";

    function getSelectedAvailable() {
        if (maleRadio && maleRadio.checked) {
            return parseInt(maleRadio.getAttribute('data-available')) || 0;
        }
        if (femaleRadio && femaleRadio.checked) {
            return parseInt(femaleRadio.getAttribute('data-available')) || 0;
        }
        return 0;
    }

    function syncBuyerModal() {
        const available = getSelectedAvailable();
        if (buyerMaxCount) {
            buyerMaxCount.textContent = available;
        }

        if (available <= 0) {
            if (qtyInput) {
                qtyInput.value = 0;
                qtyInput.max = 0;
                qtyInput.disabled = true;
            }
            if (btnMinus) btnMinus.disabled = true;
            if (btnPlus) btnPlus.disabled = true;
            if (btnSubmitOrder) {
                btnSubmitOrder.disabled = true;
                btnSubmitOrder.textContent = "Tanlangan jins tugagan";
            }
            if (buyerTotalPrice) buyerTotalPrice.textContent = `0 ${currency}`;
            if (buyerSummaryText) buyerSummaryText.textContent = "0 ta xarid";
            return;
        }

        if (qtyInput) {
            qtyInput.disabled = false;
            qtyInput.max = available;
            let current = parseInt(qtyInput.value) || 1;
            if (current < 1) current = 1;
            if (current > available) current = available;
            qtyInput.value = current;

            if (btnMinus) btnMinus.disabled = current <= 1;
            if (btnPlus) btnPlus.disabled = current >= available;

            const total = current * unitPrice;
            const formattedTotal = Number(total).toLocaleString('ru-RU');
            const formattedUnit = Number(unitPrice).toLocaleString('ru-RU');

            if (buyerTotalPrice) buyerTotalPrice.textContent = `${formattedTotal} ${currency}`;
            if (buyerSummaryText) buyerSummaryText.textContent = `${current} ta × ${formattedUnit} ${currency}`;
        }

        if (btnSubmitOrder) {
            btnSubmitOrder.disabled = false;
            btnSubmitOrder.innerHTML = `<i class="bi bi-send-check"></i> So'rov yuborish`;
        }
    }

    if (maleRadio) maleRadio.addEventListener('change', syncBuyerModal);
    if (femaleRadio) femaleRadio.addEventListener('change', syncBuyerModal);

    if (btnMinus) {
        btnMinus.addEventListener('click', function () {
            let current = parseInt(qtyInput.value) || 1;
            if (current > 1) {
                qtyInput.value = current - 1;
                syncBuyerModal();
            }
        });
    }

    if (btnPlus) {
        btnPlus.addEventListener('click', function () {
            const available = getSelectedAvailable();
            let current = parseInt(qtyInput.value) || 1;
            if (current < available) {
                qtyInput.value = current + 1;
                syncBuyerModal();
            }
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('input', syncBuyerModal);
        qtyInput.addEventListener('change', syncBuyerModal);
    }

    syncBuyerModal();
});
</script>
@endsection
