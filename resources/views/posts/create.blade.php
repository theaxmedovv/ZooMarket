@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell">
    {{-- Breadcrumb & Back Navigation --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <a href="{{ route('posts.index') }}" class="btn-back-link">
            <i class="bi bi-arrow-left"></i> E'lonlarga qaytish
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-lime-soft text-lime px-3 py-1-5 rounded-pill">
                <i class="bi bi-plus-circle me-1"></i> Yangi e'lon
            </span>
        </div>
    </div>

    {{-- Page Hero Header --}}
    <div class="create-hero-banner mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h1 class="create-hero-title">Hayvon sotuv e'lonini joylash</h1>
                <p class="create-hero-subtitle">
                    Hayvoningiz haqidagi ma'lumotlarni aniq to'ldiring, sifatli fotosuratlarni yuklang va xaridorlarga taqdim eting.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="text-muted small d-inline-flex align-items-center gap-1">
                    <i class="bi bi-shield-check text-lime fs-6"></i> Xavfsiz va tezkor savdo
                </span>
            </div>
        </div>
    </div>

    {{-- Error Banner --}}
    @if($errors->any())
        <div class="alert-error-box mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill text-orange fs-5 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1 text-cream">Iltimos, quyidagi xatoliklarni to'g'rilang:</h6>
                    <ul class="mb-0 ps-3 small text-cream-50">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
        @csrf

        <div class="row g-4">
            {{-- ── LEFT MAIN COLUMN: FORM SECTIONS ── --}}
            <div class="col-lg-8">
                
                {{-- SECTION 1: Asosiy ma'lumotlar --}}
                <div class="form-section-card mb-4">
                    <div class="section-card-header">
                        <div class="section-icon-box"><i class="bi bi-info-circle-fill"></i></div>
                        <div>
                            <h5 class="section-title">Asosiy ma'lumotlar</h5>
                            <p class="section-desc">E'lonning nomi, kategoriyasi va zotini belgilang</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label-custom">E'lon sarlavhasi <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-card-heading input-icon"></i>
                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                       class="custom-form-input"
                                       placeholder="Masalan: Shotland osmaxo'r mushukchasi, 2 oylik" required maxlength="255">
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="form-field-hint">Qisqa va xaridorni jalb qiluvchi sarlavha tanlang</span>
                                <span class="form-field-hint" id="titleCount">0/255</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label-custom">Kategoriya <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-grid-fill input-icon"></i>
                                <select name="category_id" id="category_id" class="custom-form-select" required>
                                    <option value="">Kategoriyani tanlang</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="breed" class="form-label-custom">Zot (Breed) <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-tag-fill input-icon"></i>
                                <input type="text" name="breed" id="breed" value="{{ old('breed') }}"
                                       class="custom-form-input"
                                       placeholder="Masalan: Shotland, Nemis ovcharkasi, Kane-korso" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Parametrlar va xususiyatlar --}}
                <div class="form-section-card mb-4">
                    <div class="section-card-header">
                        <div class="section-icon-box"><i class="bi bi-sliders"></i></div>
                        <div>
                            <h5 class="section-title">Parametrlar va holat</h5>
                            <p class="section-desc">Hayvonning jinsi, yoshi, rangi va e'lon statusi</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Total Quantity --}}
                        <div class="col-md-5">
                            <label for="quantity" class="form-label-custom">Jami hayvonlar soni <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-hash input-icon"></i>
                                <input type="number" name="quantity" id="quantity"
                                       value="{{ old('quantity', 1) }}" min="1" max="100"
                                       class="custom-form-input" placeholder="Masalan: 1, 5, 10, 50" required>
                            </div>
                            <span class="form-field-hint">Maksimal 100 tagacha (1–100)</span>
                        </div>

                        {{-- Gender Selector --}}
                        <div class="col-md-7">
                            <label class="form-label-custom d-block">Jinsi <span class="text-lime">*</span></label>
                            <div class="gender-segmented-control" id="genderSegmentedWrap">
                                <label class="gender-segment-option" id="genderOptMale">
                                    <input type="radio" name="gender" id="genderMale" value="male" @checked(old('gender', 'male') === 'male') required>
                                    <span class="gender-segment-btn">
                                        <i class="bi bi-gender-male"></i> Erkak
                                    </span>
                                </label>
                                <label class="gender-segment-option" id="genderOptFemale">
                                    <input type="radio" name="gender" id="genderFemale" value="female" @checked(old('gender') === 'female') required>
                                    <span class="gender-segment-btn">
                                        <i class="bi bi-gender-female"></i> Urg'ochi
                                    </span>
                                </label>
                                <label class="gender-segment-option {{ (int)old('quantity', 1) <= 1 ? 'd-none' : '' }}" id="genderOptMixed">
                                    <input type="radio" name="gender" id="genderMixed" value="mixed" @checked(old('gender') === 'mixed')>
                                    <span class="gender-segment-btn">
                                        <i class="bi bi-shuffle"></i> Aralash (Mixed)
                                    </span>
                                </label>
                            </div>
                            <span class="form-field-hint" id="genderHelpHint">Yakka hayvon uchun faqat Erkak yoki Urg'ochi</span>
                        </div>

                        {{-- Mixed Gender Split (shown only when gender === 'mixed') --}}
                        <div class="col-12 {{ old('gender') === 'mixed' ? '' : 'd-none' }}" id="mixedBreakdownWrap">
                            <div class="mixed-split-box">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-pie-chart-fill text-lime"></i>
                                        <span class="fw-bold small text-cream">Aralash jinslar soni taqsimoti:</span>
                                    </div>
                                    <div class="mixed-sum-status" id="mixedSumStatusBadge">
                                        0 / 0 ta
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="male_quantity" class="form-label-custom small text-muted">
                                            <i class="bi bi-gender-male text-info me-1"></i> Erkaklar soni:
                                        </label>
                                        <div class="input-with-icon">
                                            <i class="bi bi-gender-male input-icon text-info"></i>
                                            <input type="number" name="male_quantity" id="male_quantity"
                                                   value="{{ old('male_quantity', '') }}" min="1" max="99"
                                                   class="custom-form-input" placeholder="Masalan: 4">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="female_quantity" class="form-label-custom small text-muted">
                                            <i class="bi bi-gender-female text-danger me-1"></i> Urg'ochilar soni:
                                        </label>
                                        <div class="input-with-icon">
                                            <i class="bi bi-gender-female input-icon text-danger"></i>
                                            <input type="number" name="female_quantity" id="female_quantity"
                                                   value="{{ old('female_quantity', '') }}" min="1" max="99"
                                                   class="custom-form-input" placeholder="Masalan: 6">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted" id="mixedSplitMessage">
                                    <i class="bi bi-info-circle me-1"></i> Erkaklar va urg'ochilar soni yig'indisi jami miqdorga teng bo'lishi shart.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="age" class="form-label-custom">Yoshi <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-calendar3 input-icon"></i>
                                <input type="text" name="age" id="age" value="{{ old('age') }}"
                                       class="custom-form-input"
                                       placeholder="Masalan: 3 oy, 1.5 yosh, 6 haftalik" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="color" class="form-label-custom">Rangi</label>
                            <div class="input-with-icon">
                                <i class="bi bi-palette-fill input-icon"></i>
                                <input type="text" name="color" id="color" value="{{ old('color') }}"
                                       class="custom-form-input"
                                       placeholder="Masalan: Qora, Oq, Zangori, Dog'dor">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label-custom">E'lon holati <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-activity input-icon"></i>
                                <select name="status" id="status" class="custom-form-select" required>
                                    <option value="active" @selected(old('status', 'active') === 'active')>🟢 Aktiv (Sotuvda)</option>
                                    <option value="reserved" @selected(old('status') === 'reserved')>🟡 Rezerv (Band qilingan)</option>
                                    <option value="sold" @selected(old('status') === 'sold')>🔴 Sotilgan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Narx va Joylashuv --}}
                <div class="form-section-card mb-4">
                    <div class="section-card-header">
                        <div class="section-icon-box"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <h5 class="section-title">Narx va joylashuv</h5>
                            <p class="section-desc">To'lov qiymati, kelishuv sharti va joylashgan hudud</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="price" class="form-label-custom">Narx <span class="text-lime">*</span></label>
                            <div class="price-input-group">
                                <div class="price-input-wrap">
                                    <i class="bi bi-currency-exchange input-icon"></i>
                                    <input type="number" step="0.01" min="0" name="price" id="price"
                                           value="{{ old('price') }}"
                                           class="custom-form-input price-field"
                                           placeholder="Masalan: 1500000" required>
                                </div>
                                <select name="currency" id="currency" class="currency-select" required>
                                    <option value="UZS" @selected(old('currency', 'UZS') === 'UZS')>UZS</option>
                                    <option value="USD" @selected(old('currency') === 'USD')>USD</option>
                                    <option value="EUR" @selected(old('currency') === 'EUR')>EUR</option>
                                    <option value="RUB" @selected(old('currency') === 'RUB')>RUB</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <label class="negotiable-toggle-box w-100" for="is_negotiable">
                                <input type="checkbox" name="is_negotiable" id="is_negotiable" value="1"
                                       class="negotiable-checkbox" @checked(old('is_negotiable'))>
                                <div class="toggle-track">
                                    <div class="toggle-thumb"></div>
                                </div>
                                <div class="toggle-label-group">
                                    <span class="toggle-title">Narx kelishiladi</span>
                                    <span class="toggle-desc">Savdolashish mumkin</span>
                                </div>
                            </label>
                        </div>

                        <div class="col-12">
                            <label for="location" class="form-label-custom">Manzil / Joylashuv <span class="text-lime">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-geo-alt-fill input-icon"></i>
                                <input type="text" name="location" id="location" value="{{ old('location') }}"
                                       class="custom-form-input"
                                       placeholder="Masalan: Toshkent shahar, Yunusobod tumani" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: Fotosuratlar (3 ta gacha) --}}
                <div class="form-section-card mb-4">
                    <div class="section-card-header">
                        <div class="section-icon-box"><i class="bi bi-images"></i></div>
                        <div>
                            <h5 class="section-title">Fotosuratlar <span class="text-muted fw-normal fs-6">(Maksimum 3 ta)</span></h5>
                            <p class="section-desc">Birinchi fotosurat asosiy muqova bo'ladi. Har biri 2MB dan oshmasin.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Slot 1: Asosiy --}}
                        <div class="col-md-4">
                            <div class="image-upload-slot slot-main" id="slotWrap0">
                                <input type="file" name="images[]" id="imageInput0" class="d-none image-file-input" accept="image/jpeg,image/png,image/jpg,image/gif" data-slot="0">
                                <div class="upload-badge">Asosiy muqova</div>
                                
                                <div class="slot-empty-state" onclick="document.getElementById('imageInput0').click()">
                                    <div class="slot-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                                    <div class="slot-text">1-rasmni yuklash</div>
                                    <div class="slot-hint">JPG, PNG, GIF (max 2MB)</div>
                                </div>

                                <div class="slot-preview-state d-none" id="previewState0">
                                    <img src="" alt="Muqova rasm" id="previewImg0" class="slot-preview-image">
                                    <button type="button" class="btn-slot-remove" onclick="removeImage(0)" title="O'chirish">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Slot 2: Qo'shimcha --}}
                        <div class="col-md-4">
                            <div class="image-upload-slot" id="slotWrap1">
                                <input type="file" name="images[]" id="imageInput1" class="d-none image-file-input" accept="image/jpeg,image/png,image/jpg,image/gif" data-slot="1">
                                
                                <div class="slot-empty-state" onclick="document.getElementById('imageInput1').click()">
                                    <div class="slot-icon"><i class="bi bi-image"></i></div>
                                    <div class="slot-text">2-rasm (ixtiyoriy)</div>
                                    <div class="slot-hint">Boshqa burchakdan</div>
                                </div>

                                <div class="slot-preview-state d-none" id="previewState1">
                                    <img src="" alt="2-rasm" id="previewImg1" class="slot-preview-image">
                                    <button type="button" class="btn-slot-remove" onclick="removeImage(1)" title="O'chirish">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Slot 3: Qo'shimcha --}}
                        <div class="col-md-4">
                            <div class="image-upload-slot" id="slotWrap2">
                                <input type="file" name="images[]" id="imageInput2" class="d-none image-file-input" accept="image/jpeg,image/png,image/jpg,image/gif" data-slot="2">
                                
                                <div class="slot-empty-state" onclick="document.getElementById('imageInput2').click()">
                                    <div class="slot-icon"><i class="bi bi-image"></i></div>
                                    <div class="slot-text">3-rasm (ixtiyoriy)</div>
                                    <div class="slot-hint">Qo'shimcha tafsilot</div>
                                </div>

                                <div class="slot-preview-state d-none" id="previewState2">
                                    <img src="" alt="3-rasm" id="previewImg2" class="slot-preview-image">
                                    <button type="button" class="btn-slot-remove" onclick="removeImage(2)" title="O'chirish">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 5: Batafsil tavsif --}}
                <div class="form-section-card mb-4">
                    <div class="section-card-header">
                        <div class="section-icon-box"><i class="bi bi-text-paragraph"></i></div>
                        <div>
                            <h5 class="section-title">Batafsil tavsif <span class="text-lime">*</span></h5>
                            <p class="section-desc">Salomatligi, xarakteri, emlashlari va boshqa afzalliklari</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <textarea name="description" id="description" rows="5"
                                  class="custom-form-textarea"
                                  placeholder="Hayvonning o'ziga xosligi, ovqatlanishi, emlash holati, bolalari yoki yoshi to'g'risida to'liq yozing..." required>{{ old('description') }}</textarea>
                    </div>

                    {{-- Quick Description Pills --}}
                    <div>
                        <span class="d-block form-field-hint mb-2">Tezkor qo'shimchalar (bosish orqali matnga qo'shing):</span>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="quick-pill-btn" onclick="appendDescription('💉 Barcha vaksina va emlashlari o\'z vaqtida qilingan.')">
                                + Emlangan
                            </button>
                            <button type="button" class="quick-pill-btn" onclick="appendDescription('📋 Xalqaro veterinariya pasporti mavjud.')">
                                + Pasporti bor
                            </button>
                            <button type="button" class="quick-pill-btn" onclick="appendDescription('🧼 O\'ta toza, sog\'lom va faol hayvon.')">
                                + Sog'lom va toza
                            </button>
                            <button type="button" class="quick-pill-btn" onclick="appendDescription('🧸 Bolalar bilan o\'ynashni yaxshi ko\'radi, fe\'l-atvori yuvosh.')">
                                + Yuvosh va o'ynoqi
                            </button>
                            <button type="button" class="quick-pill-btn" onclick="appendDescription('🚗 Boshqa viloyatlarga kelishilgan holda yetkazib berish mumkin.')">
                                + Yetkazib berish
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit and Action Bar --}}
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <button type="submit" class="btn-submit-post" id="submitBtn">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> E'lonni chop etish
                    </button>
                    <a href="{{ route('posts.index') }}" class="btn-cancel-post">
                        Bekor qilish
                    </a>
                </div>

            </div>

            {{-- ── RIGHT SIDEBAR: LIVE PREVIEW & SELLING TIPS ── --}}
            <div class="col-lg-4">
                <div class="sticky-create-sidebar">
                    
                    {{-- Live Preview Card --}}
                    <div class="preview-box-shell mb-4">
                        <div class="preview-header-bar">
                            <div class="d-flex align-items-center gap-2">
                                <span class="live-dot"></span>
                                <span class="preview-title">Jonli ko'rinish</span>
                            </div>
                            <span class="badge bg-panel-soft text-muted rounded-pill px-2 py-0-5" style="font-size:0.68rem;">Katalogda</span>
                        </div>

                        <div class="preview-card-wrap">
                            <div class="post-card" id="liveCard">
                                <div class="card-img-wrap">
                                    <div class="card-img-backdrop" id="liveBackdrop" style="background-image: none;"></div>
                                    <img src="" alt="Muqova" class="card-img d-none" id="liveImg">
                                    <div class="card-img-placeholder" id="liveImgPlaceholder">
                                        <i class="bi bi-camera fs-3"></i>
                                        <span class="small">Rasm yuklanmagan</span>
                                    </div>
                                    <span class="card-badge" id="liveCategoryBadge">Kategoriya</span>
                                </div>

                                <div class="card-body-inner">
                                    <div class="card-author">
                                        <div class="author-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                                        <div>
                                            <span class="d-block fw-semibold text-cream">{{ auth()->user()->name ?? 'Siz' }}</span>
                                            <span class="author-time">Hozirgina</span>
                                        </div>
                                    </div>

                                    <h5 class="card-title" id="liveTitle">E'lon sarlavhasi bu yerda ko'rinadi</h5>

                                    <p class="card-desc" id="liveDesc">Hayvon haqidagi qisqacha tavsif bu yerda aks ettiriladi...</p>

                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small text-muted d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-tag text-lime"></i> <span id="liveBreed">Zot ko'rsatilmagan</span>
                                        </span>
                                        <span class="small text-muted d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-geo-alt text-orange"></i> <span id="liveLocation">Manzil</span>
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mb-2 small text-muted">
                                        <span class="d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-box-seam text-lime"></i> <span id="liveQuantity">1 ta mavjud</span>
                                        </span>
                                        <span class="d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-gender-ambiguous text-info"></i> <span id="liveGender">Erkak ♂</span>
                                        </span>
                                    </div>

                                    <div class="card-price" id="livePrice">
                                        0 <small id="liveCurrency">UZS</small>
                                    </div>

                                    <div class="card-footer-inner">
                                        <span class="action-tag tag-negotiable d-none" id="liveNegotiableBadge">
                                            <i class="bi bi-check2-circle"></i> Kelishiladi
                                        </span>
                                        <span class="badge bg-panel-soft text-muted ms-auto" id="liveStatusBadge">
                                            Aktiv
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tips Card --}}
                    <div class="tips-card">
                        <div class="tips-header">
                            <i class="bi bi-lightbulb-fill text-lime fs-5"></i>
                            <h6 class="tips-title">Tezroq sotish uchun tavsiyalar</h6>
                        </div>
                        <ul class="tips-list">
                            <li>
                                <i class="bi bi-check-circle-fill text-lime"></i>
                                <span><strong>Yorug' va sifatli fotosuratlar:</strong> Xaridorlar aniq ko'ringan fotosuratlarga 3 barobar ko'proq murojaat qilishadi.</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-lime"></i>
                                <span><strong>Emlash va tibbiy holat:</strong> Vaksina va pasport ma'lumotlarini kiritish xaridor ishonchini oshiradi.</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-lime"></i>
                                <span><strong>Aniq manzil:</strong> Shahar va tumaningizni yozsangiz, yaqin atrofdagi qiziquvchilar tezroq bog'lanadi.</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<style>
    :root {
        --ink: #060d07;
        --panel: #0d180e;
        --line: #1a2b1c;
        --lime: #c2f03c;
        --orange: #ff6b2b;
        --cream: #eee9de;
        --muted: #7a9a7d;
        --serif: 'Space Grotesk', sans-serif;
        --sans: 'DM Sans', sans-serif;
    }

    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-cream-50 { color: rgba(238, 233, 222, 0.7) !important; }
    .text-lime { color: var(--lime) !important; }
    .text-orange { color: var(--orange) !important; }
    .text-muted { color: var(--muted) !important; }
    .bg-lime-soft { background: rgba(194, 240, 60, 0.12) !important; }
    .bg-panel-soft { background: rgba(255, 255, 255, 0.04) !important; }
    .py-1-5 { padding-top: 0.38rem; padding-bottom: 0.38rem; }

    /* Navigation */
    .btn-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--line);
        color: var(--muted);
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-back-link:hover {
        border-color: var(--lime);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.08);
        transform: translateX(-2px);
    }

    /* Hero Banner */
    .create-hero-banner {
        background: linear-gradient(135deg, rgba(13, 24, 14, 0.95), rgba(18, 36, 20, 0.95));
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 24px 28px;
        position: relative;
        overflow: hidden;
    }
    .create-hero-banner::before {
        content: "";
        position: absolute;
        inset: -50% auto auto 70%;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(194, 240, 60, 0.08);
        filter: blur(14px);
    }
    .create-hero-title {
        font-family: var(--serif);
        font-size: clamp(1.4rem, 2.5vw, 1.9rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        margin: 0 0 6px;
        color: var(--cream);
    }
    .create-hero-subtitle {
        color: var(--muted);
        font-size: 0.86rem;
        margin: 0;
        max-width: 620px;
        line-height: 1.5;
    }

    /* Alert Error */
    .alert-error-box {
        background: rgba(255, 107, 43, 0.1);
        border: 1px solid rgba(255, 107, 43, 0.35);
        border-radius: 14px;
        padding: 16px 20px;
    }

    /* Section Cards */
    .form-section-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        transition: border-color 0.2s ease;
    }
    .form-section-card:hover {
        border-color: rgba(194, 240, 60, 0.25);
    }
    .section-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--line);
    }
    .section-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(194, 240, 60, 0.1);
        color: var(--lime);
        display: grid;
        place-items: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .section-title {
        font-family: var(--serif);
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0 0 2px;
        color: var(--cream);
    }
    .section-desc {
        font-size: 0.75rem;
        color: var(--muted);
        margin: 0;
    }

    /* Form Fields */
    .form-label-custom {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: var(--cream);
        margin-bottom: 7px;
    }
    .form-field-hint {
        font-size: 0.72rem;
        color: var(--muted);
    }

    .input-with-icon {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-icon {
        position: absolute;
        left: 14px;
        color: var(--muted);
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.2s;
    }
    .input-with-icon:focus-within .input-icon {
        color: var(--lime);
    }

    .custom-form-input,
    .custom-form-select,
    .custom-form-textarea {
        width: 100%;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 10px;
        color: var(--cream);
        font-size: 0.86rem;
        padding: 0 14px 0 42px;
        height: 44px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .custom-form-input:focus,
    .custom-form-select:focus,
    .custom-form-textarea:focus {
        border-color: var(--lime);
        box-shadow: 0 0 0 3px rgba(194, 240, 60, 0.14);
    }
    .custom-form-input::placeholder,
    .custom-form-textarea::placeholder {
        color: #536d56;
    }
    .custom-form-select {
        cursor: pointer;
        padding-right: 30px;
    }
    .custom-form-select option {
        background: #0d180e;
        color: var(--cream);
    }

    .custom-form-textarea {
        height: auto;
        padding: 12px 14px;
        min-height: 120px;
        resize: vertical;
        line-height: 1.5;
    }

    /* Gender Segmented Control */
    .gender-segmented-control {
        display: flex;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 3px;
        gap: 3px;
        height: 44px;
    }
    .gender-segment-option {
        flex: 1;
        margin: 0;
        cursor: pointer;
        position: relative;
    }
    .gender-segment-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .gender-segment-btn {
        width: 100%;
        height: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--muted);
        transition: all 0.2s ease;
    }
    .gender-segment-option input:checked + .gender-segment-btn {
        background: var(--lime);
        color: var(--ink);
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(194, 240, 60, 0.25);
    }

    /* Mixed Gender Breakdown Box */
    .mixed-split-box {
        background: #081209;
        border: 1px dashed rgba(194, 240, 60, 0.35);
        border-radius: 12px;
        padding: 16px;
        margin-top: 4px;
    }
    .mixed-sum-status {
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.05);
        color: var(--muted);
        border: 1px solid var(--line);
        transition: all 0.2s ease;
    }
    .mixed-sum-status.valid {
        background: rgba(194, 240, 60, 0.15);
        color: var(--lime);
        border-color: rgba(194, 240, 60, 0.4);
    }
    .mixed-sum-status.invalid {
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
        border-color: rgba(255, 107, 43, 0.4);
    }

    /* Price and Currency Group */
    .price-input-group {
        display: flex;
        align-items: center;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 10px;
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .price-input-group:focus-within {
        border-color: var(--lime);
        box-shadow: 0 0 0 3px rgba(194, 240, 60, 0.14);
    }
    .price-input-wrap {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }
    .custom-form-input.price-field {
        border: none;
        box-shadow: none;
        border-radius: 0;
        height: 44px;
        background: transparent;
    }
    .currency-select {
        height: 44px;
        background: #0d180e;
        border: none;
        border-left: 1px solid var(--line);
        color: var(--lime);
        font-weight: 700;
        font-size: 0.84rem;
        padding: 0 14px;
        outline: none;
        cursor: pointer;
    }
    .currency-select option {
        background: #0d180e;
        color: var(--cream);
    }

    /* Negotiable Box Toggle */
    .negotiable-toggle-box {
        display: flex;
        align-items: center;
        gap: 12px;
        height: 44px;
        padding: 0 14px;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 10px;
        cursor: pointer;
        user-select: none;
        margin: 0;
        transition: border-color 0.2s ease;
    }
    .negotiable-toggle-box:hover {
        border-color: rgba(194, 240, 60, 0.3);
    }
    .negotiable-checkbox {
        display: none;
    }
    .toggle-track {
        width: 38px;
        height: 22px;
        background: #192b1b;
        border-radius: 999px;
        position: relative;
        transition: background 0.2s ease;
        flex-shrink: 0;
    }
    .toggle-thumb {
        width: 16px;
        height: 16px;
        background: var(--muted);
        border-radius: 50%;
        position: absolute;
        top: 3px;
        left: 3px;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .negotiable-checkbox:checked + .toggle-track {
        background: var(--lime);
    }
    .negotiable-checkbox:checked + .toggle-track .toggle-thumb {
        transform: translateX(16px);
        background: var(--ink);
    }
    .toggle-label-group {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .toggle-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cream);
    }
    .toggle-desc {
        font-size: 0.68rem;
        color: var(--muted);
    }

    /* Image Upload Slots */
    .image-upload-slot {
        position: relative;
        height: 150px;
        border-radius: 14px;
        border: 1.5px dashed var(--line);
        background: #081209;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .image-upload-slot:hover {
        border-color: var(--lime);
        background: rgba(194, 240, 60, 0.03);
    }
    .image-upload-slot.slot-main {
        border-color: rgba(194, 240, 60, 0.35);
    }
    .upload-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(194, 240, 60, 0.15);
        color: var(--lime);
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid rgba(194, 240, 60, 0.3);
        z-index: 2;
        pointer-events: none;
    }
    .slot-empty-state {
        text-align: center;
        padding: 16px;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .slot-icon {
        font-size: 1.5rem;
        color: var(--lime);
        margin-bottom: 6px;
        opacity: 0.9;
    }
    .slot-text {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cream);
        margin-bottom: 2px;
    }
    .slot-hint {
        font-size: 0.68rem;
        color: var(--muted);
    }
    .slot-preview-state {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .slot-preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .btn-slot-remove {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #ff5555;
        display: grid;
        place-items: center;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 3;
    }
    .btn-slot-remove:hover {
        background: #ff3333;
        color: #fff;
        transform: scale(1.1);
    }

    /* Quick Description Pills */
    .quick-pill-btn {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--line);
        border-radius: 9999px;
        color: var(--muted);
        font-size: 0.73rem;
        font-weight: 600;
        padding: 4px 10px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .quick-pill-btn:hover {
        background: rgba(194, 240, 60, 0.09);
        border-color: var(--lime);
        color: var(--lime);
        transform: translateY(-1px);
    }

    /* Submit Actions */
    .btn-submit-post {
        height: 48px;
        padding: 0 28px;
        background: var(--lime);
        color: var(--ink);
        border: none;
        border-radius: 12px;
        font-family: var(--serif);
        font-size: 0.94rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 18px rgba(194, 240, 60, 0.25);
    }
    .btn-submit-post:hover {
        background: #d8ff61;
        transform: translateY(-2px);
        box-shadow: 0 6px 22px rgba(194, 240, 60, 0.35);
    }
    .btn-cancel-post {
        height: 48px;
        padding: 0 22px;
        background: transparent;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--muted);
        font-size: 0.88rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-cancel-post:hover {
        border-color: var(--cream);
        color: var(--cream);
    }

    /* Sticky Sidebar */
    .sticky-create-sidebar {
        position: sticky;
        top: 76px;
    }

    /* Live Preview Box */
    .preview-box-shell {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    .preview-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--line);
    }
    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--lime);
        box-shadow: 0 0 10px var(--lime);
        animation: pulseDot 1.8s infinite;
    }
    @keyframes pulseDot {
        0% { opacity: 0.4; transform: scale(0.9); }
        50% { opacity: 1; transform: scale(1.2); }
        100% { opacity: 0.4; transform: scale(0.9); }
    }
    .preview-title {
        font-family: var(--serif);
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--cream);
    }

    .tag-negotiable {
        background: rgba(194, 240, 60, 0.12);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.3);
        border-radius: 9999px;
        padding: 2px 8px;
        font-size: 0.68rem;
        font-weight: 600;
    }

    /* Tips Card */
    .tips-card {
        background: rgba(13, 24, 14, 0.85);
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 18px;
    }
    .tips-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    .tips-title {
        font-family: var(--serif);
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--cream);
        margin: 0;
    }
    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.76rem;
        color: var(--muted);
        line-height: 1.45;
    }
    .tips-list li i {
        font-size: 0.85rem;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .tips-list strong {
        color: var(--cream);
    }

    @media (max-width: 991.98px) {
        .sticky-create-sidebar {
            position: static;
            margin-top: 20px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Elements for Live Preview and Quantity/Gender
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
    const liveCurrency = document.getElementById('liveCurrency');
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

        if (total === 1) {
            // Quantity = 1: Show only Male and Female. Do NOT show Mixed.
            genderOptMixed.classList.add('d-none');
            if (genderMixed && genderMixed.checked) {
                genderMale.checked = true;
            }
            if (genderHelpHint) {
                genderHelpHint.textContent = "Yakka hayvon uchun faqat Erkak yoki Urg'ochi tanlanadi";
            }
        } else {
            // Quantity > 1: Show Male, Female, and Mixed.
            genderOptMixed.classList.remove('d-none');
            if (genderHelpHint) {
                genderHelpHint.textContent = "1 dan ortiq hayvonlar uchun Erkak, Urg'ochi yoki Aralash tanlashingiz mumkin";
            }
        }

        const isMixed = genderMixed && genderMixed.checked && total > 1;
        if (isMixed) {
            mixedBreakdownWrap.classList.remove('d-none');
            maleQtyInput.required = true;
            femaleQtyInput.required = true;

            const mVal = parseInt(maleQtyInput.value) || 0;
            const fVal = parseInt(femaleQtyInput.value) || 0;
            const sum = mVal + fVal;

            if (mVal > 0 && fVal > 0 && sum === total) {
                mixedSumStatusBadge.className = 'mixed-sum-status valid';
                mixedSumStatusBadge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> ${mVal} erkak + ${fVal} urg'ochi = ${total} ta (To'g'ri)`;
                mixedSplitMessage.className = 'mt-2 small text-lime';
                mixedSplitMessage.innerHTML = `<i class="bi bi-check2 me-1"></i> Miqdorlar to'liq mos keldi!`;
            } else {
                mixedSumStatusBadge.className = 'mixed-sum-status invalid';
                mixedSumStatusBadge.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> ${mVal} + ${fVal} = ${sum} / Jami: ${total} ta`;
                mixedSplitMessage.className = 'mt-2 small text-orange';
                mixedSplitMessage.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> Erkak va urg'ochi yig'indisi (${sum}) umumiy son (${total}) ga teng bo'lishi shart.`;
            }
        } else {
            mixedBreakdownWrap.classList.add('d-none');
            maleQtyInput.required = false;
            femaleQtyInput.required = false;
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

        // Quantity & Gender
        const total = parseInt(qtyInput ? qtyInput.value : 1) || 1;
        let genderLabel = "Erkak ♂";
        if (genderMixed && genderMixed.checked && total > 1) {
            const m = parseInt(maleQtyInput.value) || 0;
            const f = parseInt(femaleQtyInput.value) || 0;
            genderLabel = `Aralash (${m} ♂ / ${f} ♀)`;
        } else if (genderFemale && genderFemale.checked) {
            genderLabel = "Urg'ochi ♀";
        } else {
            genderLabel = "Erkak ♂";
        }
        if (liveQuantity) liveQuantity.textContent = `${total} ta mavjud`;
        if (liveGender) liveGender.textContent = genderLabel;

        // Price & Currency
        const priceVal = priceInput.value.trim();
        const currVal = currencySelect.value;
        if (priceVal) {
            const formatted = Number(priceVal).toLocaleString('ru-RU');
            livePrice.innerHTML = `${formatted} <small id="liveCurrency">${currVal}</small>`;
        } else {
            livePrice.innerHTML = `0 <small id="liveCurrency">${currVal}</small>`;
        }

        // Location
        liveLocation.textContent = locationInput.value.trim() || "Manzil";

        // Description
        const descVal = descTextarea.value.trim();
        liveDesc.textContent = descVal || "Hayvon haqidagi qisqacha tavsif bu yerda aks ettiriladi...";

        // Negotiable
        if (negotiableCheckbox.checked) {
            liveNegotiableBadge.classList.remove('d-none');
        } else {
            liveNegotiableBadge.classList.add('d-none');
        }

        // Status
        const stVal = statusSelect.value;
        if (stVal === 'active') {
            liveStatusBadge.textContent = 'Aktiv';
            liveStatusBadge.className = 'badge bg-panel-soft text-lime ms-auto';
        } else if (stVal === 'reserved') {
            liveStatusBadge.textContent = 'Rezerv';
            liveStatusBadge.className = 'badge bg-panel-soft text-orange ms-auto';
        } else {
            liveStatusBadge.textContent = 'Sotilgan';
            liveStatusBadge.className = 'badge bg-panel-soft text-muted ms-auto';
        }
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

    // Image Upload Handlers
    window.fileInputs = [
        document.getElementById('imageInput0'),
        document.getElementById('imageInput1'),
        document.getElementById('imageInput2')
    ];

    window.fileInputs.forEach((input, index) => {
        if (!input) return;
        input.addEventListener('change', function () {
            handleFileSelect(this.files[0], index);
        });

        const slotWrap = document.getElementById(`slotWrap${index}`);
        if (slotWrap) {
            slotWrap.addEventListener('dragover', function (e) {
                e.preventDefault();
                slotWrap.style.borderColor = 'var(--lime)';
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
        }
    });

    function handleFileSelect(file, slotIndex) {
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert("Rasm hajmi 2MB dan oshmasligi kerak!");
            removeImage(slotIndex);
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const previewState = document.getElementById(`previewState${slotIndex}`);
            const previewImg = document.getElementById(`previewImg${slotIndex}`);
            const emptyState = document.querySelector(`#slotWrap${slotIndex} .slot-empty-state`);

            previewImg.src = e.target.result;
            previewState.classList.remove('d-none');
            if (emptyState) emptyState.classList.add('d-none');

            // If main image (slot 0), update live card
            if (slotIndex === 0) {
                const liveImg = document.getElementById('liveImg');
                const liveBackdrop = document.getElementById('liveBackdrop');
                const liveImgPlaceholder = document.getElementById('liveImgPlaceholder');

                liveImg.src = e.target.result;
                liveImg.classList.remove('d-none');
                liveBackdrop.style.backgroundImage = `url('${e.target.result}')`;
                if (liveImgPlaceholder) liveImgPlaceholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(file);
    }

    window.removeImage = function (slotIndex) {
        const input = document.getElementById(`imageInput${slotIndex}`);
        if (input) input.value = '';

        const previewState = document.getElementById(`previewState${slotIndex}`);
        const previewImg = document.getElementById(`previewImg${slotIndex}`);
        const emptyState = document.querySelector(`#slotWrap${slotIndex} .slot-empty-state`);

        if (previewImg) previewImg.src = '';
        if (previewState) previewState.classList.add('d-none');
        if (emptyState) emptyState.classList.remove('d-none');

        if (slotIndex === 0) {
            const liveImg = document.getElementById('liveImg');
            const liveBackdrop = document.getElementById('liveBackdrop');
            const liveImgPlaceholder = document.getElementById('liveImgPlaceholder');

            if (liveImg) {
                liveImg.src = '';
                liveImg.classList.add('d-none');
            }
            if (liveBackdrop) {
                liveBackdrop.style.backgroundImage = 'none';
            }
            if (liveImgPlaceholder) {
                liveImgPlaceholder.classList.remove('d-none');
            }
        }
    };

    window.appendDescription = function (text) {
        const textarea = document.getElementById('description');
        if (!textarea) return;
        if (textarea.value.trim()) {
            textarea.value = textarea.value.trim() + '\n' + text;
        } else {
            textarea.value = text;
        }
        updatePreview();
    };

    // Form submit loading and mixed validation
    const createForm = document.getElementById('createPostForm');
    const submitBtn = document.getElementById('submitBtn');
    if (createForm && submitBtn) {
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

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Joylanmoqda...';
        });
    }
});
</script>
@endsection
