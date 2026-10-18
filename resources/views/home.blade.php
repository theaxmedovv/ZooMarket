<!DOCTYPE html>
<html lang="uz" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faunara — jonivorlar olami</title>
    <meta name="description" content="Faunara — O'zbekistondagi uy hayvonlari e'lonlari: itlar, mushuklar, qushlar va boshqalar.">
    @include('partials.head')
</head>
<body class="flex min-h-dvh flex-col text-[15px] leading-normal max-md:pb-16 md:text-base">
@php
    // Two views of the one storefront page: search results ($results), or hero + "Eng arzon narxlarda" + "Mashhurlar".
    $isResults = isset($results);
    $grid = 'grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 md:gap-4 lg:grid-cols-3 xl:grid-cols-4';
    $moreButton = 'btn h-12 w-full rounded-xl border-brand bg-white px-6 text-brand hover:bg-brand-soft sm:w-auto';

    if (! $isResults) {
        // Slides: [background, eyebrow, heading, offer html, link, button, emoji, image url].
        // Only banners approved by the main admin; with none, the hero is left out.
        $slides = $banners->map(fn ($b) => [
            $b->color, $b->eyebrow, $b->title, $b->offerHtml(), $b->link,
            $b->link ? ($b->button_text ?: 'Batafsil') : null, $b->emoji, $b->imageUrl(), $b->isImageOnly(),
        ])->all();
    } else {
        $f = $filters;
        // Removable chips: [query keys to drop, label]
        $chips = array_values(array_filter([
            $f->search !== '' ? [['q'], 'Qidiruv: "' . $f->search . '"'] : null,
            $category ? [['category_id'], $category->name] : null,
            $f->breed !== '' ? [['breed'], 'Turi: ' . $f->breed] : null,
            $f->gender !== '' ? [['gender'], 'Jinsi: ' . ($f->gender === 'male' ? 'Erkak ♂' : "Urg'ochi ♀")] : null,
            $f->region !== '' ? [['region'], 'Manzil: ' . $f->region] : null,
            $f->location !== '' ? [['location'], 'Manzil: ' . $f->location] : null,
            $f->currency !== '' ? [['currency'], 'Valyuta: ' . $f->currency] : null,
            ($f->priceMin !== null || $f->priceMax !== null) ? [['price_min', 'price_max'], 'Narx: ' . ($f->priceMin ?? '0') . ' — ' . ($f->priceMax ?? '∞')] : null,
        ]));
        $chip = 'inline-flex min-h-11 items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium transition-colors';
    }
@endphp

@include('partials.site-header')

<main class="wrap pb-16">

@if($isResults)
    @include('partials.home-results')
