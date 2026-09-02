@extends('layouts.app')

@section('content')
@php $allImages = $post->allImages(); @endphp

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0" style="font-size:0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('posts.index') }}" class="text-decoration-none text-success">E'lonlar</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($post->title, 40) }}</li>
        </ol>
    </nav>

    <div class="ad-layout">

        {{-- ── LEFT: IMAGE GALLERY ── --}}
        <div class="ad-gallery">
            <div class="gallery-main" id="galleryMain">
                @if(!empty($allImages))
                    <img src="{{ asset('storage/' . $allImages[0]) }}"
                         alt="{{ $post->title }}"
                         class="gallery-main-img" id="mainImg">
                @else
                    <div class="gallery-placeholder">
                        <i class="bi bi-image"></i>
                        <span>Rasm yo'q</span>
                    </div>
                @endif

                @if($post->status === 'reserved')
                    <span class="gallery-status-badge badge-reserved">Rezerv</span>
                @endif
            </div>

            @if(count($allImages) > 1)
                <div class="gallery-thumbs">
                    @foreach($allImages as $i => $img)
                        <button class="thumb-btn {{ $i === 0 ? 'active' : '' }}"
                                onclick="switchImage('{{ asset('storage/' . $img) }}', this)">
                            <img src="{{ asset('storage/' . $img) }}" alt="Rasm {{ $i + 1 }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── RIGHT: DETAILS PANEL ── --}}
        <div class="ad-panel">

            {{-- Title & price --}}
            <div class="panel-header">
                <h1 class="ad-title">{{ $post->title }}</h1>

                <div class="ad-price-row">
                    <span class="ad-price">
                        {{ number_format((float) $post->price, 0, '.', ' ') }}
                        <span class="ad-currency">{{ $post->currency }}</span>
                    </span>
                    @if($post->is_negotiable)
                        <span class="negotiable-badge">Kelishiladi</span>
                    @endif
                </div>
            </div>

            {{-- Seller info --}}
            <div class="seller-row">
                <div class="seller-avatar">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</div>
                <div>
                    <div class="seller-name">{{ $post->user->name }}</div>
                    <div class="seller-date">{{ $post->created_at->format('d.m.Y') }}</div>
                </div>
            </div>

            {{-- Inventory & Stock Banner --}}
            <div class="stock-panel-box mb-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <span class="stock-title">
                        <i class="bi bi-boxes text-lime me-1"></i> Hayvonlar soni va mavjudligi:
                    </span>
                    <span class="badge {{ $post->isSoldOut() ? 'bg-danger' : 'bg-lime-soft text-lime' }} fw-bold px-3 py-1 rounded-pill">
                        {{ $post->isSoldOut() ? 'Sotilgan (Tugagan)' : 'Jami: ' . $post->totalAvailableCount() . ' ta mavjud' }}
                    </span>
                </div>
                <div class="row g-2">
                    @if($post->gender === 'mixed' || ($post->male_quantity > 0 && $post->female_quantity > 0))
                        <div class="col-6">
                            <div class="stock-mini-card {{ $post->availableMaleCount() > 0 ? '' : 'depleted' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-gender-male text-info fs-5"></i>
                                    <div>
                                        <div class="small fw-bold text-cream">Erkak</div>
                                        <div class="stock-subtext {{ $post->availableMaleCount() > 0 ? 'text-lime' : 'text-danger' }}">
                                            {{ $post->availableMaleCount() > 0 ? $post->availableMaleCount() . ' ta mavjud' : 'Tugagan' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stock-mini-card {{ $post->availableFemaleCount() > 0 ? '' : 'depleted' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-gender-female text-danger fs-5"></i>
                                    <div>
                                        <div class="small fw-bold text-cream">Urg'ochi</div>
                                        <div class="stock-subtext {{ $post->availableFemaleCount() > 0 ? 'text-lime' : 'text-danger' }}">
                                            {{ $post->availableFemaleCount() > 0 ? $post->availableFemaleCount() . ' ta mavjud' : 'Tugagan' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($post->availableMaleCount() > 0 || $post->gender === 'male')
                        <div class="col-12">
                            <div class="stock-mini-card {{ $post->availableMaleCount() > 0 ? '' : 'depleted' }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-gender-male text-info fs-5"></i>
                                        <span class="small fw-bold text-cream">Jinsi: Erkak</span>
                                    </div>
                                    <span class="stock-subtext {{ $post->availableMaleCount() > 0 ? 'text-lime' : 'text-danger' }}">
                                        Mavjud: {{ $post->availableMaleCount() }} ta
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="stock-mini-card {{ $post->availableFemaleCount() > 0 ? '' : 'depleted' }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-gender-female text-danger fs-5"></i>
                                        <span class="small fw-bold text-cream">Jinsi: Urg'ochi</span>
                                    </div>
                                    <span class="stock-subtext {{ $post->availableFemaleCount() > 0 ? 'text-lime' : 'text-danger' }}">
                                        Mavjud: {{ $post->availableFemaleCount() }} ta
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info grid --}}
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-tag"></i> Kategoriya</span>
                    <span class="info-val">{{ $post->category?->name ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-award"></i> Zot</span>
                    <span class="info-val">{{ $post->breed ?: '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-gender-ambiguous"></i> Jinsi</span>
                    <span class="info-val">
                        @if($post->gender === 'mixed')
                            Aralash (Mixed)
                        @elseif($post->gender === 'female')
                            Urg'ochi ♀
                        @else
                            Erkak ♂
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-box-seam"></i> Jami soni</span>
                    <span class="info-val">{{ $post->totalAvailableCount() }} ta</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-clock"></i> Yoshi</span>
                    <span class="info-val">{{ $post->age ?: '—' }}</span>
                </div>
                @if($post->color)
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-palette"></i> Rangi</span>
                    <span class="info-val">{{ $post->color }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-geo-alt"></i> Manzil</span>
                    <span class="info-val">{{ $post->location ?: '—' }}</span>
                </div>
            </div>

            {{-- Description --}}
            @if($post->description ?? $post->content)
            <div class="ad-desc">
                <div class="ad-desc-label">Tavsif</div>
                <div class="ad-desc-text">{!! nl2br(e($post->description ?? $post->content)) !!}</div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="ad-actions">
                @if(auth()->check() && auth()->user()->hasRole('user'))
                    @if($post->isSoldOut() || $post->status === 'sold')
                        <button class="btn-action btn-sold" disabled>
                            <i class="bi bi-x-circle me-1"></i> Sotilgan
                        </button>
                    @else
                        <button class="btn-action btn-buy"
                                data-bs-toggle="modal" data-bs-target="#buyModalDetail">
                            <i class="bi bi-cart-check me-1"></i> Sotib olish
                        </button>
                    @endif

                    <form action="{{ route('posts.like', $post) }}" method="POST" class="d-contents">
                        @csrf
                        <button type="submit" class="btn-action btn-like {{ $isLiked ? 'liked' : '' }}">
                            <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }} me-1"></i>
                            {{ $isLiked ? 'Yoqtirilgan' : 'Yoqtirish' }}
                            <span class="ms-1 opacity-75">({{ $post->liked_by_users_count }})</span>
                        </button>
                    </form>
                @endif

                @if(auth()->check() && auth()->user()->can('edit posts'))
                    <a href="{{ route('posts.edit', $post) }}" class="btn-action btn-edit">
                        <i class="bi bi-pencil me-1"></i> Tahrirlash
                    </a>
                @endif

                @if($chat)
                    @php $unread = $chat->unreadCountFor(auth()->id()); @endphp
                    <a href="{{ route('chats.show', $chat) }}" class="btn-action btn-chat">
                        <i class="bi bi-chat-dots me-1"></i> Chatga o'tish
                        @if($unread > 0)
                            <span class="ms-1 badge bg-white text-success" style="font-size:0.65rem;">{{ $unread }}</span>
                        @endif
                    </a>
                @endif
            </div>

            <a href="{{ route('posts.index') }}" class="back-link">
                <i class="bi bi-arrow-left me-1"></i> Barcha e'lonlar
            </a>
        </div>
    </div>
</div>

{{-- Buy Modal with Gender and Quantity Selection --}}
@if(auth()->check() && auth()->user()->hasRole('user') && ! $post->isSoldOut())
    <div class="modal fade" id="buyModalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg modal-buy-custom">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-cream">
                        <i class="bi bi-cart-check-fill text-lime me-2"></i> Sotib olish so'rovi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('purchase-requests.store') }}" method="POST" id="buyerPurchaseForm">
                    @csrf
                    <input type="hidden" name="animal_id" value="{{ $post->id }}">
                    <div class="modal-body p-4">
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
                            @php
                                $maleAvail = $post->availableMaleCount();
                                $femaleAvail = $post->availableFemaleCount();
                                $defaultGender = $maleAvail > 0 ? 'male' : ($femaleAvail > 0 ? 'female' : 'male');
                            @endphp
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

                        {{-- Calculation Summary --}}
                        <div class="buyer-total-panel p-3 rounded-3 mb-3">
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
:root {
    --g: #16a34a;
    --g-mid: #15803d;
    --g-soft: #f0fdf4;
    --g-pale: #dcfce7;
    --g-border: #bbf7d0;
    --text: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --border: rgba(15,23,42,0.08);
    --radius: 14px;
    --radius-sm: 9px;
    --shadow: 0 4px 20px rgba(0,0,0,0.07);
}

/* ── LAYOUT ── */
.ad-layout {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 24px;
    align-items: start;
}

@media (max-width: 900px) {
    .ad-layout { grid-template-columns: 1fr; }
}

/* ── GALLERY ── */
.ad-gallery { position: sticky; top: 80px; }

.gallery-main {
    position: relative;
    border-radius: var(--radius);
    overflow: hidden;
    background: #081209;
    border: 1px solid rgba(26, 43, 28, 0.8);
    height: 460px;
    min-height: 360px;
    max-height: 540px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.gallery-main-img {
    width: 100%;
    height: 100%;
    max-height: 540px;
    object-fit: contain;
    display: block;
    transition: opacity 0.2s;
}

@media (max-width: 768px) {
    .gallery-main {
        height: 320px;
        min-height: 260px;
    }
}

.gallery-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: var(--text-3);
    font-size: 0.9rem;
}
.gallery-placeholder i { font-size: 2.5rem; }

.gallery-status-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 100px;
}
.badge-reserved {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fde68a;
}

.gallery-thumbs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.thumb-btn {
    width: 72px;
    height: 72px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.1);
    background: #081209;
    padding: 0;
    cursor: pointer;
    transition: all 0.15s;
    flex-shrink: 0;
}
.thumb-btn img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
.thumb-btn.active { border-color: #16a34a; box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.3); }
.thumb-btn:hover { border-color: #86efac; }

/* ── PANEL ── */
.ad-panel {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    box-shadow: var(--shadow);
}

.panel-header { display: flex; flex-direction: column; gap: 8px; }

.ad-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text);
    line-height: 1.35;
    margin: 0;
}

.ad-price-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.ad-price {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--g);
    line-height: 1;
}
.ad-currency { font-size: 1rem; font-weight: 600; }

.negotiable-badge {
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--g-soft);
    color: var(--g-mid);
    border: 1px solid var(--g-border);
    padding: 3px 10px;
    border-radius: 100px;
}

/* Seller */
.seller-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: var(--g-soft);
    border-radius: var(--radius-sm);
}
.seller-avatar {
    width: 36px;
    height: 36px;
    background: var(--g);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
    flex-shrink: 0;
}
.seller-name { font-size: 0.875rem; font-weight: 600; color: var(--text); }
.seller-date { font-size: 0.75rem; color: var(--text-3); }

/* Info grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 10px 12px;
    background: #f8fafc;
    border-radius: var(--radius-sm);
}
.info-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-3);
}
.info-label i { margin-right: 4px; }
.info-val { font-size: 0.875rem; font-weight: 600; color: var(--text); }

/* Description */
.ad-desc-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-3);
    margin-bottom: 6px;
}
.ad-desc-text {
    font-size: 0.9rem;
    color: var(--text-2);
    line-height: 1.65;
    max-height: 130px;
    overflow-y: auto;
    padding-right: 4px;
}

