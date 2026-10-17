{{-- Home page in search mode: header search, filters or a category tab was used. Variables come from home.blade.php. --}}
<section class="mt-6 md:mt-8" id="natijalar">
    <x-page-head :title="$resultsTitle" :subtitle="$results->total() . ' ta e\'lon'">
        <x-slot:actions>
            <div class="relative">
                <button type="button" data-dropdown-toggle aria-expanded="false" aria-controls="sortMenu"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-1.5 text-[13px] font-semibold text-ink transition hover:border-brand hover:text-brand aria-expanded:border-brand aria-expanded:text-brand">
                    <i class="bi bi-arrow-down-up text-brand"></i>
                    <span>{{ $sorts[$filters->sort][1] }}</span>
                    <i class="bi bi-chevron-down text-[10px]"></i>
                </button>
                <div id="sortMenu" hidden class="{{ $menu }}">
                    @foreach($sorts as $key => [$icon, $label])
                        <a href="{{ route('home', array_merge(request()->query(), ['sort' => $key])) }}" @if($filters->sort === $key) aria-current="true" @endif class="{{ $menuItem }}"><i class="bi {{ $icon }}"></i>{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </x-slot:actions>
    </x-page-head>

    @if($chips)
        <div class="mb-4 flex flex-wrap items-center gap-1.5 rounded-xl border border-line bg-white/70 px-3 py-2">
            <span class="mr-1 inline-flex items-center gap-1 text-xs font-semibold text-muted"><i class="bi bi-funnel"></i> Faol:</span>
            @foreach($chips as [$keys, $label])
                <a class="{{ $chip }} border-brand/25 bg-brand/9 text-brand hover:border-accent/40 hover:bg-accent/15 hover:text-accent" href="{{ route('home', request()->except($keys)) }}" title="Olib tashlash">
                    {{ $label }} <i class="bi bi-x-circle-fill text-xs opacity-80"></i>
                </a>
            @endforeach
            <a class="{{ $chip }} border-line bg-black/5 text-muted hover:border-danger hover:bg-danger/15 hover:text-danger" href="{{ route('home') }}" title="Filtrlarni tozalash">
                Tozalash <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    @endif

    @if($results->isNotEmpty())
        <div class="{{ $grid }}" id="resultsGrid">
            @include('partials.post-cards', ['posts' => $results])
        </div>
        @if($moreUrl)
            <div class="mt-8 flex justify-center">
                <button type="button" data-load-more="{{ $moreUrl }}" data-load-into="resultsGrid" class="{{ $moreButton }}"><i class="bi bi-arrow-down-circle"></i> Yana 12 tasini ko'rish</button>
            </div>
        @endif
    @else
        <x-empty-state icon="bi-search" title="Hech qanday e'lon topilmadi" text="Boshqa so'z bilan qidirib ko'ring yoki filtrlarni tozalang.">
            <a href="{{ route('home') }}" class="btn btn-primary">Bosh sahifaga qaytish</a>
        </x-empty-state>
    @endif
</section>
