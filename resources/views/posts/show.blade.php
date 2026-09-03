@extends('layouts.app')

@section('content')
@php
    $allImages = $post->allImages();
    $maleAvail = $post->availableMaleCount();
    $femaleAvail = $post->availableFemaleCount();
    $defaultGender = $maleAvail > 0 ? 'male' : ($femaleAvail > 0 ? 'female' : 'male');
@endphp

<div class="container py-4 post-detail-container">

    {{-- ── TOP NAVIGATION & BREADCRUMB BAR ── --}}
    <div class="detail-topbar mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <a href="{{ route('posts.index') }}" class="btn-back-crumb">
                    <i class="bi bi-arrow-left me-1"></i> Barcha e'lonlar
                </a>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('posts.index') }}">E'lonlar</a></li>
                        @if($post->category)
                            <li class="breadcrumb-item"><a href="{{ route('posts.index', ['category_id' => $post->category_id]) }}">{{ $post->category->name }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($post->title, 32) }}</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge-post-meta">
                    <i class="bi bi-hash text-lime"></i> ID: {{ $post->id }}
                </span>
                <span class="badge-post-meta">
                    <i class="bi bi-clock me-1 text-lime"></i> {{ $post->created_at->diffForHumans() }}
                </span>
                <span class="badge-post-meta d-none d-sm-inline-flex">
                    <i class="bi bi-heart-fill me-1 text-orange"></i> {{ $post->liked_by_users_count }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── AI MODERATION STATUS BANNER FOR SELLER ── --}}
    @if(auth()->check() && ((int) auth()->id() === (int) $post->user_id || auth()->user()->hasRole('admin')))
        @if($post->moderation_status === 'rejected')
            <div class="alert alert-danger border-danger border-opacity-50 rounded-4 p-4 mb-4 shadow-sm" style="background: rgba(220, 53, 69, 0.08);">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle p-2 bg-danger bg-opacity-25 text-danger flex-shrink-0">
                        <i class="bi bi-shield-x fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <h5 class="text-danger fw-bold mb-0">
                                <i class="bi bi-robot me-1"></i> E'lon Groq AI moderatsiyasidan o'tmadi
                            </h5>
                            <span class="badge bg-danger text-white px-2 py-1">Ommaga ko'rsatilmaydi</span>
                        </div>
                        <p class="text-cream mb-2 small">
                            Ushbu e'lon yoki yuklangan fotosuratlar hayvonlar xavfsizligi, taqiqlangan turlar yoki sifat qoidalariga mos kelmadi.
                        </p>
                        <div class="p-3 rounded-3 mb-3" style="background: rgba(0, 0, 0, 0.35); border-left: 3px solid #dc3545;">
                            <strong class="text-danger d-block small mb-1">
                                <i class="bi bi-info-circle-fill me-1"></i> Rad etilish sababi:
                            </strong>
                            <span class="text-cream">{{ $post->moderation_reason ?? 'Tavsif yoki rasm xavfsizlik talablariga mos kelmadi.' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-pencil-square me-1"></i> E'lonni tahrirlash va qayta topshirish
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($post->moderation_status === 'pending')
            <div class="alert alert-warning border-warning border-opacity-50 rounded-4 p-3 mb-4 shadow-sm" style="background: rgba(255, 193, 7, 0.08);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 bg-warning bg-opacity-25 text-warning flex-shrink-0">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-warning fw-bold mb-0">
                            <i class="bi bi-robot me-1"></i> E'lon AI moderatsiyasida ko'rib chiqilmoqda
                        </h6>
                        <small class="text-cream-50">
                            Groq AI tomonidan matn va rasmlar tekshirilmoqda. Tasdiqlangach, e'lon avtomatik ravishda umumiy marketplace'da barchaga ko'rinadi.
                        </small>
                    </div>
                    <span class="badge bg-warning text-dark px-2 py-1">Kutilmoqda</span>
                </div>
            </div>
        @endif
    @endif

    {{-- ── MAIN DETAIL LAYOUT (2 COLUMNS) ── --}}
    <div class="row g-4">

        {{-- ── LEFT COLUMN: GALLERY, DESCRIPTION & TRUST ── --}}
        <div class="col-lg-7 col-xl-7">

            {{-- Gallery Card --}}
            <div class="gallery-card-shell mb-4">
                <div class="gallery-main-view" id="galleryMainView">
                    @if(!empty($allImages))
                        <div class="gallery-ambient-backdrop" id="ambientBackdrop" style="background-image: url('{{ route('images.show', ['path' => $allImages[0]]) }}');"></div>
                        <img src="{{ route('images.show', ['path' => $allImages[0]]) }}"
                             alt="{{ $post->title }}"
                             class="gallery-main-img" id="mainImg">
                    @else
                        <div class="gallery-placeholder">
                            <i class="bi bi-image"></i>
                            <span>Rasm yuklanmagan</span>
                        </div>
                    @endif

                    {{-- Floating Status & Category Badges on Image --}}
                    <div class="gallery-overlay-top">
                        <div class="d-flex align-items-center gap-2">
                            <span class="gallery-glass-pill pill-cat">
                                <i class="bi bi-tag-fill text-lime me-1"></i> {{ $post->category?->name ?? 'Hayvon' }}
                            </span>
                            @if(auth()->check() && ((int) auth()->id() === (int) $post->user_id || auth()->user()->hasRole('admin')))
                                @if($post->moderation_status === 'approved')
                                    <span class="gallery-glass-pill text-success border border-success border-opacity-50">
                                        <i class="bi bi-shield-check me-1"></i> Tasdiqlangan
                                    </span>
                                @elseif($post->moderation_status === 'rejected')
                                    <span class="gallery-glass-pill text-danger border border-danger border-opacity-50">
                                        <i class="bi bi-shield-x me-1"></i> Rad etilgan
                                    </span>
                                @else
                                    <span class="gallery-glass-pill text-warning border border-warning border-opacity-50">
                                        <i class="bi bi-clock me-1"></i> AI tekshiruvida
                                    </span>
                                @endif
                            @endif
                            @if($post->isSoldOut())
                                <span class="gallery-glass-pill pill-sold">
                                    <i class="bi bi-x-circle-fill me-1"></i> Sotilgan
                                </span>
                            @elseif($post->status === 'reserved')
                                <span class="gallery-glass-pill pill-reserved">
                                    <i class="bi bi-hourglass-split me-1"></i> Rezerv qilingan
                                </span>
                            @else
                                <span class="gallery-glass-pill pill-active">
                                    <i class="bi bi-check-circle-fill text-lime me-1"></i> Sotuvda faol
                                </span>
                            @endif
                        </div>

                        @if(!empty($allImages))
                            <span class="gallery-glass-pill pill-counter">
                                <i class="bi bi-camera-fill text-lime me-1"></i>
                                <span id="galleryCounter">1 / {{ count($allImages) }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Thumbnails strip --}}
                @if(count($allImages) > 1)
                    <div class="gallery-thumbnails-strip">
                        @foreach($allImages as $i => $img)
                            <button type="button" class="gallery-thumb-btn {{ $i === 0 ? 'active' : '' }}"
                                    onclick="switchImage('{{ route('images.show', ['path' => $img]) }}', this, {{ $i + 1 }})">
                                <img src="{{ route('images.show', ['path' => $img]) }}" alt="Rasm {{ $i + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Description Section Card --}}
            <div class="detail-section-card mb-4">
                <div class="section-card-title">
                    <i class="bi bi-card-text text-lime me-2"></i> E'lon tavsifi
                </div>
                <div class="section-desc-body">
                    @if($post->description ?? $post->content)
                        <div class="ad-full-text">
                            {!! nl2br(e($post->description ?? $post->content)) !!}
                        </div>
                    @else
                        <p class="text-muted fst-italic mb-0">Sotuvchi ushbu e'lon uchun alohida tavsif qoldirmagan.</p>
                    @endif
                </div>
            </div>

            {{-- Trust & Safe Marketplace Notice --}}
            <div class="detail-section-card mb-4">
                <div class="section-card-title">
                    <i class="bi bi-shield-check text-lime me-2"></i> Xavfsiz xarid qoidalari
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="trust-mini-feature">
                            <div class="trust-icon-box"><i class="bi bi-camera-video"></i></div>
                            <h6>Jonli ko'rik</h6>
                            <p>Xarid qilishdan avval videochat orqali hayvonning sog'lig'i va holatini ko'ring.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="trust-mini-feature">
                            <div class="trust-icon-box"><i class="bi bi-chat-heart"></i></div>
                            <h6>To'g'ridan-to'g'ri aloqa</h6>
                            <p>Vositachilarsiz bevosita sotuvchi bilan chat yoki telefon orqali bog'laning.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="trust-mini-feature">
                            <div class="trust-icon-box"><i class="bi bi-patch-check"></i></div>
                            <h6>Xavfsiz to'lov</h6>
                            <p>To'lovni hayvonni o'z ko'zingiz bilan ko'rib, qabul qilganingizdan so'ng to'lang.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── RIGHT COLUMN: STICKY PURCHASE & SELLER PANEL ── --}}
        <div class="col-lg-5 col-xl-5">
            <div class="sticky-detail-sidebar">

                {{-- Price & Title Box --}}
                <div class="detail-panel-box mb-3">
                    <div class="panel-price-header">
                        <div class="price-val-wrap">
                            <div class="ad-price-display">
                                {{ number_format((float) $post->price, 0, '.', ' ') }}
                                <small class="ad-price-unit">{{ $post->currency }}</small>
                            </div>
                            @if($post->is_negotiable)
                                <span class="badge-negotiable-glow">
                                    <i class="bi bi-check2-circle me-1"></i> Kelishiladi
                                </span>
                            @endif
                        </div>
                        <div class="price-sub-hint text-muted small">
                            Bir dona hayvon uchun ko'rsatilgan narx
                        </div>
                    </div>

                    <h1 class="ad-main-title">{{ $post->title }}</h1>

                    <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                        @if($post->breed)
                            <span class="detail-chip">
                                <i class="bi bi-award text-lime me-1"></i> {{ $post->breed }}
                            </span>
                        @endif
                        @if($post->location)
                            <span class="detail-chip">
                                <i class="bi bi-geo-alt text-orange me-1"></i> {{ $post->location }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Live Stock & Gender Breakdown Card --}}
                <div class="detail-panel-box mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="panel-section-label">
                            <i class="bi bi-boxes text-lime me-1"></i> Zaxira va jins taqsimoti:
                        </span>
                        <span class="stock-total-badge {{ $post->isSoldOut() ? 'sold-badge' : '' }}">
                            {{ $post->isSoldOut() ? 'Tugagan (Sold out)' : 'Jami: ' . $post->totalAvailableCount() . ' ta mavjud' }}
                        </span>
                    </div>

                    <div class="row g-2">
                        @if($post->gender === 'mixed' || ($post->male_quantity > 0 && $post->female_quantity > 0))
                            <div class="col-6">
                                <div class="stock-breakdown-card {{ $maleAvail > 0 ? '' : 'depleted' }}">
                                    <div class="stock-card-icon male-icon"><i class="bi bi-gender-male"></i></div>
                                    <div>
                                        <div class="stock-card-title">Erkak ♂</div>
                                        <div class="stock-card-status {{ $maleAvail > 0 ? 'text-lime' : 'text-danger' }}">
                                            {{ $maleAvail > 0 ? $maleAvail . ' ta mavjud' : 'Tugagan' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stock-breakdown-card {{ $femaleAvail > 0 ? '' : 'depleted' }}">
                                    <div class="stock-card-icon female-icon"><i class="bi bi-gender-female"></i></div>
                                    <div>
                                        <div class="stock-card-title">Urg'ochi ♀</div>
                                        <div class="stock-card-status {{ $femaleAvail > 0 ? 'text-lime' : 'text-danger' }}">
                                            {{ $femaleAvail > 0 ? $femaleAvail . ' ta mavjud' : 'Tugagan' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($maleAvail > 0 || $post->gender === 'male')
                            <div class="col-12">
                                <div class="stock-breakdown-card {{ $maleAvail > 0 ? '' : 'depleted' }}">
                                    <div class="stock-card-icon male-icon"><i class="bi bi-gender-male"></i></div>
                                    <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                        <div class="stock-card-title">Jinsi: Erkak ♂</div>
                                        <div class="stock-card-status {{ $maleAvail > 0 ? 'text-lime' : 'text-danger' }}">
                                            Mavjud: {{ $maleAvail }} ta
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-12">
                                <div class="stock-breakdown-card {{ $femaleAvail > 0 ? '' : 'depleted' }}">
                                    <div class="stock-card-icon female-icon"><i class="bi bi-gender-female"></i></div>
                                    <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                        <div class="stock-card-title">Jinsi: Urg'ochi ♀</div>
                                        <div class="stock-card-status {{ $femaleAvail > 0 ? 'text-lime' : 'text-danger' }}">
                                            Mavjud: {{ $femaleAvail }} ta
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Key Specifications Grid --}}
                <div class="detail-panel-box mb-3">
                    <span class="panel-section-label mb-2 d-block">
                        <i class="bi bi-ui-checks-grid text-lime me-1"></i> Asosiy xususiyatlar:
                    </span>
                    <div class="specs-grid">
                        <div class="spec-cell">
                            <span class="spec-label"><i class="bi bi-tag text-muted me-1"></i> Kategoriya</span>
                            <span class="spec-val">{{ $post->category?->name ?? '—' }}</span>
                        </div>
                        <div class="spec-cell">
                            <span class="spec-label"><i class="bi bi-award text-muted me-1"></i> Zot</span>
                            <span class="spec-val">{{ $post->breed ?: '—' }}</span>
                        </div>
                        <div class="spec-cell">
                            <span class="spec-label"><i class="bi bi-gender-ambiguous text-muted me-1"></i> Jinsi</span>
                            <span class="spec-val">
                                @if($post->gender === 'mixed')
                                    Aralash (Mixed)
                                @elseif($post->gender === 'female')
                                    Urg'ochi ♀
                                @else
                                    Erkak ♂
                                @endif
                            </span>
                        </div>
                        <div class="spec-cell">
                            <span class="spec-label"><i class="bi bi-calendar3 text-muted me-1"></i> Yoshi</span>
                            <span class="spec-val">{{ $post->age ?: '—' }}</span>
                        </div>
                        <div class="spec-cell">
                            <span class="spec-label"><i class="bi bi-palette text-muted me-1"></i> Rangi</span>
                            <span class="spec-val">{{ $post->color ?: '—' }}</span>
                        </div>
                        <div class="spec-cell">
                            <span class="spec-label"><i class="bi bi-geo-alt text-muted me-1"></i> Manzil</span>
                            <span class="spec-val">{{ $post->location ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Seller Profile Trust Card --}}
                <div class="detail-panel-box mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="seller-avatar-box">
                            {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="seller-name-title mb-0">{{ $post->user->name }}</h6>
                                <span class="badge-seller-tag">Sotuvchi</span>
                            </div>
                            <div class="seller-meta-text">
                                Ro'yxatdan o'tgan: {{ $post->user->created_at->format('d.m.Y') }}
                            </div>
                        </div>
                    </div>

                    @if($post->user->phone || $post->user->telegram_username)
                        <div class="seller-contact-links mt-3 pt-3 border-top border-line">
                            @if($post->user->phone)
                                <a href="tel:{{ $post->user->phone }}" class="seller-contact-btn btn-call">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span>{{ $post->user->phone }}</span>
                                </a>
                            @endif
                            @if($post->user->telegram_username)
                                <a href="https://t.me/{{ ltrim($post->user->telegram_username, '@') }}" target="_blank" class="seller-contact-btn btn-tg">
                                    <i class="bi bi-telegram"></i>
                                    <span>Telegram</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="detail-actions-box">
                    @if(auth()->check() && auth()->user()->hasRole('user'))
                        @if($post->isSoldOut() || $post->status === 'sold')
                            <button class="btn-action-main btn-main-sold" disabled>
                                <i class="bi bi-x-circle-fill me-2"></i> E'lon sotilgan (Mavjud emas)
                            </button>
                        @else
                            <button class="btn-action-main btn-main-buy" data-bs-toggle="modal" data-bs-target="#buyModalDetail">
                                <i class="bi bi-cart-check-fill me-2"></i> Sotib olish so'rovini yuborish
                            </button>
                        @endif

                        <div class="action-secondary-row mt-2">
                            <form action="{{ route('posts.like', $post) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn-action-sub btn-sub-like {{ $isLiked ? 'liked' : '' }}">
                                    <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }} me-1"></i>
                                    <span>{{ $isLiked ? 'Yoqtirilgan' : 'Yoqtirish' }}</span>
                                    <span class="like-counter">({{ $post->liked_by_users_count }})</span>
                                </button>
                            </form>

                            @if($chat)
                                @php $unread = $chat->unreadCountFor(auth()->id()); @endphp
                                <a href="{{ route('chats.show', $chat) }}" class="btn-action-sub btn-sub-chat">
                                    <i class="bi bi-chat-dots-fill me-1"></i>
                                    <span>Chat</span>
                                    @if($unread > 0)
                                        <span class="badge bg-lime text-ink ms-1">{{ $unread }}</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    @endif

                    @if(auth()->check() && (auth()->user()->can('edit posts') || auth()->id() === $post->user_id))
                        @if(! $post->isArchived())
                            <div class="mt-2">
                                <a href="{{ route('posts.edit', $post) }}" class="btn-action-sub btn-sub-edit w-100">
                                    <i class="bi bi-pencil-square me-1"></i> E'lonni tahrirlash
                                </a>
                            </div>
                        @else
                            <div class="mt-2 text-center p-2 rounded-3 bg-panel-soft border border-line">
                                <span class="text-muted small">
                                    <i class="bi bi-lock-fill me-1"></i> Ushbu e'lon arxivlangan / sotilgan (Read-only)
                                </span>
                            </div>
                        @endif
                    @endif

                    @guest
                        <div class="guest-action-card text-center p-3 rounded-3 mt-2">
                            <div class="small text-muted mb-2">Hayvonni sotib olish yoki sotuvchi bilan bog'lanish uchun tizimga kiring:</div>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">Kirish</a>
                                <a href="{{ route('register') }}" class="btn btn-sm btn-success rounded-pill px-3">Ro'yxatdan o'tish</a>
                            </div>
                        </div>
                    @endguest
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ── BUY MODAL WITH DYNAMIC GENDER & QUANTITY SELECTION ── --}}
@if(auth()->check() && auth()->user()->hasRole('user') && ! $post->isSoldOut())
    <div class="modal fade" id="buyModalDetail" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg modal-buy-custom">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-cream" id="buyModalLabel">
                        <i class="bi bi-cart-check-fill text-lime me-2"></i> Sotib olish so'rovi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Yopish"></button>
                </div>
                <form action="{{ route('purchase-requests.store') }}" method="POST" id="buyerPurchaseForm">
                    @csrf
                    <input type="hidden" name="animal_id" value="{{ $post->id }}">
                    <div class="modal-body p-4">

                        {{-- Post Summary Card --}}
                        <div class="post-summary-card mb-3">
                            <div class="fw-bold text-cream">{{ $post->title }}</div>
                            <div class="small text-muted">{{ $post->breed }} &bull; {{ $post->location }}</div>
                            <div class="text-lime fw-bold mt-1">
                                {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                                <span class="small text-muted fw-normal">/ 1 ta uchun</span>
                            </div>
                        </div>

                        {{-- Step 1: Gender Selection --}}
                        <div class="mb-3">
                            <label class="form-label-custom small fw-bold text-cream d-block mb-2">
                                1. Hayvon jinsini tanlang:
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="buyer-gender-card {{ $maleAvail <= 0 ? 'disabled' : '' }}" for="buyerGenderMale">
                                        <input type="radio" name="gender" value="male" class="d-none"
                                               id="buyerGenderMale"
                                               data-available="{{ $maleAvail }}"
                                               @checked($defaultGender === 'male')
                                               @disabled($maleAvail <= 0) required>
                                        <div class="gender-card-inner">
                                            <i class="bi bi-gender-male text-info fs-5"></i>
                                            <span class="gender-name">Erkak</span>
                                            <span class="gender-stock {{ $maleAvail > 0 ? 'text-lime' : 'text-danger' }}">
                                                {{ $maleAvail > 0 ? $maleAvail . ' ta mavjud' : 'Tugagan' }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="buyer-gender-card {{ $femaleAvail <= 0 ? 'disabled' : '' }}" for="buyerGenderFemale">
                                        <input type="radio" name="gender" value="female" class="d-none"
                                               id="buyerGenderFemale"
                                               data-available="{{ $femaleAvail }}"
                                               @checked($defaultGender === 'female')
                                               @disabled($femaleAvail <= 0) required>
                                        <div class="gender-card-inner">
                                            <i class="bi bi-gender-female text-danger fs-5"></i>
                                            <span class="gender-name">Urg'ochi</span>
                                            <span class="gender-stock {{ $femaleAvail > 0 ? 'text-lime' : 'text-danger' }}">
                                                {{ $femaleAvail > 0 ? $femaleAvail . ' ta mavjud' : 'Tugagan' }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Quantity Selection --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="buyerQuantityInput" class="form-label-custom small fw-bold text-cream mb-0">
                                    2. Miqdorni tanlang:
                                </label>
                                <span class="small text-muted" id="buyerAvailHint">
                                    Mavjud: <strong class="text-lime" id="buyerMaxCount">{{ $defaultGender === 'male' ? $maleAvail : $femaleAvail }}</strong> ta
                                </span>
                            </div>
                            <div class="buyer-quantity-stepper">
                                <button type="button" class="btn-stepper" id="btnBuyerMinus"><i class="bi bi-dash-lg"></i></button>
                                <input type="number" name="quantity" id="buyerQuantityInput"
                                       class="form-control text-center buyer-qty-input"
                                       value="1" min="1" max="{{ $defaultGender === 'male' ? $maleAvail : $femaleAvail }}" required>
                                <button type="button" class="btn-stepper" id="btnBuyerPlus"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>

                        {{-- Calculation Summary Panel --}}
                        <div class="buyer-total-panel p-3 rounded-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">Jami to'lov:</span>
                                <span class="fw-bold text-lime fs-5" id="buyerTotalPrice">
                                    {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                                </span>
                            </div>
                            <div class="small text-muted" id="buyerSummaryText">
                                1 ta &times; {{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1" data-bs-dismiss="modal">Bekor qilish</button>
                        <button type="submit" class="btn btn-buy-confirm rounded-pill px-4 flex-grow-1" id="btnSubmitOrder">
                            <i class="bi bi-send-check me-1"></i> So'rov yuborish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
/* ── DETAIL PAGE STYLING (DARK LUXURY THEME) ── */
.post-detail-container {
    max-width: 1240px;
}

/* Topbar & Breadcrumb */
.detail-topbar {
    background: rgba(13, 24, 14, 0.75);
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 10px 16px;
    backdrop-filter: blur(12px);
}
.btn-back-crumb {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--line);
    border-radius: 999px;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.btn-back-crumb:hover {
    color: var(--lime);
    border-color: var(--lime);
    background: rgba(194, 240, 60, 0.08);
    transform: translateX(-2px);
}
.breadcrumb {
    font-size: 0.8rem;
}
.breadcrumb-item a {
    color: var(--muted);
    text-decoration: none;
    transition: color 0.15s;
}
.breadcrumb-item a:hover {
    color: var(--lime);
}
.breadcrumb-item.active {
    color: var(--cream);
    font-weight: 600;
}
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--muted);
    font-size: 0.95rem;
    padding: 0 6px;
}
.badge-post-meta {
    display: inline-flex;
    align-items: center;
    font-size: 0.72rem;
    color: var(--muted);
    background: rgba(255, 255, 255, 0.035);
    border: 1px solid rgba(255, 255, 255, 0.06);
    padding: 3px 9px;
    border-radius: 999px;
}

/* Gallery Shell */
.gallery-card-shell {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 20px;
    overflow: hidden;
    padding: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.gallery-main-view {
    height: 460px;
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: #040804;
    display: flex;
    align-items: center;
    justify-content: center;
}
.gallery-ambient-backdrop {
    position: absolute;
    inset: -14px;
    background-size: cover;
    background-position: center;
    filter: blur(24px) brightness(0.24) saturate(1.4);
    opacity: 0.9;
    transform: scale(1.15);
    pointer-events: none;
    transition: background-image 0.25s ease;
}
.gallery-main-img {
    position: relative;
    z-index: 2;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: opacity 0.2s ease, transform 0.3s ease;
}
.gallery-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: var(--muted);
    font-size: 0.9rem;
}
.gallery-placeholder i {
    font-size: 2.8rem;
    color: var(--line);
}

.gallery-overlay-top {
    position: absolute;
    top: 14px;
    left: 14px;
    right: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 3;
    pointer-events: none;
}
.gallery-glass-pill {
    display: inline-flex;
    align-items: center;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 9999px;
    backdrop-filter: blur(12px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
}
.gallery-glass-pill.pill-cat {
    background: rgba(8, 18, 9, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: var(--cream);
}
.gallery-glass-pill.pill-active {
    background: rgba(194, 240, 60, 0.18);
    border: 1px solid rgba(194, 240, 60, 0.5);
    color: var(--lime);
}
.gallery-glass-pill.pill-reserved {
    background: rgba(255, 107, 43, 0.2);
    border: 1px solid rgba(255, 107, 43, 0.5);
    color: var(--orange);
}
.gallery-glass-pill.pill-sold {
    background: rgba(239, 68, 68, 0.25);
    border: 1px solid rgba(239, 68, 68, 0.6);
    color: #ff6b6b;
}
.gallery-glass-pill.pill-counter {
    background: rgba(8, 18, 9, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: var(--cream);
}

.gallery-thumbnails-strip {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    margin-top: 12px;
    padding-bottom: 4px;
    scrollbar-width: thin;
}
.gallery-thumb-btn {
    width: 76px;
    height: 76px;
    border-radius: 12px;
    overflow: hidden;
    background: #081209;
    border: 2px solid var(--line);
    padding: 0;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.gallery-thumb-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.gallery-thumb-btn:hover {
    border-color: rgba(194, 240, 60, 0.5);
    transform: translateY(-2px);
}
.gallery-thumb-btn.active {
    border-color: var(--lime);
    box-shadow: 0 0 14px rgba(194, 240, 60, 0.35);
}

/* Detail Section Cards */
.detail-section-card {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 22px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.section-card-title {
    font-family: var(--serif);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--cream);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
}
.ad-full-text {
    font-size: 0.92rem;
    line-height: 1.7;
    color: rgba(238, 233, 222, 0.88);
}

/* Trust Features */
.trust-mini-feature {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 14px;
    padding: 16px;
    height: 100%;
}
.trust-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(194, 240, 60, 0.12);
    color: var(--lime);
    display: grid;
    place-items: center;
    font-size: 1.15rem;
    margin-bottom: 10px;
}
.trust-mini-feature h6 {
    font-family: var(--serif);
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--cream);
    margin-bottom: 6px;
}
.trust-mini-feature p {
    font-size: 0.76rem;
    color: var(--muted);
    line-height: 1.45;
    margin: 0;
}

/* Right Sticky Sidebar */
.sticky-detail-sidebar {
    position: sticky;
    top: 76px;
}
.detail-panel-box {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}

/* Price Box */
.panel-price-header {
    border-bottom: 1px solid var(--line);
    padding-bottom: 14px;
    margin-bottom: 14px;
}
.price-val-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.ad-price-display {
    font-family: var(--serif);
    font-size: 1.95rem;
    font-weight: 800;
    color: var(--lime);
    letter-spacing: -0.03em;
    line-height: 1.15;
}
.ad-price-unit {
    font-size: 0.95rem;
    font-weight: 600;
    color: rgba(238, 233, 222, 0.75);
}
.badge-negotiable-glow {
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(194, 240, 60, 0.12);
    color: var(--lime);
    border: 1px solid rgba(194, 240, 60, 0.35);
    padding: 3px 10px;
    border-radius: 9999px;
}
.ad-main-title {
    font-family: var(--serif);
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--cream);
    line-height: 1.3;
    margin: 0;
}
.detail-chip {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    color: var(--muted);
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
    padding: 3px 9px;
    border-radius: 8px;
}

/* Stock Card */
.panel-section-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--cream);
}
.stock-total-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(194, 240, 60, 0.14);
    color: var(--lime);
    border: 1px solid rgba(194, 240, 60, 0.3);
}
.stock-total-badge.sold-badge {
    background: rgba(239, 68, 68, 0.15);
    color: #ff6b6b;
    border-color: rgba(239, 68, 68, 0.4);
}
.stock-breakdown-card {
    background: #081209;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s ease;
}
.stock-breakdown-card.depleted {
    opacity: 0.45;
    background: #140d0d;
    border-color: #2b1717;
}
.stock-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.stock-card-icon.male-icon {
    background: rgba(13, 202, 240, 0.12);
    color: #0dcaf0;
}
.stock-card-icon.female-icon {
    background: rgba(220, 53, 69, 0.12);
    color: #dc3545;
}
.stock-card-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--cream);
}
.stock-card-status {
    font-size: 0.72rem;
    font-weight: 600;
}

