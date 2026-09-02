@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-lime text-dark fw-bold px-2 py-1 rounded-pill">Sotuvchi</span>
                <h1 class="h3 fw-bold mb-0 text-cream font-serif">Sotib olish so'rovlari</h1>
            </div>
            <p class="text-muted small mb-0">Sizning e'lonlaringizga kelgan barcha xaridorlar so'rovlari ro'yxati</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('posts.index') }}" class="btn-panel-link">
                <i class="bi bi-collection me-1"></i> Mening e'lonlarim
            </a>
            <a href="{{ route('admin.archive.index') }}" class="btn-panel-link">
                <i class="bi bi-archive me-1"></i> Arxiv
            </a>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="admin-tabs-nav mb-4">
        <a href="{{ route('admin.purchase-requests.index') }}" class="admin-tab-item {{ empty($status) ? 'active' : '' }}">
            <span>Barchasi</span>
            <span class="tab-count">{{ $stats['total'] }}</span>
        </a>
        <a href="{{ route('admin.purchase-requests.index', ['status' => 'pending']) }}" class="admin-tab-item {{ $status === 'pending' ? 'active' : '' }}">
            <span><i class="bi bi-clock-history me-1 text-orange"></i> Kutilmoqda</span>
            <span class="tab-count {{ $stats['pending'] > 0 ? 'highlight-orange' : '' }}">{{ $stats['pending'] }}</span>
        </a>
        <a href="{{ route('admin.purchase-requests.index', ['status' => 'approved']) }}" class="admin-tab-item {{ $status === 'approved' ? 'active' : '' }}">
            <span><i class="bi bi-check-circle-fill me-1 text-lime"></i> Tasdiqlangan</span>
            <span class="tab-count">{{ $stats['approved'] }}</span>
        </a>
        <a href="{{ route('admin.purchase-requests.index', ['status' => 'sold']) }}" class="admin-tab-item {{ $status === 'sold' ? 'active' : '' }}">
            <span><i class="bi bi-bag-check-fill me-1 text-lime"></i> Sotilgan</span>
            <span class="tab-count">{{ $stats['sold'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.purchase-requests.index', ['status' => 'rejected']) }}" class="admin-tab-item {{ $status === 'rejected' ? 'active' : '' }}">
            <span><i class="bi bi-x-circle-fill me-1 text-muted"></i> Rad etilgan</span>
            <span class="tab-count">{{ $stats['rejected'] }}</span>
        </a>
    </div>

    {{-- Main Table Panel --}}
    <div class="admin-table-panel">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Xaridor</th>
                        <th>Aloqa ma'lumotlari</th>
                        <th>E'lon (Hayvon)</th>
                        <th>Narxi</th>
                        <th>Status</th>
                        <th>Sana</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            {{-- User --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-circle">
                                        {{ mb_strtoupper(mb_substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-cream">{{ $request->user->name }}</div>
                                        <small class="text-muted">{{ $request->user->email }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td>
                                <div>
                                    @if($request->user->phone)
                                        <div class="text-cream small"><i class="bi bi-telephone text-lime me-1"></i> {{ $request->user->phone }}</div>
                                    @else
                                        <div class="text-muted small">—</div>
                                    @endif

                                    @if($request->user->telegram_username)
                                        <div class="small">
                                            <a href="https://t.me/{{ ltrim($request->user->telegram_username, '@') }}" target="_blank" class="text-info text-decoration-none">
                                                <i class="bi bi-telegram me-1"></i>{{ '@' . ltrim($request->user->telegram_username, '@') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Animal / Post --}}
                            <td>
                                @if($request->animal)
                                    <a href="{{ route('posts.show', $request->animal) }}" class="d-flex align-items-center gap-2 text-decoration-none text-cream hover-lime">
                                        <div class="animal-thumb-mini">
                                            @if($request->animal->image)
                                                <img src="{{ asset('storage/' . $request->animal->image) }}" alt="">
                                            @else
                                                <i class="bi bi-image text-muted"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ Str::limit($request->animal->title, 28) }}</div>
                                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                <span class="badge bg-panel-soft text-lime border border-line" style="font-size: 0.7rem;">
                                                    <i class="bi bi-box-seam me-1"></i>{{ $request->quantity ?? 1 }} ta
                                                </span>
                                                @if($request->gender === 'male')
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" style="font-size: 0.7rem;">
                                                        <i class="bi bi-gender-male me-1"></i>Erkak
                                                    </span>
                                                @elseif($request->gender === 'female')
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.7rem;">
                                                        <i class="bi bi-gender-female me-1"></i>Urg'ochi
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-muted small fst-italic">E'lon o'chirilgan</span>
                                @endif
                            </td>

                            {{-- Price --}}
                            <td>
                                @if($request->animal)
                                    @php
                                        $orderQty = $request->quantity ?? 1;
                                        $totalOrder = (float) $request->animal->price * $orderQty;
                                    @endphp
                                    <div class="text-lime fw-bold font-serif">
                                        {{ number_format($totalOrder, 0, '.', ' ') }}
                                        <small class="text-cream opacity-75">{{ $request->animal->currency }}</small>
                                    </div>
                                    @if($orderQty > 1)
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            {{ $orderQty }} ta &times; {{ number_format((float) $request->animal->price, 0, '.', ' ') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td>
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
                                        <i class="bi bi-clock-history me-1"></i> Kutilmoqda
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <span class="text-muted small">{{ $request->created_at->format('d.m.Y H:i') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end align-items-center">
                                    @if($request->status === 'pending')
                                        <form action="{{ route('admin.purchase-requests.approve', $request) }}" method="POST" class="m-0" onsubmit="return confirm('Ushbu so\'rovni tasdiqlashni xohlaysizmi?')">
                                            @csrf
                                            <button type="submit" class="btn-req-action btn-approve" title="Tasdiqlash">
                                                <i class="bi bi-check2"></i> Tasdiqlash
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.purchase-requests.reject', $request) }}" method="POST" class="m-0" onsubmit="return confirm('Ushbu so\'rovni rad etishni xohlaysizmi?')">
                                            @csrf
                                            <button type="submit" class="btn-req-action btn-reject" title="Rad etish">
                                                <i class="bi bi-x-lg"></i> Rad etish
                                            </button>
                                        </form>
                                    @endif

                                    @if($request->status === 'approved')
                                        <form action="{{ route('admin.purchase-requests.mark-sold', $request) }}" method="POST" class="m-0" onsubmit="return confirm('Hayvon sotilgan deb belgilansinmi?')">
                                            @csrf
                                            <button type="submit" class="btn-req-action btn-approve" title="Sotilgan deb belgilash">
                                                <i class="bi bi-bag-check-fill"></i> Sold
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.purchase-requests.return-listing', $request) }}" method="POST" class="m-0" onsubmit="return confirm('E\'lonni yana marketga qaytarishni xohlaysizmi?')">
                                            @csrf
                                            <button type="submit" class="btn-req-action btn-reject" title="Return Listing">
                                                <i class="bi bi-arrow-counterclockwise"></i> Return Listing
                                            </button>
                                        </form>
                                    @endif

                                    @if(($request->status === 'approved' || $request->status === 'sold') && $request->chat)
                                        @php $unread = $request->chat->unreadCountFor(auth()->id()); @endphp
                                        <a href="{{ route('chats.show', $request->chat) }}" class="btn-req-action btn-chat">
                                            <i class="bi bi-chat-dots-fill"></i> Chat
                                            @if($unread > 0)
                                                <span class="badge-unread-mini">{{ $unread }}</span>
                                            @endif
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state py-5">
                                    <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                                    <h4 class="empty-title">So'rovlar topilmadi</h4>
                                    <p class="empty-text">Hozircha sizning e'lonlaringizga hech qanday sotib olish so'rovi kelib tushmagan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="admin-table-footer">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-lime { color: var(--lime) !important; }
    .bg-lime { background-color: var(--lime) !important; }
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

    /* Tab navigation */
    .admin-tabs-nav {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line);
        padding-bottom: 12px;
    }

    .admin-tab-item {
        padding: 7px 14px;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 10px;
        color: var(--muted);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .admin-tab-item:hover {
        border-color: rgba(194, 240, 60, 0.4);
        color: var(--cream);
    }

    .admin-tab-item.active {
        background: rgba(194, 240, 60, 0.1);
        border-color: var(--lime);
        color: var(--lime);
    }

    .tab-count {
        background: rgba(255, 255, 255, 0.08);
        color: var(--cream);
        padding: 1px 7px;
        border-radius: 100px;
        font-size: 0.72rem;
    }

    .tab-count.highlight-orange {
        background: var(--orange);
        color: #fff;
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

    .user-avatar-circle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #1c2e1e;
        color: var(--lime);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .animal-thumb-mini {
        width: 42px;
        height: 42px;
        border-radius: 8px;
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

    /* Badges */
    .badge-status {
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .badge-pending {
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
        border: 1px solid rgba(255, 107, 43, 0.3);
    }

    .badge-approved {
        background: rgba(194, 240, 60, 0.15);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.3);
    }

    .badge-rejected {
        background: rgba(255, 255, 255, 0.05);
        color: var(--muted);
        border: 1px solid var(--line);
    }

    /* Actions */
    .btn-req-action {
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

    .btn-approve {
        background: var(--lime);
        color: var(--ink);
    }
    .btn-approve:hover {
        background: #d7ff62;
        transform: translateY(-1px);
    }

    .btn-reject {
        background: rgba(255, 60, 60, 0.15);
        color: #ff6666;
        border: 1px solid rgba(255, 60, 60, 0.3);
    }
    .btn-reject:hover {
        background: #ff5555;
        color: #fff;
    }

    .btn-chat {
        background: #1c2e1e;
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.3);
        position: relative;
    }
    .btn-chat:hover {
        background: rgba(194, 240, 60, 0.2);
        color: var(--lime);
    }

    .badge-unread-mini {
        background: var(--orange);
        color: #fff;
        font-size: 0.65rem;
        padding: 1px 5px;
        border-radius: 999px;
    }

    .admin-table-footer {
        padding: 14px 18px;
        border-top: 1px solid var(--line);
        background: #081209;
    }
</style>
@endsection
