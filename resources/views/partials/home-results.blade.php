{{-- Home page in search mode (a category was picked or the header search was used): filter sidebar on the
     left, listings on the right. Variables come from home.blade.php; $facets holds the breeds and cities of
     the chosen category. Any change in the sidebar reloads the results (data-autosubmit, resources/js/app.js). --}}
@php
    $f = $filters;
    $sorts = [
        'latest' => 'Eng yangilari',
        'price_asc' => 'Narx: arzonroq',
        'price_desc' => 'Narx: qimmatroq',
        'oldest' => 'Eng eskisi',
    ];
    $block = 'mb-6 border-b border-line pb-6 last:mb-0 last:border-0 last:pb-0';
    $heading = 'mb-3 text-sm font-extrabold text-ink';
    $option = 'flex min-h-11 cursor-pointer items-center gap-2.5 py-1 text-sm text-ink hover:text-brand lg:min-h-8';
    $radio = 'size-4 shrink-0 cursor-pointer accent-brand';
    $visible = 6;
    $filterByGender = ! $category || \App\Support\ListingProfile::forCategory($category)->usesGender();
    $anyFilter = $f->categoryId || $f->gender !== '' || $f->breed !== '' || $f->region !== '' || $f->location !== '' || $f->currency !== ''
        || $f->priceMin !== null || $f->priceMax !== null || $f->sort !== 'latest';
    // Radio groups for the breed / city lists: [form field, heading, options, current value]
    $groups = array_filter([
        ['breed', 'Turi', $facets['breeds'] ?? [], $f->breed],
        ['region', 'Manzil', $facets['regions'] ?? [], $f->region],
    ], fn ($g) => count($g[2]) > 0);
