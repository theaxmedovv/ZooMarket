@extends('layouts.app')

@section('content')
@php
    $label = 'mb-[7px] block text-[13px] font-bold tracking-wide text-ink';
    $hint = 'text-xs text-muted';
    $required = '<span class="text-brand">*</span>';
    // Input with a leading icon: <div class="$iconWrap"><i class="bi … $icon"></i><input class="$field"></div>
    $iconWrap = 'group relative flex items-center';
    $icon = 'pointer-events-none absolute left-3.5 text-[15px] text-muted transition-colors group-focus-within:text-brand';
    $field = 'input border-line bg-surface pl-[42px]';
@endphp
<div class="page">
    <x-page-head title="Yangi e'lon" subtitle="Hayvoningiz haqida aniq ma'lumot va sifatli rasmlar qo'shing — o'ng tomonda e'lon xaridorlarga qanday ko'rinishini kuzatib borasiz." />

    {{-- Error banner --}}
    @if($errors->any())
        <div class="mb-6 flex items-start gap-2 rounded-[14px] border border-accent/35 bg-accent/10 px-5 py-4">
            <i class="bi bi-exclamation-triangle-fill mt-0.5 text-xl text-accent"></i>
            <div>
                <h6 class="mb-1 font-bold text-ink">Iltimos, quyidagi xatoliklarni to'g'rilang:</h6>
                <ul class="list-disc pl-4 text-sm text-ink/70">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- AI moderation notice --}}
    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-brand/25 bg-brand/5 p-4">
        <div class="grid size-11 shrink-0 place-items-center rounded-full bg-brand/12 text-xl text-brand"><i class="bi bi-robot"></i></div>
        <div class="text-sm">
            <strong class="block text-brand">Sun'iy Intellekt (Groq AI) Moderatsiyasi</strong>
            <span class="text-ink/70">
                Har bir e'lonning matni va fotosuratlari xavfsizlik, soxta e'lonlar va taqiqlangan turlarga qarshi Groq AI orqali avtomatik tekshiriladi. Faqat mezonlarga javob beradigan e'lonlar ommaga e'lon qilinadi.
            </span>
        </div>
    </div>

    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
        @csrf

        <div class="grid items-start gap-6 lg:grid-cols-3">
            {{-- Main column: form sections --}}
            <div class="flex flex-col gap-6 lg:col-span-2">

                {{-- Section 1: basics --}}
                <x-form-section icon="bi-info-circle-fill" title="Asosiy ma'lumotlar" desc="E'lonning nomi, kategoriyasi va zotini belgilang">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="title" class="{{ $label }}">E'lon sarlavhasi {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-card-heading {{ $icon }}"></i>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" class="{{ $field }}"
                                       placeholder="Masalan: Shotland osmaxo'r mushukchasi, 2 oylik" required maxlength="255">
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="{{ $hint }}">Qisqa va xaridorni jalb qiluvchi sarlavha tanlang</span>
                                <span class="{{ $hint }}" id="titleCount">0/255</span>
                            </div>
                        </div>

                        <div>
                            <label for="category_id" class="{{ $label }}">Kategoriya {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-grid-fill {{ $icon }}"></i>
                                <select name="category_id" id="category_id" class="{{ $field }} cursor-pointer" required>
                                    <option value="">Kategoriyani tanlang</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="breed" class="{{ $label }}">Zot (Breed) {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-tag-fill {{ $icon }}"></i>
                                <input type="text" name="breed" id="breed" value="{{ old('breed') }}" class="{{ $field }}"
                                       placeholder="Masalan: Shotland, Nemis ovcharkasi, Kane-korso" required>
                            </div>
                        </div>
                    </div>
                </x-form-section>

                {{-- Section 2: parameters --}}
                <x-form-section icon="bi-sliders" title="Parametrlar va holat" desc="Hayvonning jinsi, yoshi, rangi va e'lon statusi">
                    <div class="grid gap-4 md:grid-cols-12">
                        {{-- Total quantity --}}
                        <div class="md:col-span-5">
                            <label for="quantity" class="{{ $label }}">Jami hayvonlar soni {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-hash {{ $icon }}"></i>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" max="100"
                                       class="{{ $field }}" placeholder="Masalan: 1, 5, 10, 50" required>
                            </div>
                            <span class="{{ $hint }}">Maksimal 100 tagacha (1–100)</span>
                        </div>

                        {{-- Gender selector --}}
                        <div class="md:col-span-7">
                            <span class="{{ $label }}">Jinsi {!! $required !!}</span>
                            <div class="flex h-11 gap-[3px] rounded-[10px] border border-line bg-surface p-[3px]">
                                @foreach([
                                    ['genderOptMale', 'genderMale', 'male', 'bi-gender-male', 'Erkak', old('gender', 'male') === 'male', true],
                                    ['genderOptFemale', 'genderFemale', 'female', 'bi-gender-female', "Urg'ochi", old('gender') === 'female', true],
                                    ['genderOptMixed', 'genderMixed', 'mixed', 'bi-shuffle', 'Aralash (Mixed)', old('gender') === 'mixed', false],
                                ] as [$optId, $inputId, $value, $genderIcon, $genderLabel, $checked, $isRequired])
                                    <label class="flex-1 cursor-pointer" id="{{ $optId }}" @if($value === 'mixed' && (int) old('quantity', 1) <= 1) hidden @endif>
                                        <input type="radio" name="gender" id="{{ $inputId }}" value="{{ $value }}" class="peer sr-only" @checked($checked) @required($isRequired)>
                                        <span class="inline-flex size-full items-center justify-center gap-1.5 rounded-lg text-[13px] font-semibold text-muted transition peer-checked:bg-brand peer-checked:font-bold peer-checked:text-white peer-checked:shadow-[0_2px_8px_rgba(0,142,204,.25)] peer-focus-visible:outline-2 peer-focus-visible:outline-brand">
                                            <i class="bi {{ $genderIcon }}"></i> {{ $genderLabel }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <span class="{{ $hint }}" id="genderHelpHint">Yakka hayvon uchun faqat Erkak yoki Urg'ochi</span>
                        </div>

                        {{-- Mixed gender split (only when gender === 'mixed') --}}
                        <div class="md:col-span-12" id="mixedBreakdownWrap" @if(old('gender') !== 'mixed') hidden @endif>
                            <div class="mt-1 rounded-xl border border-dashed border-brand/35 bg-surface p-4">
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-pie-chart-fill text-brand"></i>
                                        <span class="text-sm font-bold text-ink">Aralash jinslar soni taqsimoti:</span>
                                    </div>
                                    <div id="mixedSumStatusBadge" class="rounded-full border border-line bg-black/5 px-3 py-1 text-xs font-bold text-muted transition data-[state=invalid]:border-accent/40 data-[state=invalid]:bg-accent/15 data-[state=invalid]:text-accent data-[state=valid]:border-brand/40 data-[state=valid]:bg-brand/15 data-[state=valid]:text-brand">
                                        0 / 0 ta
                                    </div>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    @foreach([['male_quantity', 'bi-gender-male text-male', 'Erkaklar soni:', 'Masalan: 4'], ['female_quantity', 'bi-gender-female text-danger', "Urg'ochilar soni:", 'Masalan: 6']] as [$name, $qtyIcon, $qtyLabel, $placeholder])
                                        <div>
                                            <label for="{{ $name }}" class="{{ $label }} font-semibold text-muted"><i class="bi {{ $qtyIcon }} mr-1"></i> {{ $qtyLabel }}</label>
                                            <div class="{{ $iconWrap }}">
                                                <i class="bi {{ $qtyIcon }} {{ $icon }}"></i>
                                                <input type="number" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, '') }}" min="1" max="99" class="{{ $field }}" placeholder="{{ $placeholder }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="mixedSplitMessage" class="mt-2 text-sm text-muted data-[state=invalid]:text-accent data-[state=valid]:text-brand">
                                    <i class="bi bi-info-circle mr-1"></i> Erkaklar va urg'ochilar soni yig'indisi jami miqdorga teng bo'lishi shart.
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <label for="age" class="{{ $label }}">Yoshi {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-calendar3 {{ $icon }}"></i>
                                <input type="text" name="age" id="age" value="{{ old('age') }}" class="{{ $field }}" placeholder="Masalan: 3 oy, 1.5 yosh, 6 haftalik" required>
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <label for="color" class="{{ $label }}">Rangi</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-palette-fill {{ $icon }}"></i>
                                <input type="text" name="color" id="color" value="{{ old('color') }}" class="{{ $field }}" placeholder="Masalan: Qora, Oq, Zangori, Dog'dor">
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <label for="status" class="{{ $label }}">E'lon holati {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-activity {{ $icon }}"></i>
                                <select name="status" id="status" class="{{ $field }} cursor-pointer" required>
                                    <option value="active" @selected(old('status', 'active') === 'active')>🟢 Aktiv (Sotuvda)</option>
                                    <option value="reserved" @selected(old('status') === 'reserved')>🟡 Rezerv (Band qilingan)</option>
                                    <option value="sold" @selected(old('status') === 'sold')>🔴 Sotilgan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </x-form-section>

                {{-- Section 3: price & location --}}
                <x-form-section icon="bi-cash-stack" title="Narx va joylashuv" desc="To'lov qiymati, kelishuv sharti va joylashgan hudud">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label for="price" class="{{ $label }}">Narx {!! $required !!}</label>
                            <div class="flex items-center overflow-hidden rounded-[10px] border border-line bg-surface transition focus-within:border-brand focus-within:ring-3 focus-within:ring-brand/15">
                                <div class="{{ $iconWrap }} flex-1">
                                    <i class="bi bi-currency-exchange {{ $icon }}"></i>
                                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}"
                                           class="h-11 w-full min-w-0 bg-transparent pr-3.5 pl-[42px] text-sm text-ink outline-none placeholder:text-faint" placeholder="Masalan: 1500000" required>
                                </div>
                                <select name="currency" id="currency" class="h-11 cursor-pointer border-l border-line bg-white px-3.5 text-sm font-bold text-brand outline-none" required>
                                    @foreach(['UZS', 'USD', 'EUR', 'RUB'] as $currency)
                                        <option value="{{ $currency }}" @selected(old('currency', 'UZS') === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-end">
                            <label for="is_negotiable" class="flex h-11 w-full cursor-pointer items-center gap-3 rounded-[10px] border border-line bg-surface px-3.5 transition select-none hover:border-brand/30">
                                <input type="checkbox" name="is_negotiable" id="is_negotiable" value="1" class="peer sr-only" @checked(old('is_negotiable'))>
                                <span class="relative h-[22px] w-[38px] shrink-0 rounded-full bg-brand-tint transition peer-checked:bg-brand peer-focus-visible:outline-2 peer-focus-visible:outline-brand after:absolute after:top-[3px] after:left-[3px] after:size-4 after:rounded-full after:bg-muted after:transition after:content-[''] peer-checked:after:translate-x-4 peer-checked:after:bg-white"></span>
                                <span class="flex flex-col leading-tight">
                                    <span class="text-[13px] font-bold text-ink">Narx kelishiladi</span>
                                    <span class="text-[11px] text-muted">Savdolashish mumkin</span>
                                </span>
                            </label>
                        </div>

                        <div class="md:col-span-3">
                            <label for="location" class="{{ $label }}">Manzil / Joylashuv {!! $required !!}</label>
                            <div class="{{ $iconWrap }}">
                                <i class="bi bi-geo-alt-fill {{ $icon }}"></i>
                                <input type="text" name="location" id="location" value="{{ old('location') }}" class="{{ $field }}" placeholder="Masalan: Toshkent shahar, Yunusobod tumani" required>
                            </div>
                        </div>
                    </div>
                </x-form-section>

                {{-- Section 4: photos (up to 3) --}}
                <x-form-section icon="bi-images" desc="Birinchi fotosurat asosiy muqova bo'ladi. Har biri 2MB dan oshmasin.">
                    <x-slot:title>Fotosuratlar <span class="text-base font-normal text-muted">(Maksimum 3 ta)</span></x-slot:title>
                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach([
                            ['bi-cloud-arrow-up-fill', '1-rasmni yuklash', 'JPG, PNG, GIF (max 2MB)', 'Muqova rasm'],
                            ['bi-image', '2-rasm (ixtiyoriy)', 'Boshqa burchakdan', '2-rasm'],
                            ['bi-image', '3-rasm (ixtiyoriy)', "Qo'shimcha tafsilot", '3-rasm'],
                        ] as $i => [$slotIcon, $slotText, $slotHint, $alt])
                            <div id="slotWrap{{ $i }}" @class(['relative flex h-[150px] flex-col items-center justify-center overflow-hidden rounded-[14px] border-[1.5px] border-dashed bg-surface transition hover:border-brand hover:bg-brand/3', $i === 0 ? 'border-brand/35' : 'border-line'])>
                                <input type="file" name="images[]" id="imageInput{{ $i }}" hidden accept="image/jpeg,image/png,image/jpg,image/gif" data-slot="{{ $i }}">
                                @if($i === 0)
                                    <div class="pointer-events-none absolute top-2 left-2 z-[2] rounded-full border border-brand/30 bg-brand/15 px-2 py-0.5 text-[11px] font-bold text-brand">Asosiy muqova</div>
                                @endif

                                <label for="imageInput{{ $i }}" data-slot-empty class="flex size-full cursor-pointer flex-col items-center justify-center p-4 text-center">
                                    <span class="mb-1.5 text-2xl text-brand opacity-90"><i class="bi {{ $slotIcon }}"></i></span>
                                    <span class="mb-0.5 text-[13px] font-bold text-ink">{{ $slotText }}</span>
                                    <span class="text-[11px] text-muted">{{ $slotHint }}</span>
                                </label>

                                <div class="absolute inset-0" id="previewState{{ $i }}" hidden>
                                    <img src="" alt="{{ $alt }}" id="previewImg{{ $i }}" class="size-full object-cover">
                                    <button type="button" class="absolute top-2 right-2 z-[3] grid size-7 cursor-pointer place-items-center rounded-full border border-white/20 bg-black/75 text-[13px] text-danger transition hover:scale-110 hover:bg-[#ff3333] hover:text-white" onclick="removeImage({{ $i }})" title="O'chirish">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-form-section>

                {{-- Section 5: description --}}
                <x-form-section icon="bi-text-paragraph" desc="Salomatligi, xarakteri, emlashlari va boshqa afzalliklari">
                    <x-slot:title>Batafsil tavsif {!! $required !!}</x-slot:title>
                    <textarea name="description" id="description" rows="5" aria-label="Batafsil tavsif"
                              class="input mb-4 h-auto min-h-[120px] resize-y border-line bg-surface py-3 leading-normal"
                              placeholder="Hayvonning o'ziga xosligi, ovqatlanishi, emlash holati, bolalari yoki yoshi to'g'risida to'liq yozing..." required>{{ old('description') }}</textarea>

                    {{-- Quick description pills --}}
                    <span class="{{ $hint }} mb-2 block">Tezkor qo'shimchalar (bosish orqali matnga qo'shing):</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            "💉 Barcha vaksina va emlashlari o'z vaqtida qilingan." => '+ Emlangan',
                            '📋 Xalqaro veterinariya pasporti mavjud.' => '+ Pasporti bor',
                            "🧼 O'ta toza, sog'lom va faol hayvon." => "+ Sog'lom va toza",
                            "🧸 Bolalar bilan o'ynashni yaxshi ko'radi, fe'l-atvori yuvosh." => "+ Yuvosh va o'ynoqi",
                            '🚗 Boshqa viloyatlarga kelishilgan holda yetkazib berish mumkin.' => '+ Yetkazib berish',
                        ] as $snippet => $pillLabel)
                            <button type="button" data-append="{{ $snippet }}" class="cursor-pointer rounded-full border border-line bg-black/4 px-2.5 py-1 text-xs font-semibold text-muted transition hover:-translate-y-px hover:border-brand hover:bg-brand/9 hover:text-brand">{{ $pillLabel }}</button>
                        @endforeach
                    </div>
                </x-form-section>

                {{-- Submit bar --}}
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="btn btn-primary h-12 rounded-xl px-7 text-[15px] shadow-[0_4px_18px_rgba(0,142,204,.25)] hover:-translate-y-0.5" id="submitBtn">
                        <i class="bi bi-cloud-arrow-up-fill"></i> E'lonni chop etish
                    </button>
                    <a href="{{ route('posts.index') }}" class="btn h-12 rounded-xl border-line font-semibold text-muted hover:border-ink hover:text-ink">Bekor qilish</a>
                </div>
            </div>

            {{-- Sidebar: live preview & selling tips --}}
            <div class="lg:sticky lg:top-[92px]">
                {{-- Live preview --}}
                <div class="mb-6 rounded-[18px] border border-line bg-white p-4 shadow-[0_8px_24px_rgba(0,0,0,.12)]">
                    <div class="mb-3.5 flex items-center justify-between border-b border-line pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="size-2 animate-pulse-dot rounded-full bg-brand shadow-[0_0_10px_var(--color-brand)]"></span>
                            <span class="text-[15px] font-bold text-ink">Jonli ko'rinish</span>
                        </div>
                        <span class="rounded-full bg-black/4 px-2 text-[11px] text-muted">Katalogda</span>
                    </div>

                    <div class="overflow-hidden rounded-[18px] border border-line">
                        <div class="relative h-[200px] overflow-hidden border-b border-line bg-soft">
                            <div id="liveBackdrop" class="pointer-events-none absolute -inset-3.5 scale-115 bg-cover bg-center opacity-85 blur-[18px] brightness-[.28]"></div>
                            <img src="" alt="Muqova" id="liveImg" hidden class="relative z-[1] size-full object-cover">
                            <div id="liveImgPlaceholder" class="flex size-full flex-col items-center justify-center gap-1 text-muted">
                                <i class="bi bi-camera text-2xl"></i>
                                <span class="text-sm">Rasm yuklanmagan</span>
                            </div>
                            <span id="liveCategoryBadge" class="absolute top-2.5 left-2.5 z-[2] rounded-full border border-black/12 bg-white/85 px-2.5 py-[3px] text-[11px] font-bold text-ink">Kategoriya</span>
                        </div>

                        <div class="flex flex-col gap-2 px-4 pt-3.5 pb-4">
                            <div class="flex items-center gap-2">
                                <div class="grid size-[26px] shrink-0 place-items-center rounded-full border border-brand/30 bg-brand-tint text-xs font-bold text-brand">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                                <div class="leading-tight">
                                    <span class="block text-sm font-semibold text-ink">{{ auth()->user()->name ?? 'Siz' }}</span>
                                    <span class="text-[11px] text-faint">Hozirgina</span>
                                </div>
                            </div>

                            <h5 class="line-clamp-2 text-[17px] font-bold text-ink" id="liveTitle">E'lon sarlavhasi bu yerda ko'rinadi</h5>
                            <p class="line-clamp-2 text-sm text-muted" id="liveDesc">Hayvon haqidagi qisqacha tavsif bu yerda aks ettiriladi...</p>

                            <div class="flex items-center justify-between text-sm text-muted">
                                <span class="inline-flex items-center gap-1"><i class="bi bi-tag text-brand"></i> <span id="liveBreed">Zot ko'rsatilmagan</span></span>
                                <span class="inline-flex items-center gap-1"><i class="bi bi-geo-alt text-accent"></i> <span id="liveLocation">Manzil</span></span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-muted">
                                <span class="inline-flex items-center gap-1"><i class="bi bi-box-seam text-brand"></i> <span id="liveQuantity">1 ta mavjud</span></span>
                                <span class="inline-flex items-center gap-1"><i class="bi bi-gender-ambiguous text-male"></i> <span id="liveGender">Erkak ♂</span></span>
                            </div>

                            <div class="text-xl font-extrabold text-brand" id="livePrice">0 <small class="text-xs text-ink/70">UZS</small></div>

                            <div class="flex items-center gap-2 border-t border-black/5 pt-2.5">
                                <span id="liveNegotiableBadge" hidden class="rounded-full border border-brand/30 bg-brand/12 px-2 py-0.5 text-[11px] font-semibold text-brand"><i class="bi bi-check2-circle"></i> Kelishiladi</span>
                                <span id="liveStatusBadge" data-status="active" class="ml-auto rounded-md bg-black/4 px-2 py-0.5 text-xs font-bold text-muted data-[status=active]:text-brand data-[status=reserved]:text-accent">Aktiv</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="rounded-[18px] border border-line bg-white/85 p-[18px]">
                    <div class="mb-3 flex items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-xl text-brand"></i>
                        <h6 class="text-sm font-bold text-ink">Tezroq sotish uchun tavsiyalar</h6>
                    </div>
                    <ul class="flex flex-col gap-2.5">
                        @foreach([
                            ["Yorug' va sifatli fotosuratlar:", "Xaridorlar aniq ko'ringan fotosuratlarga 3 barobar ko'proq murojaat qilishadi."],
                            ['Emlash va tibbiy holat:', 'Vaksina va pasport ma\'lumotlarini kiritish xaridor ishonchini oshiradi.'],
                            ['Aniq manzil:', "Shahar va tumaningizni yozsangiz, yaqin atrofdagi qiziquvchilar tezroq bog'lanadi."],
                        ] as [$tipTitle, $tipText])
                            <li class="flex items-start gap-2 text-xs leading-snug text-muted">
                                <i class="bi bi-check-circle-fill mt-0.5 shrink-0 text-brand"></i>
                                <span><strong class="text-ink">{{ $tipTitle }}</strong> {{ $tipText }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Elements for live preview and quantity/gender
    const titleInput = document.getElementById('title');
    const categorySelect = document.getElementById('category_id');
    const breedInput = document.getElementById('breed');
    const priceInput = document.getElementById('price');
    const currencySelect = document.getElementById('currency');
    const locationInput = document.getElementById('location');
    const negotiableCheckbox = document.getElementById('is_negotiable');
    const descTextarea = document.getElementById('description');
    const statusSelect = document.getElementById('status');
    const titleCount = document.getElementById('titleCount');

    const qtyInput = document.getElementById('quantity');
    const genderOptMixed = document.getElementById('genderOptMixed');
    const genderHelpHint = document.getElementById('genderHelpHint');
    const mixedBreakdownWrap = document.getElementById('mixedBreakdownWrap');
    const maleQtyInput = document.getElementById('male_quantity');
    const femaleQtyInput = document.getElementById('female_quantity');
    const mixedSumStatusBadge = document.getElementById('mixedSumStatusBadge');
    const mixedSplitMessage = document.getElementById('mixedSplitMessage');
    const genderMale = document.getElementById('genderMale');
    const genderFemale = document.getElementById('genderFemale');
    const genderMixed = document.getElementById('genderMixed');

    const liveTitle = document.getElementById('liveTitle');
    const liveCategoryBadge = document.getElementById('liveCategoryBadge');
    const liveBreed = document.getElementById('liveBreed');
    const livePrice = document.getElementById('livePrice');
    const liveLocation = document.getElementById('liveLocation');
    const liveDesc = document.getElementById('liveDesc');
    const liveNegotiableBadge = document.getElementById('liveNegotiableBadge');
    const liveStatusBadge = document.getElementById('liveStatusBadge');
    const liveQuantity = document.getElementById('liveQuantity');
    const liveGender = document.getElementById('liveGender');

    function syncGenderAndQuantity() {
        if (!qtyInput) return;
        let total = parseInt(qtyInput.value) || 1;
        if (total < 1) total = 1;
        if (total > 100) total = 100;
        qtyInput.value = total;

        // A single animal is either male or female; "mixed" only makes sense for 2+.
        genderOptMixed.hidden = total === 1;
        if (total === 1 && genderMixed && genderMixed.checked) {
            genderMale.checked = true;
        }
        if (genderHelpHint) {
            genderHelpHint.textContent = total === 1
                ? "Yakka hayvon uchun faqat Erkak yoki Urg'ochi tanlanadi"
                : "1 dan ortiq hayvonlar uchun Erkak, Urg'ochi yoki Aralash tanlashingiz mumkin";
        }

        const isMixed = genderMixed && genderMixed.checked && total > 1;
        mixedBreakdownWrap.hidden = !isMixed;
        maleQtyInput.required = isMixed;
        femaleQtyInput.required = isMixed;
        if (isMixed) {
            const mVal = parseInt(maleQtyInput.value) || 0;
            const fVal = parseInt(femaleQtyInput.value) || 0;
            const sum = mVal + fVal;
            const valid = mVal > 0 && fVal > 0 && sum === total;

            mixedSumStatusBadge.dataset.state = mixedSplitMessage.dataset.state = valid ? 'valid' : 'invalid';
            if (valid) {
                mixedSumStatusBadge.innerHTML = `<i class="bi bi-check-circle-fill mr-1"></i> ${mVal} erkak + ${fVal} urg'ochi = ${total} ta (To'g'ri)`;
                mixedSplitMessage.innerHTML = `<i class="bi bi-check2 mr-1"></i> Miqdorlar to'liq mos keldi!`;
            } else {
                mixedSumStatusBadge.innerHTML = `<i class="bi bi-exclamation-triangle-fill mr-1"></i> ${mVal} + ${fVal} = ${sum} / Jami: ${total} ta`;
                mixedSplitMessage.innerHTML = `<i class="bi bi-exclamation-circle mr-1"></i> Erkak va urg'ochi yig'indisi (${sum}) umumiy son (${total}) ga teng bo'lishi shart.`;
            }
        }

        updatePreview();
    }

    function updatePreview() {
        // Title
        const titleVal = titleInput.value.trim();
        liveTitle.textContent = titleVal || "E'lon sarlavhasi bu yerda ko'rinadi";
        if (titleCount) {
            titleCount.textContent = `${titleInput.value.length}/255`;
        }

        // Category
        const selectedCatOption = categorySelect.options[categorySelect.selectedIndex];
        liveCategoryBadge.textContent = (selectedCatOption && selectedCatOption.value) ? selectedCatOption.text.trim() : "Kategoriya";

        // Breed
        liveBreed.textContent = breedInput.value.trim() || "Zot ko'rsatilmagan";

        // Quantity & gender
        const total = parseInt(qtyInput ? qtyInput.value : 1) || 1;
        let genderLabel = "Erkak ♂";
        if (genderMixed && genderMixed.checked && total > 1) {
            const m = parseInt(maleQtyInput.value) || 0;
            const f = parseInt(femaleQtyInput.value) || 0;
            genderLabel = `Aralash (${m} ♂ / ${f} ♀)`;
        } else if (genderFemale && genderFemale.checked) {
            genderLabel = "Urg'ochi ♀";
        }
        if (liveQuantity) liveQuantity.textContent = `${total} ta mavjud`;
        if (liveGender) liveGender.textContent = genderLabel;

        // Price & currency
        const priceVal = priceInput.value.trim();
        const formatted = priceVal ? Number(priceVal).toLocaleString('ru-RU') : '0';
        livePrice.innerHTML = `${formatted} <small class="text-xs text-ink/70"></small>`;
        livePrice.querySelector('small').textContent = currencySelect.value;

        // Location & description
        liveLocation.textContent = locationInput.value.trim() || "Manzil";
        liveDesc.textContent = descTextarea.value.trim() || "Hayvon haqidagi qisqacha tavsif bu yerda aks ettiriladi...";

        // Negotiable & status
        liveNegotiableBadge.hidden = !negotiableCheckbox.checked;
        liveStatusBadge.dataset.status = statusSelect.value;
        liveStatusBadge.textContent = { active: 'Aktiv', reserved: 'Rezerv' }[statusSelect.value] || 'Sotilgan';
    }

    [titleInput, categorySelect, breedInput, priceInput, currencySelect, locationInput, descTextarea, statusSelect].forEach(el => {
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });

    if (qtyInput) {
        qtyInput.addEventListener('input', syncGenderAndQuantity);
        qtyInput.addEventListener('change', syncGenderAndQuantity);
    }

    [genderMale, genderFemale, genderMixed].forEach(radio => {
        if (radio) {
            radio.addEventListener('change', syncGenderAndQuantity);
        }
    });

    [maleQtyInput, femaleQtyInput].forEach(inp => {
        if (inp) {
            inp.addEventListener('input', syncGenderAndQuantity);
            inp.addEventListener('change', syncGenderAndQuantity);
        }
    });

    if (negotiableCheckbox) {
        negotiableCheckbox.addEventListener('change', updatePreview);
    }

    syncGenderAndQuantity();

    // Image upload slots: pick or drop a file, preview it, mirror slot 0 into the live card.
    [0, 1, 2].forEach(index => {
        const input = document.getElementById(`imageInput${index}`);
        const slotWrap = document.getElementById(`slotWrap${index}`);
        input.addEventListener('change', function () {
            handleFileSelect(this.files[0], index);
        });
        slotWrap.addEventListener('dragover', function (e) {
            e.preventDefault();
            slotWrap.style.borderColor = 'var(--color-brand)';
        });
        slotWrap.addEventListener('dragleave', function (e) {
            e.preventDefault();
            slotWrap.style.borderColor = '';
        });
        slotWrap.addEventListener('drop', function (e) {
            e.preventDefault();
            slotWrap.style.borderColor = '';
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                input.files = e.dataTransfer.files;
                handleFileSelect(e.dataTransfer.files[0], index);
            }
        });
    });

    function setSlotPreview(slotIndex, src) {
        document.getElementById(`previewImg${slotIndex}`).src = src || '';
        document.getElementById(`previewState${slotIndex}`).hidden = !src;
        document.querySelector(`#slotWrap${slotIndex} [data-slot-empty]`).hidden = !!src;

        if (slotIndex === 0) {
            const liveImg = document.getElementById('liveImg');
            liveImg.src = src || '';
            liveImg.hidden = !src;
            document.getElementById('liveBackdrop').style.backgroundImage = src ? `url('${src}')` : 'none';
            document.getElementById('liveImgPlaceholder').hidden = !!src;
        }
    }

    function handleFileSelect(file, slotIndex) {
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert("Rasm hajmi 2MB dan oshmasligi kerak!");
            removeImage(slotIndex);
            return;
        }

        const reader = new FileReader();
        reader.onload = e => setSlotPreview(slotIndex, e.target.result);
        reader.readAsDataURL(file);
    }

    window.removeImage = function (slotIndex) {
        document.getElementById(`imageInput${slotIndex}`).value = '';
        setSlotPreview(slotIndex, null);
    };

    document.querySelectorAll('[data-append]').forEach(btn => btn.addEventListener('click', () => {
        const text = btn.dataset.append;
        descTextarea.value = descTextarea.value.trim() ? descTextarea.value.trim() + '\n' + text : text;
        updatePreview();
    }));

    // Mixed-gender split must add up before submitting.
    const createForm = document.getElementById('createPostForm');
    const submitBtn = document.getElementById('submitBtn');
    createForm.addEventListener('submit', function (e) {
        const total = parseInt(qtyInput.value) || 1;
        if (genderMixed && genderMixed.checked && total > 1) {
            const mVal = parseInt(maleQtyInput.value) || 0;
            const fVal = parseInt(femaleQtyInput.value) || 0;
            if ((mVal + fVal) !== total || mVal < 1 || fVal < 1) {
                e.preventDefault();
                alert(`Xatolik: Erkak (${mVal}) va urg'ochi (${fVal}) hayvonlar soni yig'indisi umumiy miqdorga (${total}) teng bo'lishi shart!`);
                mixedBreakdownWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }
        submitBtn.lastChild.textContent = ' Joylanmoqda...';
    });
});
</script>
@endsection
