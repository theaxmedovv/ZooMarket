@extends('layouts.app')

@section('content')
@php
    $allImages = $post->allImages();
    $maleAvail = $post->availableMaleCount();
    $femaleAvail = $post->availableFemaleCount();
    $defaultGender = $maleAvail > 0 ? 'male' : ($femaleAvail > 0 ? 'female' : 'male');
    $profile = $post->profile();
    $usesGender = $profile->usesGender();
    $canModerate = auth()->check() && ((int) auth()->id() === (int) $post->user_id || auth()->user()->hasRole('admin'));
    $isOwner = auth()->check() && (int) auth()->id() === (int) $post->user_id;
    // Any user can buy, just not from their own listing
    $isBuyer = auth()->check() && auth()->user()->hasRole('user') && ! $isOwner;
    $isAdmin = auth()->check() && auth()->user()->hasRole('admin');

    $panel = 'rounded-[20px] border border-line bg-white p-4  md:p-5';
    $sectionTitle = 'mb-4 flex items-center gap-2 text-[17px] font-bold text-ink [&_.bi]:text-brand';
    $glassPill = 'inline-flex items-center gap-1 rounded-full border px-3 py-[5px] text-xs font-bold  backdrop-blur-md';
    $chip = 'inline-flex items-center gap-1 rounded-lg border border-black/7 bg-black/4 px-[9px] py-[3px] text-xs text-muted';
    $stockCard = 'flex items-center gap-2.5 rounded-xl border border-line bg-surface px-3 py-2.5 transition data-[depleted]:border-[#fff1f0] data-[depleted]:bg-[#fff5f4] data-[depleted]:opacity-45';
    $stockIcon = 'grid size-8 shrink-0 place-items-center rounded-lg text-base';
    $subBtn = 'inline-flex h-11 cursor-pointer items-center justify-center gap-1.5 rounded-xl border text-[13px] font-semibold transition';
@endphp

<div class="page">

    {{-- Breadcrumb is the only "back" navigation; posting time is the only meta worth showing here. --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 text-sm">
        <nav aria-label="breadcrumb" class="min-w-0">
            <ol class="flex min-w-0 items-center [&>li+li]:before:px-1.5 [&>li+li]:before:text-muted [&>li+li]:before:content-['›']">
                <li class="whitespace-nowrap"><a href="{{ route('home') }}" class="inline-flex min-h-11 items-center font-semibold text-brand">Bosh sahifa</a></li>
                @if($post->category)
                    <li class="whitespace-nowrap"><a href="{{ route('home', ['category_id' => $post->category_id]) }}" class="inline-flex min-h-11 items-center font-semibold text-brand">{{ $post->category->name }}</a></li>
                @endif
                <li class="min-w-0 truncate font-semibold text-ink" aria-current="page">{{ Str::limit($post->title, 40) }}</li>
            </ol>
        </nav>
        <span class="text-[13px] whitespace-nowrap text-muted"><i class="bi bi-clock mr-1"></i>{{ $post->created_at->diffForHumans() }}</span>
    </div>

    {{-- Moderation: the main admin decides here; the seller sees the outcome and, if rejected, why --}}
    @if($isAdmin)
        <x-moderation-panel :post="$post" class="mb-6" />
    @elseif($canModerate)
        @if($post->moderation_status === 'rejected')
            {{-- Compact: the outcome, the reason, one way to fix it --}}
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-danger/30 bg-danger/5 px-4 py-3">
                <i class="bi bi-shield-x mt-0.5 text-lg text-danger"></i>
                <div class="min-w-0 flex-1 text-[13px] leading-snug">
                    <p class="font-bold text-danger">
                        {{ $post->moderated_by ? "E'lon rad etildi" : "E'lon avtomatik tekshiruvdan o'tmadi" }}
                        <span class="font-normal text-muted">· ommaga ko'rinmaydi</span>
                    </p>
                    <p class="mt-1 whitespace-pre-line text-ink/80">{{ $post->moderation_reason ?? 'Tavsif yoki rasm xavfsizlik talablariga mos kelmadi.' }}</p>
                </div>
                <a href="{{ route('posts.edit', $post) }}" class="inline-flex shrink-0 items-center gap-1 rounded-md border border-danger/40 px-2.5 py-1 text-xs font-semibold text-danger hover:bg-danger hover:text-white">
                    <i class="bi bi-pencil-square"></i> Tahrirlash
                </a>
            </div>
        @elseif($post->moderation_status === 'pending')
            {{-- A small toast that fades away on its own; right after posting, the redirect's own toast already says this --}}
            @unless(session('info'))
                <script>
                    addEventListener('DOMContentLoaded', () => window.zmToast(@json("E'lon tekshiruvda. Biroz vaqtdan keyin tasdiqlanadi va hammaga ko'rinadi."), 'warning'));
                </script>
            @endunless
        @endif
    @endif

    {{-- Below lg every block is a direct child of this grid and `order-*` sets the reading order:
         gallery, price + title, stock, specs, description, seller, actions, trust. From lg up the two wrappers become columns. --}}
    <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:gap-6">

        {{-- Left column: gallery, description, trust --}}
        <div class="contents lg:flex lg:min-w-0 lg:flex-col lg:gap-6">

            {{-- Gallery --}}
            <div class="order-1 min-w-0 overflow-hidden rounded-[20px] border border-line bg-white p-2 md:p-3.5 lg:order-none">
                <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl bg-soft lg:aspect-auto lg:h-[460px]">
                    @if(!empty($allImages))
                        <div id="ambientBackdrop" class="pointer-events-none absolute -inset-3.5 scale-115 bg-cover bg-center opacity-90 blur-[24px] brightness-[.24] saturate-[1.4]" style="background-image: url('{{ route('images.show', ['path' => $allImages[0]]) }}');"></div>
                        {{-- Slides: swipe on a phone, thumbnails scroll to one; tapping opens it full-screen --}}
                        <div id="gallerySlides" class="no-scrollbar relative z-[2] flex size-full snap-x snap-mandatory overflow-x-auto overscroll-x-contain">
                            @foreach($allImages as $i => $img)
                                <button type="button" data-gallery-zoom="{{ route('images.show', ['path' => $img]) }}" aria-label="Rasmni kattalashtirish ({{ $i + 1 }})"
                                        class="flex size-full shrink-0 cursor-zoom-in snap-center items-center justify-center">
                                    <img src="{{ route('images.show', ['path' => $img]) }}" alt="{{ $post->title }} — {{ $i + 1 }}" width="800" height="600" class="max-h-full max-w-full object-contain" @if($i > 0) loading="lazy" @endif>
                                </button>
                            @endforeach
                        </div>
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
                                    <span class="{{ $glassPill }} border-amber-400/50 bg-white/85 text-amber-600"><i class="bi bi-clock"></i> Tekshiruvda</span>
                                @endif
                            @endif
                            @if($post->status === 'reserved' && ! $post->isArchived())
                                <span class="{{ $glassPill }} border-accent/50 bg-accent/20 text-accent"><i class="bi bi-hourglass-split"></i> Rezerv qilingan</span>
                            @elseif($post->isSoldOut())
                                <span class="{{ $glassPill }} border-red-500/60 bg-red-500/25 text-danger"><i class="bi bi-x-circle-fill"></i> Sotilgan</span>
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
                    <div class="no-scrollbar mt-2 flex gap-2 overflow-x-auto pb-1 md:mt-3 md:gap-2.5">
                        @foreach($allImages as $i => $img)
                            <button type="button" data-gallery-thumb="{{ $i + 1 }}" data-src="{{ route('images.show', ['path' => $img]) }}" @if($i === 0) aria-current="true" @endif
                                    class="size-16 shrink-0 cursor-pointer overflow-hidden rounded-xl border-2 border-line bg-surface transition hover:-translate-y-0.5 hover:border-brand/50 current:border-brand sm:size-[76px]">
                                <img src="{{ route('images.show', ['path' => $img]) }}" alt="Rasm {{ $i + 1 }}" class="block size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="{{ $panel }} order-5 min-w-0 lg:order-none">
                <div class="{{ $sectionTitle }}"><i class="bi bi-card-text"></i> E'lon tavsifi</div>
                @if($post->description ?? $post->content)
                    <div class="text-[15px] leading-relaxed break-words text-ink/88 md:text-base">
                        {!! nl2br(e($post->description ?? $post->content)) !!}
                    </div>
                @else
                    <p class="text-muted italic">Sotuvchi ushbu e'lon uchun alohida tavsif qoldirmagan.</p>
                @endif
            </div>

            {{-- Safe marketplace notice --}}
            <div class="{{ $panel }} order-8 lg:order-none">
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
        <div class="contents lg:sticky lg:top-24 lg:block lg:min-w-0 lg:self-start">
            <div class="contents lg:flex lg:flex-col lg:gap-3">

                {{-- Price & title --}}
                <div class="{{ $panel }} order-2 min-w-0 lg:order-none">
                    <div class="mb-3.5 border-b border-line pb-3.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-2xl leading-tight font-extrabold tracking-tight text-brand lg:text-3xl">
                                {{ number_format((float) $post->price, 0, '.', ' ') }}
                                <small class="text-[0.95rem] font-semibold text-ink/75">{{ $post->currency }}</small>
                            </div>
                            @if($post->is_negotiable)
                                <span class="rounded-full border border-brand/35 bg-brand/12 px-2.5 py-[3px] text-xs font-bold text-brand"><i class="bi bi-check2-circle mr-1"></i> Kelishiladi</span>
                            @endif
                        </div>
                        <div class="text-sm text-muted">Bir dona hayvon uchun ko'rsatilgan narx</div>
                    </div>

                    <h1 class="text-2xl leading-snug font-bold break-words text-ink md:text-3xl">{{ $post->title }}</h1>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @if($post->breed)
                            <span class="{{ $chip }}"><i class="bi bi-award text-brand"></i> {{ $post->breed }}</span>
                        @endif
                        @if($post->location)
                            <span class="{{ $chip }} min-w-0 break-words"><i class="bi bi-geo-alt text-accent"></i> {{ $post->fullLocation() }}</span>
                        @endif
                    </div>
                </div>

                {{-- Stock & gender breakdown --}}
                <div class="{{ $panel }} order-3 lg:order-none">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="text-[13px] font-bold text-ink"><i class="bi bi-boxes mr-1 text-brand"></i> {{ $usesGender ? 'Zaxira va jins taqsimoti:' : 'Zaxira:' }}</span>
                        <span @class(['rounded-full border px-2.5 py-[3px] text-xs font-bold', $post->isSoldOut() ? 'border-red-500/40 bg-red-500/15 text-danger' : 'border-brand/30 bg-brand/14 text-brand'])>
                            {{ $post->isSoldOut() ? ($post->status === 'reserved' && ! $post->isArchived() ? 'Hammasi band qilingan' : 'Tugagan (Sold out)') : 'Jami: ' . $post->totalAvailableCount() . ' ta mavjud' }}
                        </span>
                    </div>

                    @php
                        $maleCard = ['bi-gender-male', 'bg-male/12 text-male', $maleAvail];
                        $femaleCard = ['bi-gender-female', 'bg-danger/12 text-danger', $femaleAvail];
                    @endphp
                    @if(! $usesGender)
                        <div class="{{ $stockCard }}" @if($maleAvail <= 0) data-depleted @endif>
                            <div class="{{ $stockIcon }} bg-brand/12 text-brand"><i class="bi bi-hash"></i></div>
                            <div class="flex flex-1 items-center justify-between">
                                <div class="text-[13px] font-bold text-ink">{{ $profile->forForm()['quantity_label'] }}</div>
                                <div @class(['text-xs font-semibold', $maleAvail > 0 ? 'text-brand' : 'text-danger'])>Mavjud: {{ $maleAvail }} ta</div>
                            </div>
                        </div>
                    @elseif($post->gender === 'mixed' || ($post->male_quantity > 0 && $post->female_quantity > 0))
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
                <div class="{{ $panel }} order-4 min-w-0 lg:order-none">
                    <span class="mb-2 block text-[13px] font-bold text-ink"><i class="bi bi-ui-checks-grid mr-1 text-brand"></i> Asosiy xususiyatlar:</span>
                    <div class="grid grid-cols-1 gap-2 min-[361px]:grid-cols-2">
                        @php
                            $specs = array_values(array_filter([
                                ['bi-tag', 'Kategoriya', $post->category?->name ?? '—'],
                                ['bi-award', $profile->breedLabel(), $post->breed ?: '—'],
                                $usesGender ? ['bi-gender-ambiguous', 'Jinsi', match ($post->gender) { 'mixed' => 'Aralash (Mixed)', 'female' => "Urg'ochi ♀", default => 'Erkak ♂' }] : null,
                                $profile->usesAge() ? ['bi-calendar3', $profile->forForm()['age_label'], $post->age ?: '—'] : null,
                                $profile->usesColor() ? ['bi-palette', 'Rangi', $post->color ?: '—'] : null,
                            ]));
                            foreach ($post->detailRows() as $row) {
                                $specs[] = ['bi-check2-circle', $row['label'], $row['value']];
                            }
                            $specs[] = ['bi-geo-alt', 'Manzil', $post->fullLocation() ?: '—'];
                        @endphp
                        @foreach($specs as [$icon, $label, $value])
                            <div class="flex min-w-0 flex-col gap-0.5 rounded-[10px] border border-line bg-surface px-3 py-2">
                                <span class="text-xs font-semibold tracking-wide text-muted uppercase"><i class="bi {{ $icon }} mr-1"></i> {{ $label }}</span>
                                <span class="text-sm font-bold break-words text-ink">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Seller --}}
                <div class="{{ $panel }} order-6 lg:order-none">
                    <div class="flex items-center gap-3">
                        <div class="grid size-11 shrink-0 place-items-center rounded-full border-[1.5px] border-brand/40 bg-brand-tint text-lg font-bold text-brand">
                            {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2">
                                <h6 class="min-w-0 text-[15px] font-bold break-words text-ink">{{ $post->user->name }}</h6>
                                <span class="rounded-full border border-brand/30 bg-brand/12 px-1.5 py-px text-[11px] font-bold text-brand">Sotuvchi</span>
                            </div>
                            <div class="text-xs text-muted">Ro'yxatdan o'tgan: {{ $post->user->created_at->format('d.m.Y') }}</div>
                        </div>
                    </div>

                    @if($post->user->phone || $post->user->telegram_username)
                        <div class="mt-3 flex flex-wrap gap-2 border-t border-line pt-3">
                            @if($post->user->phone)
                                <a href="tel:{{ $post->user->phone }}" class="inline-flex h-11 min-w-32 flex-1 items-center justify-center gap-1.5 rounded-[10px] border border-brand/35 bg-brand/12 text-[13px] font-semibold text-brand transition hover:bg-brand hover:font-bold hover:text-white">
                                    <i class="bi bi-telephone-fill"></i><span>{{ $post->user->phone }}</span>
                                </a>
                            @endif
                            @if($post->user->telegram_username)
                                <a href="https://t.me/{{ ltrim($post->user->telegram_username, '@') }}" target="_blank" class="inline-flex h-11 min-w-32 flex-1 items-center justify-center gap-1.5 rounded-[10px] border border-male/35 bg-male/12 text-[13px] font-semibold text-male transition hover:bg-male hover:font-bold hover:text-white">
                                    <i class="bi bi-telegram"></i><span>Telegram</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="order-7 mt-1 flex min-w-0 flex-col gap-2 lg:order-none">
                    @if($isBuyer)
                        @if($hasPurchaseRequest)
                            <a href="{{ route('user.purchase-requests.index') }}" class="inline-flex h-[50px] w-full items-center justify-center gap-2 rounded-[14px] border border-brand/40 bg-brand/10 text-[15px] font-extrabold text-brand transition hover:bg-brand/18">
                                <i class="bi bi-check-circle-fill"></i> So'rov yuborilgan — buyurtmalarim
                            </a>
                            <form action="{{ route('user.purchase-requests.cancel', $activeRequestId) }}" method="POST"
                                  onsubmit="return confirm('So\'rovni bekor qilasizmi? Tanlangan hayvonlar yana sotuvga qaytadi.')">
                                @csrf
                                <button type="submit" class="inline-flex h-[44px] w-full cursor-pointer items-center justify-center gap-2 rounded-[14px] border border-danger/35 bg-white text-[14px] font-bold text-danger transition hover:border-danger hover:bg-danger hover:text-white">
                                    <i class="bi bi-x-circle"></i> So'rovni bekor qilish
                                </button>
                            </form>
                        @elseif($post->isSoldOut() && $post->status === 'reserved' && ! $post->isArchived())
                            <button class="inline-flex h-[50px] w-full cursor-not-allowed items-center justify-center gap-2 rounded-[14px] border border-accent/40 bg-accent/10 text-[15px] font-extrabold text-accent" disabled>
                                <i class="bi bi-hourglass-split"></i> Barcha hayvonlar band qilingan
                            </button>
                        @elseif($post->isSoldOut() || $post->status === 'sold')
                            <button class="inline-flex h-[50px] w-full cursor-not-allowed items-center justify-center gap-2 rounded-[14px] border border-[#ffd6d1] bg-[#fff1f0] text-[15px] font-extrabold text-danger" disabled>
                                <i class="bi bi-x-circle-fill"></i> E'lon sotilgan (Mavjud emas)
                            </button>
                        @else
                            <button type="button" data-dialog-open="buyModalDetail" class="inline-flex h-[50px] w-full cursor-pointer items-center justify-center gap-2 rounded-[14px] bg-brand text-[15px] font-extrabold text-white transition hover:-translate-y-0.5 hover:bg-brand-dark ">
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

                    @if($isOwner)
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
                            <div class="flex flex-wrap justify-center gap-2">
                                <a href="{{ route('login', ['next' => route('posts.show', $post, false)]) }}" class="inline-flex min-h-11 items-center rounded-full border border-brand px-5 text-sm font-semibold text-brand hover:bg-brand hover:text-white">Kirish</a>
                                <a href="{{ route('register', ['next' => route('posts.show', $post, false)]) }}" class="inline-flex min-h-11 items-center rounded-full bg-brand px-5 text-sm font-semibold text-white hover:bg-brand-dark">Ro'yxatdan o'tish</a>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    {{-- Similar listings: same category, four across --}}
    @if($similar->isNotEmpty())
        <section class="mt-12" id="oxshash">
            <h2 class="mb-5 text-xl font-extrabold text-ink md:text-2xl">O'xshash e'lonlar</h2>
            <div class="grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 md:gap-4 lg:grid-cols-3 xl:grid-cols-4">
                @include('partials.post-cards', ['posts' => $similar])
            </div>
        </section>
    @endif
</div>


{{-- Phone-only bottom bar: price, call, and the one action that fits this visitor (replaces the tab bar on this page) --}}
<div class="fixed inset-x-0 bottom-0 z-30 flex items-center gap-2 border-t border-line bg-white px-4 pt-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))] md:hidden">
    <div class="min-w-0 flex-1 leading-tight">
        <div class="truncate text-lg font-extrabold text-brand">{{ number_format((float) $post->price, 0, '.', ' ') }} <small class="text-xs font-semibold text-ink/75">{{ $post->currency }}</small></div>
        <div class="truncate text-xs text-muted">{{ $post->is_negotiable ? 'Kelishiladi' : '1 ta uchun' }}</div>
    </div>
    @if($post->user->phone)
        <a href="tel:{{ $post->user->phone }}" aria-label="Sotuvchiga qo'ng'iroq qilish" class="inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-xl border border-brand/35 bg-brand/10 px-3 text-sm font-bold text-brand"><i class="bi bi-telephone-fill"></i><span class="max-[360px]:sr-only">Qo'ng'iroq</span></a>
    @endif
    @if($isBuyer && $chat)
        <a href="{{ route('chats.show', $chat) }}" class="inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-xl bg-brand px-4 text-sm font-bold text-white"><i class="bi bi-chat-dots-fill"></i> Xabar</a>
    @elseif($isBuyer && $hasPurchaseRequest)
        <a href="{{ route('user.purchase-requests.index') }}" class="inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-xl bg-brand px-4 text-sm font-bold text-white"><i class="bi bi-bag-check"></i> So'rovim</a>
    @elseif($isBuyer && ! $post->isSoldOut())
        <button type="button" data-dialog-open="buyModalDetail" class="inline-flex h-11 min-w-11 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-brand px-4 text-sm font-bold text-white"><i class="bi bi-cart-check-fill"></i> Sotib olish</button>
    @elseif($isOwner && ! $post->isArchived())
        <a href="{{ route('posts.edit', $post) }}" class="inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-xl bg-brand px-4 text-sm font-bold text-white"><i class="bi bi-pencil-square"></i> Tahrirlash</a>
    @elseif(! auth()->check())
        <a href="{{ route('login', ['next' => route('posts.show', $post, false)]) }}" class="inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-xl bg-brand px-4 text-sm font-bold text-white"><i class="bi bi-box-arrow-in-right"></i> Kirish</a>
    @endif
</div>

{{-- Full-screen photo --}}
<dialog id="galleryZoom" aria-label="Rasm" class="m-0 h-dvh max-h-none w-screen max-w-none bg-black/95 p-0 backdrop:bg-black">
    <button type="button" data-dialog-close aria-label="Yopish" class="absolute top-3 right-3 z-10 grid size-11 cursor-pointer place-items-center rounded-full bg-white/15 text-xl text-white hover:bg-white/30"><i class="bi bi-x-lg"></i></button>
    <img id="galleryZoomImg" src="" alt="" class="size-full object-contain">
</dialog>

{{-- Buy dialog: pick how many males and females to request --}}
@if($isBuyer && ! $hasPurchaseRequest && ! $post->isSoldOut())
    <dialog id="buyModalDetail" aria-labelledby="buyModalLabel"
            class="m-auto max-h-[90dvh] w-[min(500px,calc(100vw-2rem))] overflow-y-auto overscroll-contain rounded-2xl border border-line bg-white p-0 text-ink backdrop:bg-black/50 max-md:mt-auto max-md:mb-0 max-md:w-full max-md:max-w-full max-md:rounded-b-none">
        <div class="flex items-center justify-between px-4 pt-4 md:px-6 md:pt-5">
            <h5 class="text-lg font-bold text-ink" id="buyModalLabel"><i class="bi bi-cart-check-fill mr-2 text-brand"></i> Sotib olish so'rovi</h5>
            <button type="button" data-dialog-close aria-label="Yopish" class="grid size-11 cursor-pointer place-items-center rounded-lg text-muted hover:bg-soft hover:text-ink"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('purchase-requests.store') }}" method="POST" id="buyerPurchaseForm">
            @csrf
            <input type="hidden" name="animal_id" value="{{ $post->id }}">
            <div class="flex flex-col gap-4 p-4 md:p-6">

                {{-- Post summary --}}
                <div class="rounded-xl border border-line bg-surface px-3.5 py-3">
                    <div class="font-bold text-ink">{{ $post->title }}</div>
                    <div class="text-sm text-muted">{{ $post->breed }} &bull; {{ $post->fullLocation() }}</div>
                    <div class="mt-1 font-bold text-brand">
                        {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                        <span class="text-sm font-normal text-muted">/ 1 ta uchun</span>
                    </div>
                </div>

                {{-- Step 1: how many of each gender --}}
                <div>
                    <span class="mb-2 block text-sm font-bold text-ink">{{ $usesGender ? '1. Har bir jinsdan nechta kerakligini tanlang:' : '1. Nechta kerakligini tanlang:' }}</span>
                    @php $stepBtn = 'flex h-full w-11 cursor-pointer items-center justify-center text-lg font-bold text-brand transition hover:bg-brand/15 disabled:cursor-not-allowed disabled:opacity-25'; @endphp
                    <div class="flex flex-col gap-2">
                        @foreach($usesGender
                            ? [['male', $maleAvail, 'bi-gender-male text-male', 'Erkak ♂'], ['female', $femaleAvail, 'bi-gender-female text-danger', "Urg'ochi ♀"]]
                            : [['male', $maleAvail, 'bi-hash text-brand', $profile->forForm()['quantity_label']]] as [$value, $avail, $icon, $label])
                            <div @class(['flex items-center justify-between gap-3 rounded-xl border-[1.5px] border-line bg-surface px-3 py-2', 'opacity-40' => $avail <= 0])>
                                <label for="buyerQty_{{ $value }}" class="flex items-center gap-2">
                                    <i class="bi {{ $icon }} text-xl"></i>
                                    <span>
                                        <span class="block text-sm font-bold text-ink">{{ $label }}</span>
                                        <span @class(['block text-xs font-semibold', $avail > 0 ? 'text-brand' : 'text-danger'])>{{ $avail > 0 ? $avail . ' ta mavjud' : 'Tugagan' }}</span>
                                    </span>
                                </label>
                                <div class="flex h-11 items-center overflow-hidden rounded-lg border border-line bg-white">
                                    <button type="button" class="{{ $stepBtn }}" data-qty-step="-1" data-qty-target="buyerQty_{{ $value }}" aria-label="{{ $label }}: kamaytirish"><i class="bi bi-dash-lg"></i></button>
                                    <input type="number" name="{{ $value }}_quantity" id="buyerQty_{{ $value }}" data-qty-input
                                           class="h-full w-12 bg-transparent text-center text-base font-extrabold text-ink outline-none"
                                           value="{{ $defaultGender === $value && $avail > 0 ? 1 : 0 }}" min="0" max="{{ $avail }}" @disabled($avail <= 0)>
                                    <button type="button" class="{{ $stepBtn }}" data-qty-step="1" data-qty-target="buyerQty_{{ $value }}" aria-label="{{ $label }}: ko'paytirish"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('quantity')
                        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
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
            <div class="sticky bottom-0 flex gap-2 border-t border-line bg-white px-4 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom))] md:static md:border-0 md:px-6 md:pt-0 md:pb-6">
                <button type="button" data-dialog-close class="btn flex-1 rounded-full border-field bg-white text-muted hover:border-muted hover:text-ink">Bekor qilish</button>
                <button type="submit" class="btn btn-primary flex-1 rounded-full disabled:bg-slate-500 disabled:text-slate-300" id="btnSubmitOrder">
                    <i class="bi bi-send-check"></i> So'rov yuborish
                </button>
            </div>
        </form>
    </dialog>
@endif

<script>
// Gallery: swiping (or a thumbnail) moves between slides; counter, thumbnail and blurred backdrop follow.
const slides = document.getElementById('gallerySlides');
if (slides) {
    const thumbs = [...document.querySelectorAll('[data-gallery-thumb]')];
    const total = slides.children.length;
    let current = 0;
    const show = i => {
        current = i;
        document.getElementById('galleryCounter').textContent = `${i + 1} / ${total}`;
        thumbs.forEach((b, n) => n === i ? b.setAttribute('aria-current', 'true') : b.removeAttribute('aria-current'));
        const src = slides.children[i].dataset.galleryZoom;
        const backdrop = document.getElementById('ambientBackdrop');
        if (backdrop) backdrop.style.backgroundImage = `url('${src}')`;
        thumbs[i]?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
    };
    slides.addEventListener('scroll', () => {
        const i = Math.round(slides.scrollLeft / slides.clientWidth);
        if (i !== current && i >= 0 && i < total) show(i);
    }, { passive: true });
    thumbs.forEach((btn, i) => btn.addEventListener('click', () => slides.scrollTo({ left: i * slides.clientWidth, behavior: 'smooth' })));
    slides.addEventListener('click', e => {
        const slide = e.target.closest('[data-gallery-zoom]');
        if (!slide) return;
        document.getElementById('galleryZoomImg').src = slide.dataset.galleryZoom;
        document.getElementById('galleryZoom').showModal();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = Array.from(document.querySelectorAll('[data-qty-input]'));
    const buyerTotalPrice = document.getElementById('buyerTotalPrice');
    const buyerSummaryText = document.getElementById('buyerSummaryText');
    const btnSubmitOrder = document.getElementById('btnSubmitOrder');

    const unitPrice = {{ (float) $post->price }};
    const currency = "{{ $post->currency }}";

    function clamp(input) {
        const max = parseInt(input.max) || 0;
        let value = parseInt(input.value) || 0;
        value = Math.min(Math.max(value, 0), max);
        input.value = value;
        return value;
    }

    function syncBuyerModal() {
        let totalCount = 0;
        qtyInputs.forEach(input => {
            const value = input.disabled ? 0 : clamp(input);
            totalCount += value;
            document.querySelectorAll(`[data-qty-target="${input.id}"]`).forEach(btn => {
                const step = parseInt(btn.dataset.qtyStep);
                btn.disabled = input.disabled || (step < 0 ? value <= 0 : value >= (parseInt(input.max) || 0));
            });
        });

        const formattedUnit = Number(unitPrice).toLocaleString('ru-RU');
        if (buyerTotalPrice) buyerTotalPrice.textContent = `${Number(totalCount * unitPrice).toLocaleString('ru-RU')} ${currency}`;
        if (buyerSummaryText) buyerSummaryText.textContent = `${totalCount} ta × ${formattedUnit} ${currency}`;

        if (btnSubmitOrder) {
            btnSubmitOrder.disabled = totalCount < 1;
            btnSubmitOrder.innerHTML = totalCount < 1
                ? "Kamida 1 ta hayvon tanlang"
                : `<i class="bi bi-send-check"></i> So'rov yuborish`;
        }
    }

    document.querySelectorAll('[data-qty-step]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.qtyTarget);
        input.value = (parseInt(input.value) || 0) + parseInt(btn.dataset.qtyStep);
        syncBuyerModal();
    }));

    qtyInputs.forEach(input => {
        input.addEventListener('input', syncBuyerModal);
        input.addEventListener('change', syncBuyerModal);
    });

    syncBuyerModal();
});
</script>
@endsection