/* Actions */
.ad-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.d-contents { display: contents; }

.btn-action {
    width: 100%;
    height: 44px;
    border-radius: 100px;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: inherit;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    text-decoration: none;
}

.btn-buy    { background: var(--g); color: white; }
.btn-buy:hover { background: var(--g-mid); }

.btn-pending { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; cursor: not-allowed; }
.btn-sold    { background: #f1f5f9; color: var(--text-3); cursor: not-allowed; }

.btn-like { background: #fff0f3; color: #e11d48; border: 1px solid rgba(225,29,72,0.15); }
.btn-like:hover, .btn-like.liked { background: #e11d48; color: white; border-color: #e11d48; }

.btn-edit { background: #f8fafc; color: var(--text-2); border: 1px solid var(--border); }
.btn-edit:hover { background: #e2e8f0; color: var(--text); }

.btn-chat { background: var(--g-soft); color: var(--g); border: 1px solid var(--g-border); }
.btn-chat:hover { background: var(--g-pale); }

.back-link {
    font-size: 0.82rem;
    color: var(--text-3);
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: color 0.15s;
    margin-top: -4px;
}
.back-link:hover { color: var(--g); }

/* Stock Banner & Mini Cards */
.stock-panel-box {
    background: #0d180e;
    border: 1px solid #1a2b1c;
    border-radius: 12px;
    padding: 14px;
}
.stock-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: #eee9de;
}
.stock-mini-card {
    background: #142416;
    border: 1px solid #1e3621;
    border-radius: 10px;
    padding: 10px 14px;
    transition: all 0.2s ease;
}
.stock-mini-card.depleted {
    opacity: 0.5;
    background: #1a1616;
    border-color: #3b1e1e;
}
.stock-subtext {
    font-size: 0.76rem;
    font-weight: 600;
}
.bg-lime-soft { background: rgba(194, 240, 60, 0.12) !important; }
.text-lime { color: #c2f03c !important; }
.text-cream { color: #eee9de !important; }

/* Buyer Purchase Modal */
.modal-buy-custom {
    background: #0d180e !important;
    border: 1px solid #1a2b1c !important;
    color: #eee9de;
}
.post-summary-card {
    background: #142416;
    border: 1px solid #1e3621;
    border-radius: 12px;
    padding: 12px 14px;
}
.buyer-gender-card {
    display: block;
    cursor: pointer;
    margin: 0;
}
.buyer-gender-card .gender-card-inner {
    border: 1.5px solid #1a2b1c;
    background: #142416;
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
    border-color: #c2f03c;
    background: rgba(194, 240, 60, 0.12);
    box-shadow: 0 0 12px rgba(194, 240, 60, 0.2);
}
.buyer-gender-card.disabled {
    cursor: not-allowed;
    opacity: 0.45;
}
.buyer-gender-card .gender-name {
    font-weight: 700;
    font-size: 0.88rem;
    color: #eee9de;
}
.buyer-gender-card .gender-stock {
    font-size: 0.72rem;
    font-weight: 600;
}

/* Stepper */
.buyer-quantity-stepper {
    display: flex;
    align-items: center;
    background: #142416;
    border: 1.5px solid #1a2b1c;
    border-radius: 12px;
    overflow: hidden;
    height: 48px;
}
.btn-stepper {
    width: 48px;
    height: 100%;
    border: none;
    background: transparent;
    color: #c2f03c;
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
    opacity: 0.3;
    cursor: not-allowed;
}
.buyer-qty-input {
    border: none !important;
    background: transparent !important;
    color: #eee9de !important;
    font-weight: 800;
    font-size: 1.1rem;
    height: 100%;
    box-shadow: none !important;
}

/* Total Panel */
.buyer-total-panel {
    background: #081209;
    border: 1px solid #1a2b1c;
}
.btn-buy-confirm {
    background: #c2f03c;
    color: #060d07;
    font-weight: 700;
    border: none;
    transition: all 0.2s ease;
}
.btn-buy-confirm:hover {
    background: #d4f564;
    color: #060d07;
    transform: translateY(-1px);
}
.btn-buy-confirm:disabled {
    background: #475569;
    color: #94a3b8;
    cursor: not-allowed;
    transform: none;
}

/* Breadcrumb arrow */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    font-size: 1rem;
    vertical-align: middle;
}
</style>

<script>
function switchImage(src, btn) {
    document.getElementById('mainImg').style.opacity = '0';
    setTimeout(() => {
        document.getElementById('mainImg').src = src;
        document.getElementById('mainImg').style.opacity = '1';
    }, 120);
    document.querySelectorAll('.thumb-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
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
