@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary text-light fw-bold px-2 py-1 rounded-pill"><i class="bi bi-archive me-1"></i> Arxiv</span>
                <h1 class="h3 fw-bold mb-0 text-cream font-serif">Arxivdagi e'lonlar</h1>
            </div>
            <p class="text-muted small mb-0">Sotilgan yoki arxivga o'tkazilgan e'lonlaringiz ro'yxati</p>
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
                    <div class="stat-label">Jami arxivda</div>
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
                        <th>Narxi</th>
                        <th>Xaridor ma'lumoti</th>
                        <th>Holati</th>
                        <th>Sana</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivedPosts as $post)
                        @php
                            $approvedReq = $post->purchaseRequests->first();
                        @endphp
                        <tr>
                            {{-- Animal / Post Info --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="animal-thumb-mini">
                                        @if($post->image)
                                            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}">
                                        @else
                                            <i class="bi bi-image text-muted"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('posts.show', $post) }}" class="fw-bold text-cream text-decoration-none hover-lime">
                                            {{ $post->title }}
                                        </a>
                                        @if($post->breed)
                                            <div class="small text-muted">{{ $post->breed }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td>
                                <span class="badge-cat-pill">
                                    {{ $post->category?->name ?? '—' }}
                                </span>
                            </td>

                            {{-- Price --}}
                            <td>
                                @if($post->price)
                                    <span class="text-lime fw-bold font-serif">
                                        {{ number_format((float) $post->price, 0, '.', ' ') }}
                                        <small class="text-cream opacity-75">{{ $post->currency }}</small>
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Buyer Info --}}
                            <td>
                                @if($approvedReq && $approvedReq->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="buyer-avatar">
                                            {{ mb_strtoupper(mb_substr($approvedReq->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-cream small">{{ $approvedReq->user->name }}</div>
                                            <small class="text-muted">{{ $approvedReq->user->phone ?: $approvedReq->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
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
                                <span class="text-muted small">{{ $post->updated_at->format('d.m.Y H:i') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end align-items-center">
                                    <a href="{{ route('posts.show', $post) }}" class="btn-archive-action btn-view" title="Ko'rish">
                                        <i class="bi bi-eye"></i> Ko'rish
                                    </a>
                                    <form action="{{ route('admin.posts.restore', $post) }}" method="POST" class="m-0" onsubmit="return confirm('Ushbu e\'lonni qayta faollashtirish va marketplace\'da ko\'rsatishni xohlaysizmi?')">
                                        @csrf
                                        <button type="submit" class="btn-archive-action btn-restore" title="Qayta faollashtirish">
                                            <i class="bi bi-arrow-counterclockwise"></i> Qayta tiklash
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state py-5">
                                    <div class="empty-icon"><i class="bi bi-archive"></i></div>
                                    <h4 class="empty-title">Arxiv bo'sh</h4>
                                    <p class="empty-text">Hozircha sizda sotilgan yoki arxivga o'tkazilgan e'lonlar mavjud emas.</p>
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
        width: 44px;
        height: 44px;
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

    .badge-cat-pill {
        background: rgba(255, 255, 255, 0.05);
        color: var(--muted);
        border: 1px solid var(--line);
        border-radius: 100px;
        padding: 3px 9px;
        font-size: 0.72rem;
        font-weight: 600;
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
    }

    .btn-restore {
        background: rgba(194, 240, 60, 0.15);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.3);
    }
    .btn-restore:hover {
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