@endphp
<section class="mt-5" id="natijalar">
    <div class="grid items-start gap-5 lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-8">

        {{-- ============ Filters ============ --}}
        <aside>
            {{-- A bottom sheet on phones and tablets (opened by the filter button in the header), part of the page from lg up --}}
            <div id="filterSheet" class="max-lg:hidden max-lg:sheet lg:pr-1">
                <div class="mb-2 flex items-center justify-between lg:hidden">
                    <b class="text-lg text-ink">Filtrlar</b>
                    <button type="button" data-sheet-close aria-label="Yopish" class="grid size-11 cursor-pointer place-items-center rounded-lg text-xl text-ink hover:bg-soft"><i class="bi bi-x-lg"></i></button>
                </div>

                <form action="{{ route('home') }}" method="GET" data-autosubmit data-sheet-form>
                    @if($f->search !== '')<input type="hidden" name="q" value="{{ $f->search }}">@endif
                    @if($f->categoryId)<input type="hidden" name="category_id" value="{{ $f->categoryId }}">@endif

                    <div class="{{ $block }}">
                        <h3 class="{{ $heading }}">Saralash</h3>
                        <select name="sort" aria-label="Saralash" class="input">
                            @foreach($sorts as $value => $label)
                                <option value="{{ $value }}" @selected($f->sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Before a category is chosen: pick one here. Afterwards the breeds ("Turi") of that category replace this list. --}}
                    @unless($f->categoryId)
                        <div class="{{ $block }}">
                            <h3 class="{{ $heading }}">Kategoriyalar</h3>
                            <ul class="flex flex-col gap-0.5">
                                @foreach($categories as $c)
                                    @php $isActive = $f->categoryId === $c->id; @endphp
                                    <li>
                                        <a href="{{ route('home', $isActive ? array_filter(['q' => $f->search]) : array_filter(['q' => $f->search, 'category_id' => $c->id])) }}"
                                           @if($isActive) aria-current="page" @endif
                                           class="flex min-h-11 items-center justify-between gap-2 rounded-lg px-2.5 py-1.5 text-sm text-muted hover:bg-soft hover:text-ink current:bg-brand-soft current:font-bold current:text-brand lg:min-h-9">
                                            {{ $c->name }}@if($isActive)<i class="bi bi-x-lg text-[11px]"></i>@endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endunless

                    <div class="{{ $block }}">
                        <h3 class="{{ $heading }}">Narx</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="price_min" min="0" step="any" value="{{ $f->priceMin }}" placeholder="dan" aria-label="Eng kam narx" class="input">
                            <input type="number" name="price_max" min="0" step="any" value="{{ $f->priceMax }}" placeholder="gacha" aria-label="Eng ko'p narx" class="input">
                        </div>
                        <select name="currency" aria-label="Valyuta" class="input mt-2">
                            <option value="">Barcha valyutalar</option>
                            @foreach(\App\Support\ListingFilters::CURRENCIES as $currency)
                                <option value="{{ $currency }}" @selected($f->currency === $currency)>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($filterByGender)
                    <div class="{{ $block }}">
                        <h3 class="{{ $heading }}">Jinsi</h3>
                        @foreach(['' => "Farqi yo'q", 'male' => 'Erkak', 'female' => "Urg'ochi"] as $value => $label)
                            <label class="{{ $option }}"><input type="radio" name="gender" value="{{ $value }}" class="{{ $radio }}" @checked($f->gender === $value)>{{ $label }}</label>
                        @endforeach
                    </div>
                    @endif

                    {{-- Breeds and cities of the chosen category; the long lists fold behind "Yana N" --}}
                    @foreach($groups as [$field, $title, $options, $current])
                        @php
                            // Keep the chosen option visible even when it sits in the folded part
                            $folded = ! in_array($current, array_column(array_slice($options, $visible), 'name'), true);
                        @endphp
                        <div class="{{ $block }}">
                            <h3 class="{{ $heading }}">{{ $title }}</h3>
                            <div>
                                <label class="{{ $option }}"><input type="radio" name="{{ $field }}" value="" class="{{ $radio }}" @checked($current === '')>Farqi yo'q</label>
                                @foreach($options as $o)
                                    <label class="{{ $option }}" @if($folded && $loop->index >= $visible) data-catalog-extra hidden @endif>
                                        <input type="radio" name="{{ $field }}" value="{{ $o['name'] }}" class="{{ $radio }}" @checked($current === $o['name'])>
                                        <span class="min-w-0 flex-1 truncate">{{ $o['name'] }}</span><span class="text-xs text-faint">{{ $o['total'] }}</span>
                                    </label>
                                @endforeach
                                @if($folded && count($options) > $visible)
                                    <button type="button" data-catalog-more class="mt-1 inline-flex min-h-11 cursor-pointer items-center gap-1 text-sm text-muted hover:text-brand lg:min-h-0">Yana {{ count($options) - $visible }} <i class="bi bi-chevron-down text-xs"></i></button>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- On phones this bar sticks to the bottom of the sheet --}}
                    <div class="flex gap-2 max-lg:sticky max-lg:bottom-[calc(-1rem-env(safe-area-inset-bottom))] max-lg:-mx-4 max-lg:border-t max-lg:border-line max-lg:bg-white max-lg:px-4 max-lg:py-3">
                        <button type="submit" class="btn btn-primary flex-1 text-[13px]"><i class="bi bi-funnel-fill"></i> <span class="lg:hidden">Natijalarni ko'rsatish ({{ $results->total() }})</span><span class="max-lg:hidden">Qo'llash</span></button>
                        @if($anyFilter)
                            <a href="{{ route('home', array_filter(['q' => $f->search])) }}" class="btn btn-outline px-3 text-[13px]" title="Filtrlarni tozalash"><i class="bi bi-arrow-counterclockwise"></i> <span class="max-[420px]:sr-only">Tozalash</span></a>
                        @endif
                    </div>
                </form>
            </div>
        </aside>

        {{-- ============ Listings ============ --}}
        <div class="min-w-0">
            @if($chips)
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @foreach($chips as [$keys, $label])
                        <a class="{{ $chip }} border-line bg-soft text-ink hover:border-danger hover:text-danger" href="{{ route('home', request()->except($keys)) }}" title="Olib tashlash">
                            {{ $label }} <i class="bi bi-x-lg text-[10px] opacity-70"></i>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($results->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 md:gap-4 lg:grid-cols-3 xl:grid-cols-4" id="resultsGrid">
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
        </div>
    </div>
</section>
