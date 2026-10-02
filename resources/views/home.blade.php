<!DOCTYPE html>
<html lang="uz" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZooMarket — uy hayvonlari bozori</title>
    <meta name="description" content="ZooMarket — O'zbekistondagi uy hayvonlari e'lonlari: itlar, mushuklar, qushlar va boshqalar.">
    @include('partials.head')
</head>
<body class="flex min-h-dvh flex-col text-[15px] leading-[1.45]">
@php
    $slides = [
        ['bg-navy', "Eng yaxshi e'lonlar onlayn", "Sodiq do'stlar.", $stats['posts'] > 0 ? "Barcha hayvonlar — <b>{$stats['posts']}</b> ta e'lon" : "Birinchi e'lonlar <b>tez orada</b>", route('posts.index'), "Ko'rish", '🐕'],
        ['bg-[#1f3a5f]', "Har kuni yangi e'lonlar", 'Mushuklar olami.', 'Narxlar — <b>kelishiladi</b>', route('posts.index', ['sort' => 'latest']), 'Yangilari', '🐈'],
    ];
    if (auth()->guest()) {
        $slides[] = ['bg-[#2d2350]', 'Sotuvchilar uchun', "Bepul e'lon bering.", '<b>' . e($stats['sellers']) . '</b> sotuvchi bizga ishonadi', route('register'), 'Boshlash', '🦜'];
    }
    // Horizontal product rail: 2.2 cards on phones up to 5 on wide screens.
    $rail = 'no-scrollbar grid snap-x snap-mandatory auto-cols-[calc((100%-14px)/2.2)] grid-flow-col gap-3.5 overflow-x-auto px-0.5 pt-0.5 pb-2.5 *:snap-start md:auto-cols-[calc((100%-36px)/3)] md:gap-[18px] lg:auto-cols-[calc((100%-54px)/4)] min-[1200px]:auto-cols-[calc((100%-72px)/5)]';
@endphp

@include('partials.site-header')

<main class="wrap pb-16">

    {{-- ============ Hero ============ --}}
    <section class="relative mt-2" id="hero">
        <div class="overflow-hidden rounded-[20px]">
            <div class="flex transition-transform duration-600 ease-[cubic-bezier(.2,.7,.2,1)]" data-hero-track>
                @foreach($slides as [$bg, $eyebrow, $heading, $offer, $href, $cta, $art])
                    <div class="{{ $bg }} relative grid min-h-[280px] min-w-full grid-cols-1 items-center overflow-hidden px-6 py-8 text-white md:min-h-[316px] md:grid-cols-[1.1fr_1fr] md:px-[70px] md:py-9 xl:px-[90px]">
                        <span class="absolute top-1/2 right-[12%] hidden size-[330px] -translate-y-1/2 rounded-full border-[60px] border-white/6 md:block" aria-hidden="true"></span>
                        <div class="relative">
                            <p class="mb-3 text-[17px] font-semibold">{{ $eyebrow }}</p>
                            <h2 class="mb-3.5 text-[clamp(34px,4.4vw,52px)] leading-[1.05] font-black tracking-[.01em] uppercase">{{ $heading }}</h2>
                            <p class="mb-[22px] text-xl font-bold [&_b]:text-[#ffd12e]">{!! $offer !!}</p>
                            <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-[22px] py-[11px] text-sm font-extrabold text-navy hover:bg-brand hover:text-white">{{ $cta }} <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="absolute right-3 bottom-2 text-[110px] leading-none opacity-45 drop-shadow-[0_18px_30px_rgba(0,0,0,.35)] md:relative md:right-auto md:bottom-auto md:justify-self-center md:text-[150px] md:opacity-100 lg:text-[200px]">{{ $art }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @foreach([-1 => ['bi-chevron-left', 'Oldingi', 'left-2.5 min-[1200px]:-left-[22px]'], 1 => ['bi-chevron-right', 'Keyingi', 'right-2.5 min-[1200px]:-right-[22px]']] as $step => [$icon, $label, $side])
            <button type="button" data-hero-step="{{ $step }}" aria-label="{{ $label }}" class="{{ $side }} absolute top-1/2 z-[2] hidden size-[54px] -translate-y-1/2 cursor-pointer place-items-center rounded-full bg-brand-soft text-xl text-brand shadow-[0_6px_18px_rgba(0,0,0,.12)] hover:bg-brand hover:text-white md:grid"><i class="bi {{ $icon }}"></i></button>
        @endforeach
        <div class="mt-4 flex justify-center gap-2" data-hero-dots></div>
    </section>

    {{-- ============ Latest listings ============ --}}
    <x-home-section :href="route('posts.index', ['sort' => 'latest'])" :rail="$latestPosts->count() > 5 ? 'railLatest' : null">
        <x-slot:title>Eng yangi <span>e'lonlar</span></x-slot:title>
        @if($latestPosts->isNotEmpty())
            <div class="{{ $rail }}" id="railLatest">
                @foreach($latestPosts as $post)
                    @include('partials.home-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-[#d9d9d9] px-5 py-11 text-center font-semibold text-faint"><span class="mb-1.5 block text-[50px]">🐾</span>Hozircha e'lonlar yo'q. Tez orada bu yerda yangi do'stlar paydo bo'ladi!</div>
        @endif
    </x-home-section>
</main>

@include('partials.site-footer')
</body>
</html>