/* Specs 2x3 Grid */
.specs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.spec-cell {
    background: #081209;
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 9px 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.spec-label {
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.spec-val {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--cream);
}

/* Seller Card */
.seller-avatar-box {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #192b1b;
    border: 1.5px solid rgba(194, 240, 60, 0.4);
    color: var(--lime);
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    font-weight: 700;
    flex-shrink: 0;
}
.seller-name-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--cream);
}
.badge-seller-tag {
    font-size: 0.65rem;
    font-weight: 700;
    background: rgba(194, 240, 60, 0.12);
    color: var(--lime);
    border: 1px solid rgba(194, 240, 60, 0.3);
    padding: 1px 6px;
    border-radius: 999px;
}
.seller-meta-text {
    font-size: 0.72rem;
    color: var(--muted);
}
.seller-contact-links {
    display: flex;
    gap: 8px;
}
.seller-contact-btn {
    flex: 1;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 10px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.seller-contact-btn.btn-call {
    background: rgba(194, 240, 60, 0.12);
    border: 1px solid rgba(194, 240, 60, 0.35);
    color: var(--lime);
}
.seller-contact-btn.btn-call:hover {
    background: var(--lime);
    color: var(--ink);
    font-weight: 700;
}
.seller-contact-btn.btn-tg {
    background: rgba(13, 202, 240, 0.12);
    border: 1px solid rgba(13, 202, 240, 0.35);
    color: #0dcaf0;
}
.seller-contact-btn.btn-tg:hover {
    background: #0dcaf0;
    color: #05161c;
    font-weight: 700;
}

/* Action Buttons */
.detail-actions-box {
    margin-top: 4px;
}
.btn-action-main {
    width: 100%;
    height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-weight: 800;
    font-size: 0.95rem;
    border: none;
    cursor: pointer;
    transition: all 0.22s ease;
}
.btn-main-buy {
    background: var(--lime);
    color: var(--ink);
    box-shadow: 0 6px 20px rgba(194, 240, 60, 0.28);
}
.btn-main-buy:hover {
    background: #d4f564;
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(194, 240, 60, 0.4);
}
.btn-main-sold {
    background: #2b1717;
    color: #ff6b6b;
    border: 1px solid #4a1f1f;
    cursor: not-allowed;
}

.action-secondary-row {
    display: flex;
    gap: 8px;
}
.btn-action-sub {
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--line);
    background: var(--panel);
    color: var(--cream);
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-sub-like {
    width: 100%;
}
.btn-sub-like:hover, .btn-sub-like.liked {
    border-color: var(--orange);
    color: var(--orange);
    background: rgba(255, 107, 43, 0.12);
}
.btn-sub-chat {
    padding: 0 16px;
    background: rgba(194, 240, 60, 0.08);
    border-color: rgba(194, 240, 60, 0.3);
    color: var(--lime);
}
.btn-sub-chat:hover {
    background: rgba(194, 240, 60, 0.18);
    color: var(--lime);
}
.btn-sub-edit {
    background: rgba(255, 255, 255, 0.04);
    color: var(--muted);
}
.btn-sub-edit:hover {
    border-color: var(--cream);
    color: var(--cream);
}
.like-counter {
    opacity: 0.75;
    font-size: 0.76rem;
}
.guest-action-card {
    background: rgba(13, 24, 14, 0.75);
    border: 1px dashed var(--line);
}

/* Buy Modal Customization */
.modal-buy-custom {
    background: #0d180e !important;
    border: 1px solid var(--line) !important;
    color: var(--cream);
    box-shadow: 0 20px 60px rgba(0,0,0,0.8) !important;
}
.post-summary-card {
    background: #081209;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 12px 14px;
}
.buyer-gender-card {
    display: block;
    cursor: pointer;
    margin: 0;
}
.buyer-gender-card .gender-card-inner {
    border: 1.5px solid var(--line);
    background: #081209;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}
.buyer-gender-card input:checked + .gender-card-inner {
    border-color: var(--lime);
    background: rgba(194, 240, 60, 0.12);
    box-shadow: 0 0 12px rgba(194, 240, 60, 0.2);
}
.buyer-gender-card.disabled {
    cursor: not-allowed;
    opacity: 0.4;
}
.buyer-gender-card .gender-name {
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--cream);
}
.buyer-gender-card .gender-stock {
    font-size: 0.72rem;
    font-weight: 600;
}

/* Stepper */
.buyer-quantity-stepper {
    display: flex;
    align-items: center;
    background: #081209;
    border: 1.5px solid var(--line);
    border-radius: 12px;
    overflow: hidden;
    height: 48px;
}
.btn-stepper {
    width: 48px;
    height: 100%;
    border: none;
    background: transparent;
    color: var(--lime);
    font-size: 1.1rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease;
}
.btn-stepper:hover {
    background: rgba(194, 240, 60, 0.15);
}
.btn-stepper:disabled {
    opacity: 0.25;
    cursor: not-allowed;
}
.buyer-qty-input {
    border: none !important;
    background: transparent !important;
    color: var(--cream) !important;
    font-weight: 800;
    font-size: 1.15rem;
    height: 100%;
    box-shadow: none !important;
}

/* Total Panel */
.buyer-total-panel {
    background: #050c06;
    border: 1px solid var(--line);
}
.btn-buy-confirm {
    background: var(--lime);
    color: var(--ink);
    font-weight: 700;
    border: none;
    transition: all 0.2s ease;
}
.btn-buy-confirm:hover {
    background: #d4f564;
    color: var(--ink);
    transform: translateY(-1px);
}
.btn-buy-confirm:disabled {
    background: #475569;
    color: #94a3b8;
    cursor: not-allowed;
    transform: none;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .sticky-detail-sidebar {
        position: static;
        margin-top: 24px;
    }
    .gallery-main-view {
        height: 360px;
    }
    .ad-price-display {
        font-size: 1.65rem;
    }
}
@media (max-width: 575.98px) {
    .gallery-main-view {
        height: 280px;
    }
    .gallery-thumb-btn {
        width: 64px;
        height: 64px;
    }
    .specs-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function switchImage(src, btn, index) {
    const mainImg = document.getElementById('mainImg');
    const ambientBackdrop = document.getElementById('ambientBackdrop');
    const galleryCounter = document.getElementById('galleryCounter');
    const totalCount = {{ max(1, count($allImages)) }};

    if (mainImg) {
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 120);
    }
    if (ambientBackdrop) {
        ambientBackdrop.style.backgroundImage = `url('${src}')`;
    }
    if (galleryCounter && index) {
        galleryCounter.textContent = `${index} / ${totalCount}`;
    }

    document.querySelectorAll('.gallery-thumb-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

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
            btnSubmitOrder.innerHTML = `<i class="bi bi-send-check me-1"></i> So'rov yuborish`;
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
