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
                    <span class="info-val">{{ $post->gender === 'female' ? 'Urg\'ochi' : ($post->gender === 'male' ? 'Erkak' : '—') }}</span>
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
                    @if($post->status === 'sold')
                        <button class="btn-action btn-sold" disabled>
                            <i class="bi bi-x-circle me-1"></i> Sotilgan
                        </button>
                    @elseif($hasPurchaseRequest)
                        <button class="btn-action btn-pending" disabled>
                            <i class="bi bi-hourglass-split me-1"></i> So'rov yuborilgan
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
            </div>

            <a href="{{ route('posts.index') }}" class="back-link">
                <i class="bi bi-arrow-left me-1"></i> Barcha e'lonlar
            </a>
        </div>
    </div>
</div>

{{-- Buy Modal --}}
@if(auth()->check() && auth()->user()->hasRole('user') && $post->status !== 'sold' && ! $hasPurchaseRequest)
    <div class="modal fade" id="buyModalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="modal-icon mb-3"><i class="bi bi-cart-check"></i></div>
                    <h5 class="fw-bold mb-2">Tasdiqlash</h5>
                    <p class="text-muted small mb-4">
                        <strong>{{ $post->title }}</strong> uchun so'rov yuborilsinmi?
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Yo'q</button>
                        <form action="{{ route('purchase-requests.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="animal_id" value="{{ $post->id }}">
                            <button type="submit" class="btn btn-success rounded-pill px-4">Ha, yuborish</button>
                        </form>
                    </div>
                </div>
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
    background: #f1f5f9;
    aspect-ratio: 4/3;
    margin-bottom: 8px;
}

.gallery-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: opacity 0.2s;
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
}

.thumb-btn {
    width: 72px;
    height: 72px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid transparent;
    padding: 0;
    cursor: pointer;
    transition: border-color 0.15s;
    flex-shrink: 0;
}
.thumb-btn img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-btn.active { border-color: var(--g); }
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

/* Modal */
.modal-icon {
    width: 52px;
    height: 52px;
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 14px;
    color: var(--g);
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
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
</script>
@endsection
