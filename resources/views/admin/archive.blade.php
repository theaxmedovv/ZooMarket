@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary text-light fw-bold px-2 py-1 rounded-pill"><i class="bi bi-archive me-1"></i> Arxiv</span>
                <h1 class="h3 fw-bold mb-0 text-cream font-serif">Arxivdagi e'lonlar</h1>
                <span class="badge bg-dark border border-secondary text-muted px-2 py-1 rounded-pill small">
                    <i class="bi bi-lock-fill me-1"></i> Faqat o'qish uchun (Read-only)
                </span>
            </div>
            <p class="text-muted small mb-0">Sotilgan (to'liq yoki qisman) yoki arxivga o'tkazilgan e'lonlar, xaridorlar va tranzaksiyalar tarixi</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('posts.index') }}" class="btn-panel-link">
                <i class="bi bi-collection me-1"></i> Mening e'lonlarim
            </a>
            <a href="{{ route('admin.purchase-requests.index') }}" class="btn-panel-link">
                <i class="bi bi-inbox me-1"></i> So'rovlar
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="admin-stat-card">
                <div class="stat-icon bg-lime-soft text-lime"><i class="bi bi-archive-fill"></i></div>
                <div>
                    <div class="stat-value text-cream font-serif">{{ $totalArchived }} ta</div>
                    <div class="stat-label">Jami arxivda (Read-only)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="admin-stat-card">
                <div class="stat-icon bg-orange-soft text-orange"><i class="bi bi-bag-check-fill"></i></div>
                <div>
                    <div class="stat-value text-cream font-serif">{{ $totalSold }} ta</div>
                    <div class="stat-label">Muvaffaqiyatli sotilgan</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Panel --}}
    <div class="admin-table-panel">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>E'lon (Hayvon)</th>
                        <th>Kategoriya</th>
                        <th>Xaridor(lar)</th>
                        <th>Sotilgan miqdor & Jinsi</th>
                        <th>Tranzaksiya / Buyurtma</th>
                        <th>Holati</th>
                        <th>Sana</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivedPosts as $post)
                        @php
                            $soldRequests = $post->purchaseRequests;
                            $primarySold = $soldRequests->first();
                        @endphp
                        <tr>
                            {{-- Animal / Post Info --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="animal-thumb-mini">
                                        @if($post->image)
                                            <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}">
                                        @else
                                            <i class="bi bi-image text-muted"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('posts.show', $post) }}" class="fw-bold text-cream text-decoration-none hover-lime">
                                            {{ $post->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            @if($post->breed)
                                                <span class="small text-muted">{{ $post->breed }}</span>
                                            @endif
                                            <span class="badge-readonly-tag">
                                                <i class="bi bi-lock-fill me-1"></i>Read-only
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td>
                                <span class="badge-cat-pill">
                                    {{ $post->category?->name ?? '—' }}
                                </span>
                            </td>

                            {{-- Buyer Info --}}
                            <td>
                                @if($soldRequests->isNotEmpty())
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($soldRequests as $soldReq)
                                            @if($soldReq->user)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="buyer-avatar">
                                                        {{ mb_strtoupper(mb_substr($soldReq->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-cream small">{{ $soldReq->user->name }}</div>
                                                        <div class="text-muted" style="font-size: 0.72rem;">
                                                            @if($soldReq->user->phone)
                                                                <i class="bi bi-telephone text-lime me-1"></i>{{ $soldReq->user->phone }}
                                                            @else
                                                                {{ $soldReq->user->email }}
                                                            @endif
                                                        </div>
                                                        @if($soldReq->user->telegram_username)
                                                            <div style="font-size: 0.7rem;">
                                                                <a href="https://t.me/{{ ltrim($soldReq->user->telegram_username, '@') }}" target="_blank" class="text-info text-decoration-none">
                                                                    <i class="bi bi-telegram me-1"></i>{{ '@' . ltrim($soldReq->user->telegram_username, '@') }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">To'g'ridan-to'g'ri sotilgan / Arxiv</span>
                                @endif
                            </td>

                            {{-- Quantity & Gender Sold --}}
                            <td>
                                @if($soldRequests->isNotEmpty())
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($soldRequests as $soldReq)
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <span class="badge bg-lime-soft text-lime border border-lime border-opacity-25" style="font-size: 0.75rem; font-weight: 700;">
                                                    <i class="bi bi-box-seam me-1"></i>{{ $soldReq->quantity ?? 1 }} ta sotildi
                                                </span>
                                                @if($soldReq->gender === 'male')
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" style="font-size: 0.72rem;">
                                                        <i class="bi bi-gender-male me-1"></i>Erkak ♂
                                                    </span>
                                                @elseif($soldReq->gender === 'female')
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.72rem;">
                                                        <i class="bi bi-gender-female me-1"></i>Urg'ochi ♀
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="small text-muted">
                                        Umumiy: {{ $post->quantity }} ta
                                    </div>
                                @endif
                            </td>

                            {{-- Transaction / Order Info --}}
                            <td>
                                @if($soldRequests->isNotEmpty())
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($soldRequests as $soldReq)
                                            @php
                                                $reqQty = $soldReq->quantity ?? 1;
                                                $totalSum = (float) $post->price * $reqQty;
                                            @endphp
                                            <div>
                                                <div class="order-id-tag">
                                                    <i class="bi bi-receipt me-1"></i>#REQ-{{ str_pad($soldReq->id, 5, '0', STR_PAD_LEFT) }}
                                                </div>
                                                <div class="text-lime fw-bold font-serif small mt-1">
                                                    {{ number_format($totalSum, 0, '.', ' ') }}
                                                    <small class="text-cream opacity-75">{{ $post->currency }}</small>
                                                </div>
                                                @if($reqQty > 1)
                                                    <div class="text-muted" style="font-size: 0.68rem;">
                                                        ({{ $reqQty }} ta &times; {{ number_format((float) $post->price, 0, '.', ' ') }})
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($post->price)
                                    <span class="text-lime fw-bold font-serif">
                                        {{ number_format((float) $post->price, 0, '.', ' ') }}
                                        <small class="text-cream opacity-75">{{ $post->currency }}</small>
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                @if($post->status === 'sold')
                                    <span class="badge-status badge-sold">
                                        <i class="bi bi-bag-check-fill me-1"></i> Sotilgan
                                    </span>
                                @else
                                    <span class="badge-status badge-archived">
                                        <i class="bi bi-archive-fill me-1"></i> Arxivlangan
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <div class="text-cream small">{{ $post->updated_at->format('d.m.Y') }}</div>
                                <small class="text-muted">{{ $post->updated_at->format('H:i') }}</small>
                            </td>

                            {{-- Actions (Strictly Read-Only, No Restore Button) --}}
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end align-items-center">
                                    <a href="{{ route('posts.show', $post) }}" class="btn-archive-action btn-view" title="E'lonni ko'rish (Faqat o'qish)">
                                        <i class="bi bi-eye"></i> Ko'rish
                                    </a>
                                    @if($primarySold && $primarySold->chat)
                                        <a href="{{ route('chats.show', $primarySold->chat) }}" class="btn-archive-action btn-chat-hist" title="Chat tarixini ko'rish">
                                            <i class="bi bi-chat-left-text"></i> Chat tarixi
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state py-5 text-center">
                                    <div class="empty-icon text-muted fs-1 mb-2"><i class="bi bi-archive"></i></div>
                                    <h4 class="empty-title text-cream">Arxiv bo'sh</h4>
                                    <p class="empty-text text-muted">Hozircha sizda sotilgan yoki arxivga o'tkazilgan e'lonlar mavjud emas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($archivedPosts->hasPages())
            <div class="admin-table-footer">
                {{ $archivedPosts->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-lime { color: var(--lime) !important; }
    .bg-lime-soft { background: rgba(194, 240, 60, 0.12); }
    .bg-orange-soft { background: rgba(255, 107, 43, 0.12); }
    .text-orange { color: var(--orange) !important; }
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

    /* Stat Cards */
    .admin-stat-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .stat-value {
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-label {
        color: var(--muted);
        font-size: 0.76rem;
    }

    /* Table Panel */
    .admin-table-panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    .table-custom th {
        background: #081209;
        color: var(--muted);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 14px 18px;
        border-bottom: 1px solid var(--line);
    }

    .table-custom td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--line);
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .table-custom tbody tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .animal-thumb-mini {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        overflow: hidden;
        background: #060d07;
        border: 1px solid var(--line);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .animal-thumb-mini img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .badge-cat-pill {
        background: rgba(255, 255, 255, 0.05);
        color: var(--muted);
        border: 1px solid var(--line);
        border-radius: 100px;
        padding: 3px 9px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .badge-readonly-tag {
        background: rgba(255, 255, 255, 0.04);
        color: var(--muted);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        padding: 1px 5px;
        font-size: 0.65rem;
        font-weight: 600;
    }

    .order-id-tag {
        font-family: monospace;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--cream);
        background: rgba(255, 255, 255, 0.05);
        padding: 2px 6px;
        border-radius: 6px;
        display: inline-block;
    }

    .buyer-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #1c2e1e;
        color: var(--lime);
        display: grid;
        place-items: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Badges */
    .badge-status {
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .badge-sold {
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
        border: 1px solid rgba(255, 107, 43, 0.3);
    }

    .badge-archived {
        background: rgba(255, 255, 255, 0.06);
        color: var(--muted);
        border: 1px solid var(--line);
    }

    /* Actions */
    .btn-archive-action {
        height: 32px;
        padding: 0 12px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        border: 0;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-view {
        background: transparent;
        border: 1px solid var(--line);
        color: var(--cream);
    }
    .btn-view:hover {
        border-color: var(--lime);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.06);
    }

    .btn-chat-hist {
        background: rgba(194, 240, 60, 0.1);
        border: 1px solid rgba(194, 240, 60, 0.25);
        color: var(--lime);
    }
    .btn-chat-hist:hover {
        background: var(--lime);
        color: var(--ink);
    }

    .admin-table-footer {
        padding: 14px 18px;
        border-top: 1px solid var(--line);
        background: #081209;
    }
</style>
@endsection
