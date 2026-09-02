@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-soft text-primary fw-bold px-2 py-1 rounded-pill">
                    <i class="bi bi-bag-check-fill me-1"></i> Xaridor
                </span>
                <h1 class="h3 fw-bold mb-0 text-cream font-serif">Mening buyurtmalarim</h1>
            </div>
            <p class="text-muted small mb-0">Siz yuborgan barcha xarid so'rovlari, ularning holati va sotuvchilar bilan aloqa</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('posts.index') }}" class="btn-panel-link">
                <i class="bi bi-compass me-1"></i> E'lonlarni ko'rish
            </a>
            <a href="{{ route('user.profile.show') }}" class="btn-panel-link">
                <i class="bi bi-person me-1"></i> Profil
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-banner mb-4 rounded-3 py-2 px-3 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-lime fs-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="admin-tabs-nav mb-4">
        <a href="{{ route('user.purchase-requests.index') }}" class="admin-tab-item {{ empty($status) ? 'active' : '' }}">
            <span>Barchasi</span>
            <span class="tab-count">{{ $stats['total'] ?? 0 }}</span>
        </a>
        <a href="{{ route('user.purchase-requests.index', ['status' => 'pending']) }}" class="admin-tab-item {{ ($status ?? '') === 'pending' ? 'active' : '' }}">
            <span><i class="bi bi-clock-history me-1 text-orange"></i> Kutilmoqda</span>
            <span class="tab-count {{ ($stats['pending'] ?? 0) > 0 ? 'highlight-orange' : '' }}">{{ $stats['pending'] ?? 0 }}</span>
        </a>
        <a href="{{ route('user.purchase-requests.index', ['status' => 'approved']) }}" class="admin-tab-item {{ ($status ?? '') === 'approved' ? 'active' : '' }}">
            <span><i class="bi bi-check-circle-fill me-1 text-lime"></i> Tasdiqlangan</span>
            <span class="tab-count">{{ $stats['approved'] ?? 0 }}</span>
        </a>
        <a href="{{ route('user.purchase-requests.index', ['status' => 'rejected']) }}" class="admin-tab-item {{ ($status ?? '') === 'rejected' ? 'active' : '' }}">
            <span><i class="bi bi-x-circle-fill me-1 text-muted"></i> Rad etilgan</span>
            <span class="tab-count">{{ $stats['rejected'] ?? 0 }}</span>
        </a>
    </div>

    {{-- Orders List --}}
    <div class="orders-list-wrapper">
        @forelse($requests as $request)
            <div class="order-card {{ $request->status === 'approved' ? 'order-card-approved' : ($request->status === 'pending' ? 'order-card-pending' : '') }}">
                {{-- Order Card Header --}}
                <div class="order-card-header">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="order-id-badge">
                            <i class="bi bi-receipt me-1"></i> #REQ-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="order-date-text">
                            <i class="bi bi-calendar3 me-1"></i> {{ $request->created_at->format('d.m.Y H:i') }}
                            <small class="order-relative-time">({{ $request->created_at->diffForHumans() }})</small>
                        </span>
                    </div>

                    {{-- Status Badge --}}
                    <div>
                        @if($request->status === 'approved')
                            <span class="badge-status badge-approved">
                                <i class="bi bi-check-circle-fill me-1"></i> Tasdiqlangan
                            </span>
                        @elseif($request->status === 'sold')
                            <span class="badge-status badge-sold">
                                <i class="bi bi-bag-check-fill me-1"></i> Sotilgan
                            </span>
                        @elseif($request->status === 'rejected')
                            <span class="badge-status badge-rejected">
                                <i class="bi bi-x-circle-fill me-1"></i> Rad etilgan
                            </span>
                        @else
                            <span class="badge-status badge-pending">
                                <span class="pulse-dot me-1"></span> Kutilmoqda
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Order Card Body --}}
                <div class="order-card-body">
                    <div class="row align-items-center g-3">
                        {{-- Animal Image --}}
                        <div class="col-12 col-md-auto">
                            <div class="order-img-box">
                                @if($request->animal && $request->animal->image)
                                    <div class="order-img-backdrop" style="background-image: url('{{ asset('storage/' . $request->animal->image) }}');"></div>
                                    <img src="{{ asset('storage/' . $request->animal->image) }}" alt="{{ $request->animal->title }}">
                                @else
                                    <div class="order-img-placeholder">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="col-12 col-md">
                            @if($request->animal)
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <span class="badge-cat-tag">
                                        {{ $request->animal->category?->name ?? 'Hayvon' }}
                                    </span>
                                    <span class="badge-sub-tag">
                                        <i class="bi bi-box-seam me-1 text-lime"></i>Miqdor: <strong>{{ $request->quantity ?? 1 }}</strong> ta
                                    </span>
                                    @if($request->gender)
                                        <span class="badge-sub-tag">
                                            {{ $request->gender === 'male' ? 'Erkak ♂' : 'Urg\'ochi ♀' }}
                                        </span>
                                    @endif
                                    @if($request->animal->location)
                                        <span class="badge-sub-tag">
                                            <i class="bi bi-geo-alt me-1 text-lime"></i>{{ $request->animal->location }}
                                        </span>
                                    @endif
                                </div>
                                <h4 class="order-title mb-2">
                                    <a href="{{ route('posts.show', $request->animal) }}" class="text-cream text-decoration-none hover-lime">
                                        {{ $request->animal->title }}
                                    </a>
                                </h4>

                                {{-- Seller Info --}}
                                @if($request->animal->user)
                                    <div class="order-seller-info">
                                        <div class="seller-avatar-mini">
                                            {{ mb_strtoupper(mb_substr($request->animal->user->name, 0, 1)) }}
                                        </div>
                                        <div class="order-seller-text">
                                            <span class="text-muted small">Sotuvchi:</span>
                                            <span class="fw-semibold text-cream small ms-1">{{ $request->animal->user->name }}</span>
                                            @if($request->animal->user->phone)
                                                <span class="text-muted small ms-2 d-none d-sm-inline">
                                                    <i class="bi bi-telephone me-1 text-lime"></i>{{ $request->animal->user->phone }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-muted py-2 fst-italic">
                                    <i class="bi bi-exclamation-circle me-1"></i> Ushbu e'lon sotuvchi tomonidan olib tashlangan.
                                </div>
                            @endif
                        </div>

                        {{-- Price Block --}}
                        <div class="col-12 col-md-auto text-md-end">
                            <div class="order-price-panel">
                                <div class="order-price-label">Jami to'lov</div>
                                @if($request->animal)
                                    @php
                                        $reqQty = $request->quantity ?? 1;
                                        $totalSum = (float) $request->animal->price * $reqQty;
                                    @endphp
                                    <div class="order-price-val font-serif text-lime">
                                        {{ number_format($totalSum, 0, '.', ' ') }}
                                        <small class="text-cream opacity-75">{{ $request->animal->currency }}</small>
                                    </div>
                                    @if($reqQty > 1)
                                        <div class="small text-muted">
                                            {{ $reqQty }} ta &times; {{ number_format((float) $request->animal->price, 0, '.', ' ') }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted">—</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Card Footer --}}
                <div class="order-card-footer">
                    <div class="order-status-hint">
                        @if($request->status === 'approved')
                            <div class="text-lime small d-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Sotuvchi so'rovingizni tasdiqladi! Chat orqali to'g'ridan-to'g'ri bog'lanishingiz mumkin.</span>
                            </div>
                        @elseif($request->status === 'sold')
                            <div class="text-lime small d-flex align-items-center gap-1">
                                <i class="bi bi-bag-check-fill"></i>
                                <span>Ushbu e'lon sotildi. So'rov "Sold" holatiga o'tdi.</span>
                            </div>
                        @elseif($request->status === 'rejected')
                            <div class="text-muted small d-flex align-items-center gap-1">
                                <i class="bi bi-info-circle"></i>
                                <span>Ushbu so'rov rad etilgan. Boshqa mavjud e'lonlarni ko'rib chiqishingiz mumkin.</span>
                            </div>
                        @else
                            <div class="text-orange small d-flex align-items-center gap-1">
                                <i class="bi bi-hourglass-split"></i>
                                <span>Sotuvchi so'rovingizni ko'rib chiqmoqda. Tez orada javob olasiz.</span>
                            </div>
                        @endif
                    </div>

                    <div class="order-actions-group">
                        @if($request->animal)
                            <a href="{{ route('posts.show', $request->animal) }}" class="btn-order-action btn-view-post">
                                <i class="bi bi-eye"></i> E'lonni ko'rish
                            </a>
                        @endif

                        @if(($request->status === 'approved' || $request->status === 'sold') && $request->chat)
                            @php $unread = $request->chat->unreadCountFor(auth()->id()); @endphp
                            <a href="{{ route('chats.show', $request->chat) }}" class="btn-order-action btn-open-chat">
                                <i class="bi bi-chat-dots-fill"></i> Sotuvchi bilan chat
                                @if($unread > 0)
                                    <span class="badge-unread-pill">{{ $unread }}</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state py-5">
                <div class="empty-icon"><i class="bi bi-bag-x"></i></div>
                <h3 class="empty-title">Buyurtmalar topilmadi</h3>
                <p class="empty-text">Hozircha siz tomonidan yuborilgan xarid so'rovlari mavjud emas.</p>
                <a href="{{ route('posts.index') }}" class="btn-filter-apply d-inline-flex mt-3 text-decoration-none px-4">
                    <i class="bi bi-compass me-1"></i> E'lonlarni ko'rish va xarid qilish
                </a>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $requests->links() }}
        </div>
    @endif
