<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZooMarket — uy hayvonlari bozori</title>
    <meta name="description" content="ZooMarket — O'zbekistondagi uy hayvonlari e'lonlari: itlar, mushuklar, qushlar va boshqalar.">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @include('partials.site-styles')
    <style>
        :root { --ink: #222222; --muted: #9a9a9a; --line: #ededed; }
        html { scroll-behavior: smooth; }
        body { font-size: 15px; line-height: 1.45; }
        * { box-sizing: border-box; }
        :where(.wrap) a { color: inherit; text-decoration: none; }
        :where(.wrap) img { max-width: 100%; display: block; }
        .wrap { width: 100%; max-width: 1240px; margin: 0 auto; padding: 0 16px; }

        /* ---------- Hero ---------- */
        .hero { position: relative; margin-top: 8px; }
        .hero-track-wrap { border-radius: 20px; overflow: hidden; }
        .hero-track { display: flex; transition: transform .6s cubic-bezier(.2,.7,.2,1); }
        .slide { min-width: 100%; min-height: 316px; background: var(--navy); color: #fff; display: grid; grid-template-columns: 1.1fr 1fr; align-items: center; padding: 36px 90px; position: relative; overflow: hidden; }
        .slide::before { content: ''; position: absolute; right: 12%; top: 50%; width: 330px; height: 330px; transform: translateY(-50%); border-radius: 50%; border: 60px solid rgba(255,255,255,.06); }
        .slide-eyebrow { font-size: 17px; font-weight: 600; margin: 0 0 12px; }
        .slide h2 { font-size: clamp(34px, 4.4vw, 52px); font-weight: 900; line-height: 1.05; margin: 0 0 14px; letter-spacing: .01em; text-transform: uppercase; }
        .slide-off { font-size: 20px; font-weight: 700; margin: 0 0 22px; }
        .slide-off b { color: #ffd12e; }
        .slide-cta { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: var(--navy); padding: 11px 22px; border-radius: 8px; font-weight: 800; font-size: 14px; }
        .slide-cta:hover { background: var(--blue); color: #fff; }
        .slide-art { justify-self: center; font-size: 200px; line-height: 1; position: relative; filter: drop-shadow(0 18px 30px rgba(0,0,0,.35)); }
        .slide-2 { background: #1f3a5f; } .slide-3 { background: #2d2350; }
        .hero-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 54px; height: 54px; border-radius: 50%; border: 0; background: var(--blue-soft); color: var(--blue); font-size: 20px; cursor: pointer; display: grid; place-items: center; box-shadow: 0 6px 18px rgba(0,0,0,.12); z-index: 2; }
        .hero-nav:hover { background: var(--blue); color: #fff; }
        .hero-nav.prev { left: -22px; } .hero-nav.next { right: -22px; }
        .dots { display: flex; justify-content: center; gap: 8px; margin-top: 16px; }
        .dots button { width: 10px; height: 10px; padding: 0; border: 0; border-radius: 5px; background: #d9d9d9; cursor: pointer; transition: width .3s, background .3s; }
        .dots button.on { width: 30px; background: var(--blue); }

        /* ---------- Section heads ---------- */
        .section { margin-top: 48px; }
        .sec-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); margin-bottom: 24px; }
        .sec-head h2 { margin: 0; padding-bottom: 12px; font-size: 22px; font-weight: 700; color: var(--text); border-bottom: 3px solid var(--blue); margin-bottom: -2px; }
        .sec-head h2 span { color: var(--blue); }
        .sec-head-right { display: flex; align-items: center; gap: 14px; padding-bottom: 12px; }
        .view-all { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 700; color: var(--ink); white-space: nowrap; }
        .view-all i { color: var(--blue); }
        .view-all:hover { color: var(--blue); }
        .arrows { display: flex; gap: 6px; }
        .arrows button { width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--line); background: #fff; cursor: pointer; display: grid; place-items: center; color: var(--blue); }
        .arrows button:hover { background: var(--blue); color: #fff; border-color: var(--blue); }

        /* ---------- Product rail & card ---------- */
        .rail { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 4 * 18px) / 5); gap: 18px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding: 2px 2px 10px; }
        .rail::-webkit-scrollbar { display: none; }
        .rail > * { scroll-snap-align: start; }
        .p-card { border: 1px solid var(--line); border-radius: 16px; overflow: hidden; background: #fff; display: flex; flex-direction: column; transition: border-color .2s, box-shadow .2s, transform .2s; }
        .p-card:hover { border-color: var(--blue); box-shadow: 0 10px 26px rgba(0, 142, 204, .14); transform: translateY(-3px); }
        .p-card-media { position: relative; background: var(--soft); aspect-ratio: 1 / 0.92; }
        .p-card-img-link { display: grid; place-items: center; width: 100%; height: 100%; overflow: hidden; }
        .p-card-img-link img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .p-card:hover .p-card-img-link img { transform: scale(1.05); }
        .p-card-placeholder { font-size: 80px; }
        .p-card-badge { position: absolute; top: 0; right: 0; background: var(--blue); color: #fff; font-size: 11px; font-weight: 800; letter-spacing: .03em; padding: 10px 10px 8px 12px; border-radius: 0 0 0 14px; max-width: 60%; text-align: center; line-height: 1.1; }
        .p-card-fav { position: absolute; top: 10px; left: 10px; margin: 0; }
        .p-card-fav button, .p-card-fav .fav-btn { width: 34px; height: 34px; border-radius: 50%; border: 0; background: #fff; cursor: pointer; display: grid; place-items: center; font-size: 16px; color: var(--ink); box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .p-card-fav button:hover, .p-card-fav .fav-btn:hover, .p-card-fav button.is-liked { color: #ff4d2e; }
        .p-card-body { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .p-card-title { font-size: 15px; font-weight: 700; color: var(--text); line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px; }
        .p-card-title:hover { color: var(--blue); }
        .p-card-price { display: flex; flex-direction: column; gap: 2px; padding-bottom: 10px; border-bottom: 1px solid var(--line); margin-top: auto; }
        .p-card-price strong { font-size: 17px; font-weight: 800; color: var(--ink); }
        .p-card-sub { font-size: 13px; color: var(--muted); }
        .p-card-foot { color: var(--green); font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ---------- Circle categories ---------- */
        .circles { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 22px; }
        .circle { display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; font-weight: 700; color: var(--text); }
        .circle-img { width: 130px; height: 130px; max-width: 100%; aspect-ratio: 1; border-radius: 50%; background: var(--soft); display: grid; place-items: center; font-size: 62px; border: 2px solid transparent; transition: border-color .2s, background .2s, transform .2s; }
        .circle:hover .circle-img { border-color: var(--blue); background: var(--blue-soft); transform: translateY(-3px); }
        .circle:hover { color: var(--blue); }
        .circle small { display: block; font-weight: 600; color: var(--muted); font-size: 12px; margin-top: 2px; }

        /* ---------- Breeds strip (daily essentials) ---------- */
        .breeds { display: grid; grid-template-columns: repeat(6, 1fr); gap: 18px; }
        .breed { text-align: center; }
        .breed-img { aspect-ratio: 1; border-radius: 16px; background: var(--soft); border: 1px solid var(--line); overflow: hidden; display: grid; place-items: center; font-size: 60px; margin-bottom: 12px; transition: border-color .2s; }
        .breed-img img { width: 100%; height: 100%; object-fit: cover; }
        .breed:hover .breed-img { border-color: var(--blue); }
        .breed b { display: block; color: var(--text); font-size: 15px; }
        .breed span { display: block; font-weight: 800; margin-top: 2px; font-size: 14px; }

        .empty { border: 1px dashed #d9d9d9; border-radius: 16px; padding: 44px 20px; text-align: center; color: var(--muted); font-weight: 600; }
        .empty .emoji { font-size: 50px; display: block; margin-bottom: 6px; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1200px) {
            .rail { grid-auto-columns: calc((100% - 3 * 18px) / 4); }
            .hero-nav.prev { left: 10px; } .hero-nav.next { right: 10px; }
        }
        @media (max-width: 1024px) {
            .rail { grid-auto-columns: calc((100% - 2 * 18px) / 3); }
            .breeds { grid-template-columns: repeat(3, 1fr); }
            .slide { padding: 36px 70px; }
            .slide-art { font-size: 150px; }
        }
        @media (max-width: 768px) {
            .slide { grid-template-columns: 1fr; padding: 32px 24px; min-height: 280px; }
            .slide-art { position: absolute; right: 12px; bottom: 8px; font-size: 110px; opacity: .45; }
            .slide::before { display: none; }
            .hero-nav { display: none; }
            .rail { grid-auto-columns: calc((100% - 14px) / 2.2); gap: 14px; }
            .sec-head h2 { font-size: 18px; }
            .arrows { display: none; }
            .circle-img { width: 96px; height: 96px; font-size: 46px; }
            .circles { grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 16px; }
        }
        @media (max-width: 480px) {
            .breeds { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

@include('partials.site-header')

<main class="wrap">

    {{-- ============ Hero ============ --}}
    <section class="hero" id="hero">
        <div class="hero-track-wrap">
            <div class="hero-track">
                <div class="slide">
                    <div>
                        <p class="slide-eyebrow">Eng yaxshi e'lonlar onlayn</p>
                        <h2>Sodiq do'stlar.</h2>
                        <p class="slide-off">@if($stats['posts'] > 0)Barcha hayvonlar — <b>{{ $stats['posts'] }}</b> ta e'lon @else Birinchi e'lonlar <b>tez orada</b>@endif</p>
                        <a href="{{ route('posts.index') }}" class="slide-cta">Ko'rish <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="slide-art">🐕</div>
                </div>
                <div class="slide slide-2">
                    <div>
                        <p class="slide-eyebrow">Har kuni yangi e'lonlar</p>
                        <h2>Mushuklar olami.</h2>
                        <p class="slide-off">Narxlar — <b>kelishiladi</b></p>
                        <a href="{{ route('posts.index', ['sort' => 'latest']) }}" class="slide-cta">Yangilari <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="slide-art">🐈</div>
                </div>
                @guest
                <div class="slide slide-3">
                    <div>
                        <p class="slide-eyebrow">Sotuvchilar uchun</p>
                        <h2>Bepul e'lon bering.</h2>
                        <p class="slide-off"><b>{{ $stats['sellers'] }}</b> sotuvchi bizga ishonadi</p>
                        <a href="{{ route('register') }}" class="slide-cta">Boshlash <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="slide-art">🦜</div>
                </div>
                @endguest
            </div>
        </div>
        <button class="hero-nav prev" type="button" aria-label="Oldingi"><i class="bi bi-chevron-left"></i></button>
        <button class="hero-nav next" type="button" aria-label="Keyingi"><i class="bi bi-chevron-right"></i></button>
        <div class="dots"></div>
    </section>

    {{-- ============ Latest listings ============ --}}
    <section class="section">
        <div class="sec-head">
            <h2>Eng yangi <span>e'lonlar</span></h2>
            <div class="sec-head-right">
                @if($latestPosts->count() > 5)
                    <div class="arrows" data-rail="railLatest"><button type="button" data-dir="-1" aria-label="Chapga"><i class="bi bi-chevron-left"></i></button><button type="button" data-dir="1" aria-label="O'ngga"><i class="bi bi-chevron-right"></i></button></div>
                @endif
                <a href="{{ route('posts.index', ['sort' => 'latest']) }}" class="view-all">Hammasi <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        @if($latestPosts->isNotEmpty())
            <div class="rail" id="railLatest">
                @foreach($latestPosts as $post)
                    @include('partials.home-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
                @endforeach
            </div>
        @else
            <div class="empty"><span class="emoji">🐾</span>Hozircha e'lonlar yo'q. Tez orada bu yerda yangi do'stlar paydo bo'ladi!</div>
        @endif
    </section>

    {{-- ============ Top categories (circles) ============ --}}
    @if($categories->isNotEmpty())
        <section class="section">
            <div class="sec-head">
                <h2>Top <span>kategoriyalar</span></h2>
                <div class="sec-head-right"><a href="{{ route('posts.index') }}" class="view-all">Hammasi <i class="bi bi-chevron-right"></i></a></div>
            </div>
            <div class="circles">
                @foreach($categories as $category)
                    <a href="{{ route('posts.index', ['category_id' => $category->id]) }}" class="circle">
                        <span class="circle-img">{{ \App\Models\Category::emojiFor($category->name) }}</span>
                        <span>{{ $category->name }}<small>{{ $category->posts_count }} ta e'lon</small></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ Popular listings ============ --}}
    @if($popularPosts->isNotEmpty())
        <section class="section">
            <div class="sec-head">
                <h2>Eng <span>ommabop</span></h2>
                <div class="sec-head-right">
                    @if($popularPosts->count() > 5)
                        <div class="arrows" data-rail="railPopular"><button type="button" data-dir="-1" aria-label="Chapga"><i class="bi bi-chevron-left"></i></button><button type="button" data-dir="1" aria-label="O'ngga"><i class="bi bi-chevron-right"></i></button></div>
                    @endif
                    <a href="{{ route('posts.index') }}" class="view-all">Hammasi <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
            <div class="rail" id="railPopular">
                @foreach($popularPosts as $post)
                    @include('partials.home-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ Per-category rails ============ --}}
    @foreach($categorySections as $section)
        <section class="section">
            <div class="sec-head">
                <h2>Eng yaxshi takliflar: <span>{{ $section['category']->name }}</span></h2>
                <div class="sec-head-right">
                    @if($section['posts']->count() > 5)
                        <div class="arrows" data-rail="railCat{{ $section['category']->id }}"><button type="button" data-dir="-1" aria-label="Chapga"><i class="bi bi-chevron-left"></i></button><button type="button" data-dir="1" aria-label="O'ngga"><i class="bi bi-chevron-right"></i></button></div>
                    @endif
                    <a href="{{ route('posts.index', ['category_id' => $section['category']->id]) }}" class="view-all">Hammasi <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
            <div class="rail" id="railCat{{ $section['category']->id }}">
                @foreach($section['posts'] as $post)
                    @include('partials.home-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- ============ Popular breeds (daily essentials) ============ --}}
    @if($topBreeds->isNotEmpty())
        <section class="section">
            <div class="sec-head">
                <h2>Ommabop <span>zotlar</span></h2>
                <div class="sec-head-right"><a href="{{ route('posts.index') }}" class="view-all">Hammasi <i class="bi bi-chevron-right"></i></a></div>
            </div>
            <div class="breeds">
                @foreach($topBreeds as $breed)
                    <a href="{{ route('posts.index', ['q' => $breed['name']]) }}" class="breed">
                        <div class="breed-img">
                            @if($breed['sample']?->imageUrl())
                                <img src="{{ $breed['sample']->imageUrl() }}" alt="{{ $breed['name'] }}" loading="lazy">
                            @else
                                {{ \App\Models\Category::emojiFor($breed['sample']?->category?->name) }}
                            @endif
                        </div>
                        <b>{{ $breed['name'] }}</b>
                        <span>
                            @if($breed['min_price'])
                                {{ number_format($breed['min_price'], 0, '.', ' ') }} {{ $breed['currency'] }} dan
                            @else
                                {{ $breed['count'] }} ta e'lon
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

@include('partials.site-footer')

<script>
(function () {
    // Hero slider
    const hero = document.getElementById('hero');
    const track = hero.querySelector('.hero-track');
    const slides = track.children;
    const dotsBox = hero.querySelector('.dots');
    let index = 0, timer;
    for (let i = 0; i < slides.length; i++) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', (i + 1) + '-slayd');
        dot.addEventListener('click', () => go(i));
        dotsBox.appendChild(dot);
    }
    function go(i) {
        index = (i + slides.length) % slides.length;
        track.style.transform = 'translateX(' + (-index * 100) + '%)';
        [...dotsBox.children].forEach((d, n) => d.classList.toggle('on', n === index));
        clearInterval(timer);
        timer = setInterval(() => go(index + 1), 6000);
    }
    hero.querySelector('.prev').addEventListener('click', () => go(index - 1));
    hero.querySelector('.next').addEventListener('click', () => go(index + 1));
    let startX = null;
    track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', e => {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
        startX = null;
    });
    go(0);

    // Product rail arrows
    document.querySelectorAll('.arrows[data-rail]').forEach(box => {
        const rail = document.getElementById(box.dataset.rail);
        box.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
            rail.scrollBy({ left: Number(btn.dataset.dir) * rail.clientWidth * 0.8, behavior: 'smooth' });
        }));
    });
})();
</script>
</body>
</html>