@else
    {{-- ============ Hero (only when there are approved banners) ============ --}}
    @if($slides)
    <section class="relative mt-2" id="hero">
        {{-- md+: the banner keeps a fixed shape, height = 33.308824% of the width (the padding-bottom trick) --}}
        <div class="relative overflow-hidden rounded-[20px] md:pb-[33.308824%]">
            <div class="flex transition-transform duration-600 ease-[cubic-bezier(.2,.7,.2,1)] md:absolute md:inset-0" data-hero-track>
                @foreach($slides as [$bg, $eyebrow, $heading, $offer, $href, $cta, $art, $image, $imageOnly])
                    @if($imageOnly)
                        {{-- Just a picture: shown as it is, the whole banner is the link --}}
                        <div style="background-color: {{ $bg }}" class="relative aspect-[3/1] min-w-full overflow-hidden md:aspect-auto">
                            <img src="{{ $image }}" alt="" class="absolute inset-0 size-full object-cover" @unless($loop->first) loading="lazy" @endunless>
                            @if($href)
                                <a href="{{ $href }}" @if(str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif class="absolute inset-0" aria-label="Batafsil"></a>
                            @endif
                        </div>
                        @continue
                    @endif
                    <div style="background-color: {{ $bg }}" class="relative grid min-h-64 min-w-full grid-cols-1 items-center overflow-hidden px-5 py-6 text-white sm:min-h-72 sm:px-6 sm:py-8 md:grid-cols-[1.1fr_1fr] md:px-16 md:py-9 xl:px-24">
                        @if($image)
                            {{-- Photo banner: the picture fills the slide, a shade keeps the text readable --}}
                            <img src="{{ $image }}" alt="" class="absolute inset-0 size-full object-cover" @unless($loop->first) loading="lazy" @endunless>
                            <span class="absolute inset-0 bg-linear-to-r from-black/75 via-black/45 to-black/5" aria-hidden="true"></span>
                        @else
                            <span class="absolute top-1/2 right-[10%] hidden size-[330px] lg:size-[440px] -translate-y-1/2 rounded-full border-[60px] border-white/6 md:block" aria-hidden="true"></span>
                        @endif
                        <div class="relative">
                            @if($eyebrow)<p class="mb-3 text-[17px] font-semibold md:mb-4 md:text-xl">{{ $eyebrow }}</p>@endif
                            @if($heading)<h2 class="mb-3.5 text-[clamp(28px,5.2vw,68px)] leading-[1.05] font-black tracking-[.01em] break-words uppercase md:mb-5">{{ $heading }}</h2>@endif
                            @if($offer)<p class="mb-[22px] text-xl font-bold md:mb-7 md:text-2xl [&_b]:text-[#ffd12e]">{!! $offer !!}</p>@endif
                            @if($href)
                                <a href="{{ $href }}" @if(str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-white px-6 py-2.5 text-sm font-extrabold text-navy hover:bg-brand hover:text-white sm:w-auto md:px-7 md:py-3.5 md:text-base">{{ $cta }} <i class="bi bi-arrow-right"></i></a>
                            @endif
                        </div>
                        @if($art && ! $image)
                            <div class="absolute right-3 bottom-2 text-[110px] leading-none opacity-45 drop-shadow-[0_18px_30px_rgba(0,0,0,.35)] md:relative md:right-auto md:bottom-auto md:justify-self-center md:text-[170px] md:opacity-100 lg:text-[260px]">{{ $art }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @foreach([-1 => ['bi-chevron-left', 'Oldingi', 'left-2.5 min-[1200px]:-left-[22px]'], 1 => ['bi-chevron-right', 'Keyingi', 'right-2.5 min-[1200px]:-right-[22px]']] as $step => [$icon, $label, $side])
            <button type="button" data-hero-step="{{ $step }}" aria-label="{{ $label }}" class="{{ $side }} absolute top-1/2 z-[2] hidden size-[54px] -translate-y-1/2 cursor-pointer place-items-center rounded-full bg-brand-soft text-xl text-brand shadow-[0_6px_18px_rgba(0,0,0,.12)] hover:bg-brand hover:text-white md:grid"><i class="bi {{ $icon }}"></i></button>
        @endforeach
        <div class="mt-4 flex justify-center gap-2" data-hero-dots></div>
    </section>
    @endif

    {{-- ============ Popular: most liked first, 12 more per click ============ --}}
    <x-home-section id="mashhurlar">
        <x-slot:title><span>Mashhurlar</span></x-slot:title>
        @if($popular->isNotEmpty())
            <div class="{{ $grid }}" id="popularGrid">
                @include('partials.post-cards', ['posts' => $popular])
            </div>
            @if($moreUrl)
                <div class="mt-8 flex justify-center">
                    <button type="button" data-load-more="{{ $moreUrl }}" data-load-into="popularGrid" class="{{ $moreButton }}"><i class="bi bi-arrow-down-circle"></i> Yana 12 tasini ko'rish</button>
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-[#d9d9d9] px-5 py-11 text-center font-semibold text-faint"><span class="mb-1.5 block text-[50px]">🐾</span>Hozircha e'lonlar yo'q. Tez orada bu yerda yangi do'stlar paydo bo'ladi!</div>
        @endif
    </x-home-section>

    {{-- ============ Cheapest: the four lowest prices (in so'm) that can still be bought ============ --}}
    @if($cheapest->isNotEmpty())
        <x-home-section id="eng-arzon">
            <x-slot:title>Eng arzon <span>narxlarda</span></x-slot:title>
            <div class="{{ $grid }}">
                @include('partials.post-cards', ['posts' => $cheapest])
            </div>
        </x-home-section>
    @endif
@endif
</main>

@include('partials.site-footer')
</body>
</html>