</div>

<style>
    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-lime { color: var(--lime) !important; }
    .text-orange { color: var(--orange) !important; }
    .bg-primary-soft { background: rgba(194, 240, 60, 0.15) !important; color: var(--lime) !important; }
    .hover-lime:hover { color: var(--lime) !important; }

    .btn-panel-link {
        padding: 6px 14px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--cream);
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-panel-link:hover {
        border-color: var(--lime);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.08);
    }

    /* Tabs */
    .admin-tabs-nav {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line);
        padding-bottom: 12px;
    }

    .admin-tab-item {
        padding: 8px 14px;
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--muted);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .admin-tab-item:hover {
        border-color: rgba(194, 240, 60, 0.45);
        color: var(--cream);
        background: rgba(194, 240, 60, 0.04);
    }

    .admin-tab-item.active {
        background: rgba(194, 240, 60, 0.12);
        border-color: rgba(194, 240, 60, 0.5);
        color: var(--lime);
        box-shadow: inset 0 0 0 1px rgba(194, 240, 60, 0.12);
    }

    .tab-count {
        background: rgba(255, 255, 255, 0.08);
        color: var(--cream);
        padding: 1px 7px;
        border-radius: 100px;
        font-size: 0.72rem;
        min-width: 20px;
        text-align: center;
    }

    .tab-count.highlight-orange {
        background: var(--orange);
        color: #fff;
    }

    /* Orders List & Cards */
    .orders-list-wrapper {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .order-card {
        background: linear-gradient(180deg, rgba(13, 24, 14, 0.96) 0%, rgba(9, 16, 11, 0.98) 100%);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .order-card:hover {
        border-color: rgba(194, 240, 60, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.3);
    }

    .order-card-approved {
        border-left: 4px solid var(--lime);
    }

    .order-card-pending {
        border-left: 4px solid var(--orange);
    }

    .order-card-header {
        padding: 14px 18px;
        background: rgba(7, 15, 9, 0.85);
        border-bottom: 1px solid rgba(255,255,255,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .order-id-badge {
        font-family: monospace;
        font-weight: 700;
        color: var(--cream);
        background: rgba(255, 255, 255, 0.05);
        padding: 5px 9px;
        border-radius: 8px;
        font-size: 0.78rem;
        letter-spacing: 0.02em;
    }

    .order-date-text {
        font-size: 0.78rem;
        color: var(--muted);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .order-relative-time {
        color: rgba(238, 233, 222, 0.72);
        margin-left: 3px;
    }

    .order-card-body {
        padding: 18px;
    }

    /* Image Box */
    .order-img-box {
        width: 120px;
        height: 96px;
        border-radius: 16px;
        overflow: hidden;
        background: #071108;
        border: 1px solid rgba(255,255,255,0.05);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .order-img-backdrop {
        position: absolute;
        inset: -8px;
        background-size: cover;
        background-position: center;
        filter: blur(10px) brightness(0.35);
        opacity: 0.8;
        pointer-events: none;
    }

    .order-img-box img {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-img-placeholder {
        color: var(--muted);
        font-size: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .badge-cat-tag {
        background: rgba(194, 240, 60, 0.1);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.2);
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .badge-sub-tag {
        background: rgba(255, 255, 255, 0.04);
        color: var(--muted);
        border: 1px solid var(--line);
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .order-title {
        font-size: 1.15rem;
        font-weight: 700;
        font-family: var(--serif);
        line-height: 1.35;
        margin: 0;
    }

    .order-seller-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
    }

    .order-seller-text {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .seller-avatar-mini {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(194,240,60,0.22), rgba(255,255,255,0.04));
        color: var(--lime);
        display: grid;
        place-items: center;
        font-size: 0.72rem;
        font-weight: 800;
        flex-shrink: 0;
        border: 1px solid rgba(194,240,60,0.3);
    }

    .order-price-panel {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.04);
        border-radius: 14px;
        padding: 10px 12px;
        min-width: 122px;
        text-align: right;
    }

    .order-price-label {
        font-size: 0.7rem;
        color: var(--muted);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .order-price-val {
        font-size: 1.4rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Badges */
    .badge-status {
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.01em;
    }

    .badge-pending {
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
        border: 1px solid rgba(255, 107, 43, 0.35);
    }

    .badge-approved {
        background: rgba(194, 240, 60, 0.14);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.35);
    }

    .badge-sold {
        background: rgba(110, 142, 255, 0.12);
        color: #aad1ff;
        border: 1px solid rgba(110, 142, 255, 0.35);
    }

    .badge-rejected {
        background: rgba(255, 255, 255, 0.05);
        color: var(--muted);
        border: 1px solid var(--line);
    }

    .pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--orange);
        display: inline-block;
        animation: pulse 1.5s infinite ease-in-out;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.3); }
    }

    /* Order Card Footer */
    .order-card-footer {
        padding: 14px 18px;
        background: rgba(6, 14, 8, 0.9);
        border-top: 1px solid rgba(255,255,255,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .order-status-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1;
    }

    .order-actions-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-order-action {
        height: 38px;
        padding: 0 14px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-view-post {
        background: transparent;
        border: 1px solid var(--line);
        color: var(--cream);
    }
    .btn-view-post:hover {
        border-color: rgba(194, 240, 60, 0.45);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.06);
    }

    .btn-open-chat {
        background: var(--lime);
        color: var(--ink);
        border: 0;
        box-shadow: 0 4px 12px rgba(194, 240, 60, 0.25);
    }
    .btn-open-chat:hover {
        background: #d7ff62;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(194, 240, 60, 0.35);
    }

    .badge-unread-pill {
        background: var(--orange);
        color: #fff;
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 999px;
        font-weight: 800;
    }

    @media (max-width: 767px) {
        .order-card-header, .order-card-body, .order-card-footer {
            padding: 14px;
        }
        .order-img-box {
            width: 100%;
            height: 160px;
        }
        .order-price-panel {
            min-width: 0;
            width: 100%;
            text-align: left;
        }
        .order-actions-group {
            width: 100%;
            justify-content: flex-start;
        }
        .btn-order-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection
