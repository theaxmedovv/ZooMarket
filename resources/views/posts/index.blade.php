@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- ── PAGE HEADER ── --}}
    <div class="row g-4 align-items-start mb-5">
        <div class="col-lg-6">
            <h1 class="page-title mb-2">Barcha mahsulotlar</h1>
        </div>

        <div class="col-lg-6">
            @php
                $showFilters = !auth()->check() || auth()->user()->hasRole('user');
                $hasActiveFilters = !empty($activeFilters['category_id'])
                    || !empty($activeFilters['gender'])
                    || !empty($activeFilters['location'])
                    || !empty($activeFilters['currency'])
                    || ($activeFilters['price_min'] ?? null) !== null
                    || ($activeFilters['price_max'] ?? null) !== null;
            @endphp

            <form action="{{ route('posts.index') }}" method="GET" class="d-grid gap-3">
                <div class="search-group d-flex gap-2">
                    <div class="search-input-wrap flex-grow-1">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" value="{{ $search ?? '' }}"
                               class="search-input"
                               placeholder="Mahsulot yoki brend qidirish...">
                    </div>

                    <button type="submit" class="btn-search">Qidirish</button>

                    @if($showFilters)
                        <button class="btn-filter {{ $hasActiveFilters ? 'active' : '' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#postFilters"
                                aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}">
                            <i class="bi bi-sliders2"></i>
                        </button>
                    @endif

                    @if(auth()->check() && (auth()->user()->hasRole('seller') || auth()->user()->hasRole('admin')))
                        <a href="{{ route('posts.create') }}" class="btn-create">
                            <i class="bi bi-plus-lg me-1"></i> Sotish
                        </a>
                    @endif
                </div>

                @if($showFilters)
                    <div id="postFilters" class="collapse {{ $hasActiveFilters ? 'show' : '' }}">
                        <div class="filter-panel">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="filter-label">Kategoriya</label>
                                    <select name="category_id" class="filter-select">
                                        <option value="">Barchasi</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string)($activeFilters['category_id'] ?? '') === (string)$category->id)>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="filter-label">Jinsi</label>
                                    <select name="gender" class="filter-select">
                                        <option value="">Barchasi</option>
                                        <option value="male"   @selected(($activeFilters['gender'] ?? '') === 'male')>Erkak</option>
                                        <option value="female" @selected(($activeFilters['gender'] ?? '') === 'female')>Urg'ochi</option>
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="filter-label">Joylashuv</label>
                                    <input type="text" name="location" class="filter-input"
                                           value="{{ $activeFilters['location'] ?? '' }}"
                                           placeholder="Masalan: Samarqand">
                                </div>

                                <div class="col-sm-6">
                                    <label class="filter-label">Valyuta</label>
                                    <select name="currency" class="filter-select">
                                        <option value="">Barchasi</option>
                                        <option value="UZS" @selected(($activeFilters['currency'] ?? '') === 'UZS')>UZS</option>
                                        <option value="USD" @selected(($activeFilters['currency'] ?? '') === 'USD')>USD</option>
                                        <option value="EUR" @selected(($activeFilters['currency'] ?? '') === 'EUR')>EUR</option>
                                        <option value="RUB" @selected(($activeFilters['currency'] ?? '') === 'RUB')>RUB</option>
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="filter-label">Min narx</label>
                                    <input type="number" name="price_min" class="filter-input" min="0" step="0.01"
                                           value="{{ $activeFilters['price_min'] ?? '' }}" placeholder="0">
                                </div>

                                <div class="col-sm-6">
                                    <label class="filter-label">Maks narx</label>
                                    <input type="number" name="price_max" class="filter-input" min="0" step="0.01"
                                           value="{{ $activeFilters['price_max'] ?? '' }}" placeholder="1 000 000">
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn-search">Filterlash</button>
                                <a href="{{ route('posts.index') }}" class="btn-reset">Tozalash</a>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ── POSTS GRID ── --}}
    <div class="row g-4">
        @forelse($posts as $post)
            <div class="col-md-6 col-lg-4 post-col" style="--i: {{ $loop->index }}">
                <article class="post-card">

                    {{-- Image --}}
                    <div class="card-img-wrap">
                        @if($post->image)
                            <img src="{{ asset('storage/' . $post->image) }}"
                                 alt="{{ $post->title }}"
                                 class="card-img" loading="lazy">
                        @else
                            <div class="card-img-placeholder">
                                <i class="bi bi-image"></i>
                                <span>Rasm yo'q</span>
                            </div>
                        @endif

                        <span class="card-badge">
                            <i class="bi bi-tag me-1"></i>{{ $post->category?->name ?? 'Mahsulot' }}
                        </span>

                        @if($post->status === 'sold')
                            <span class="card-sold-overlay">Sotilgan</span>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="card-body-inner">
                        {{-- Author --}}
                        <div class="card-author">
                            <div class="author-avatar">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</div>
                            <div class="author-info">
                                <span class="author-name">{{ $post->user->name }}</span>
                                <span class="author-time">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Title & Desc --}}
                        <h2 class="card-title">
                            <a href="{{ route('posts.show', $post) }}" class="card-title-link">{{ $post->title }}</a>
                        </h2>

                        <p class="card-desc">{{ Str::limit($post->description ?? $post->content, 90) }}</p>

                        {{-- Price --}}
                        @if(!empty($post->price))
                        <div class="card-price">
                            {{ number_format($post->price) }} {{ $post->currency ?? '' }}
                        </div>
                        @endif

                        {{-- Footer --}}
                        <div class="card-footer-inner">
                            <a href="{{ route('posts.show', $post) }}" class="btn-detail">
                                Batafsil <i class="bi bi-arrow-right ms-1"></i>
                            </a>

                            <div class="card-actions">
                                @if(auth()->check() && auth()->user()->hasRole('user'))
                                    @if($post->status === 'sold')
                                        <span class="action-tag tag-sold">Sotilgan</span>
                                    @elseif(in_array($post->id, $requestedAnimalIds ?? []))
                                        <span class="action-tag tag-pending">So'rov yuborilgan</span>
                                    @else
                                        <button type="button"
                                                class="btn-buy"
                                                data-bs-toggle="modal"
                                                data-bs-target="#buyModal{{ $post->id }}">
                                            <i class="bi bi-cart-check me-1"></i> Sotib olish
                                        </button>
                                    @endif
                                @endif

                                @if(auth()->check() && auth()->user()->hasRole('user'))
                                    <form action="{{ route('posts.like', $post) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-icon {{ in_array($post->id, $likedPostIds ?? []) ? 'liked' : '' }}"
                                                title="Like">
                                            <i class="bi {{ in_array($post->id, $likedPostIds ?? []) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->check() && (auth()->user()->can('delete posts') || auth()->id() === $post->user_id))
                                    <div class="dropdown">
                                        <button class="btn-icon" data-bs-toggle="dropdown" aria-expanded="false" title="Amallar">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end card-dropdown shadow border-0 rounded-3 p-1">
                                            <li>
                                                <a class="dropdown-item rounded-2 py-2 small fw-500"
                                                   href="{{ route('posts.edit', $post) }}">
                                                    <i class="bi bi-pencil me-2 text-muted"></i> Tahrirlash
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1 opacity-25"></li>
                                            <li>
                                                <form action="{{ route('posts.destroy', $post) }}" method="POST"
                                                      onsubmit="return confirm('O\'chirilsinmi?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item rounded-2 py-2 small text-danger fw-500">
                                                        <i class="bi bi-trash me-2"></i> O'chirish
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>

                {{-- Buy Modal --}}
                @if(auth()->check() && auth()->user()->hasRole('user') && $post->status !== 'sold' && !in_array($post->id, $requestedAnimalIds ?? []))
                    <div class="modal fade" id="buyModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content buy-modal border-0 shadow">
                                <div class="modal-body p-4">
                                    <div class="buy-modal-icon mb-3">
                                        <i class="bi bi-cart-check"></i>
                                    </div>
                                    <h5 class="fw-700 mb-1">Tasdiqlash</h5>
                                    <p class="text-muted small mb-4">
                                        <strong>{{ $post->title }}</strong> ni sotib olishga so'rov yuboriladimi?
                                    </p>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Yo'q</button>
                                        <form action="{{ route('purchase-requests.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="animal_id" value="{{ $post->id }}">
                                            <button type="submit" class="btn-modal-confirm">Ha, yuborish</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-search"></i></div>
                    <h3 class="empty-title">Hech narsa topilmadi</h3>
                    <p class="empty-text">Boshqa kalit so'z bilan urinib ko'ring yoki filtrlarni tozalang.</p>
                    <a href="{{ route('posts.index') }}" class="btn-search mt-2">Barcha postlar</a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($posts->hasPages())
        <div class="d-flex justify-content-center mt-5 pt-2">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<style>
/* ── DESIGN TOKENS ── */
:root {
    --g: #16a34a;
    --g-mid: #15803d;
    --g-dark: #14532d;
    --g-soft: #f0fdf4;
    --g-pale: #dcfce7;
    --g-border: #bbf7d0;
    --text: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --border: rgba(15,23,42,0.08);
    --surface: #ffffff;
    --bg: #f8fafc;
    --radius: 16px;
    --radius-sm: 10px;
    --radius-pill: 100px;
    --transition: 0.2s cubic-bezier(0.4,0,0.2,1);
}

/* ── PAGE HEADER ── */
.section-eyebrow {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--g);
}

.page-title {
    font-family: 'DM Serif Display', Georgia, serif;
    font-size: clamp(1.75rem, 3vw, 2.5rem);
    font-weight: 400;
    line-height: 1.15;
    letter-spacing: -0.03em;
    color: var(--text);
    margin: 0;
}

.page-subtitle {
    font-size: 0.9375rem;
    color: var(--text-2);
    margin: 0;
}

/* ── SEARCH ── */
.search-input-wrap {
    position: relative;
    min-width: 0;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-3);
    font-size: 0.9rem;
    pointer-events: none;
}

.search-input {
    width: 100%;
    height: 44px;
    padding: 0 14px 0 38px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    font-family: inherit;
    background: var(--surface);
    color: var(--text);
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition);
}

.search-input:focus {
    border-color: var(--g);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
}

.search-input::placeholder { color: var(--text-3); }

.btn-search {
    height: 44px;
    padding: 0 20px;
    background: var(--g);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: inherit;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    white-space: nowrap;
    transition: background var(--transition), transform var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-search:hover {
    background: var(--g-mid);
    color: white;
    transform: translateY(-1px);
}

.btn-filter {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text-2);
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
}

.btn-filter:hover, .btn-filter.active {
    background: var(--g-soft);
    border-color: var(--g-border);
    color: var(--g);
}

.btn-create {
    height: 44px;
    padding: 0 18px;
    background: var(--text);
    color: white !important;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: inherit;
    border-radius: var(--radius-sm);
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    transition: background var(--transition), transform var(--transition);
}

.btn-create:hover {
    background: #1e293b;
    transform: translateY(-1px);
}

/* ── FILTER PANEL ── */
.filter-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem;
    margin-top: 4px;
}

.filter-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-3);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
}

.filter-input, .filter-select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-family: inherit;
    color: var(--text);
    outline: none;
    transition: border-color var(--transition);
    appearance: none;
    -webkit-appearance: none;
}

.filter-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%2394a3b8' d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 32px;
}

.filter-input:focus, .filter-select:focus {
    border-color: var(--g);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
}

.btn-reset {
    height: 40px;
    padding: 0 18px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    font-family: inherit;
    color: var(--text-2);
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all var(--transition);
}

.btn-reset:hover {
    background: var(--bg);
    color: var(--text);
}

/* ── POST CARD ── */
.post-col {
    animation: fadeUp 0.4s ease both;
    animation-delay: calc(var(--i) * 0.05s);
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.post-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: box-shadow var(--transition), transform var(--transition), border-color var(--transition);
}

.post-card:hover {
    box-shadow: 0 12px 32px rgba(0,0,0,0.08);
    transform: translateY(-4px);
    border-color: rgba(22,163,74,0.15);
}

/* Card image */
.card-img-wrap {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: #f1f5f9;
    flex-shrink: 0;
}

.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.post-card:hover .card-img {
    transform: scale(1.04);
}

.card-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: var(--text-3);
}

.card-img-placeholder i { font-size: 2rem; }
.card-img-placeholder span { font-size: 0.8rem; }

.card-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(8px);
    color: var(--g-dark);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--g-border);
}

.card-sold-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15,23,42,0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}

/* Card body */
.card-body-inner {
    padding: 1.125rem 1.25rem 1.25rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 0;
}

.card-author {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.author-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--g-pale);
    border: 1px solid var(--g-border);
    color: var(--g-dark);
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.author-info { display: flex; flex-direction: column; line-height: 1.3; }
.author-name { font-size: 0.8125rem; font-weight: 600; color: var(--text); }
.author-time { font-size: 0.75rem; color: var(--text-3); }

.card-title {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.4;
    margin: 0 0 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-title-link {
    color: var(--text);
    text-decoration: none;
    transition: color var(--transition);
}

.card-title-link:hover { color: var(--g); }

.card-desc {
    font-size: 0.8375rem;
    color: var(--text-2);
    line-height: 1.55;
    margin: 0 0 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.card-price {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--g-dark);
    margin-bottom: 14px;
}

.card-footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    margin-top: auto;
}

.btn-detail {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--g);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: gap var(--transition), color var(--transition);
}

.btn-detail:hover { color: var(--g-mid); gap: 6px; }

.card-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-buy {
    height: 32px;
    padding: 0 12px;
    background: var(--g);
    color: white;
    font-size: 0.78rem;
    font-weight: 600;
    font-family: inherit;
    border: none;
    border-radius: var(--radius-pill);
    cursor: pointer;
    display: flex;
    align-items: center;
    white-space: nowrap;
    transition: background var(--transition);
}

.btn-buy:hover { background: var(--g-mid); }

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-3);
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
}

.btn-icon:hover { background: var(--surface); color: var(--text); border-color: #e2e8f0; }
.btn-icon.liked { background: #fff0f0; color: #ef4444; border-color: #fecaca; }

.card-dropdown { min-width: 160px; }
.card-dropdown .dropdown-item { font-weight: 500; }

.action-tag {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: var(--radius-pill);
    white-space: nowrap;
}

.tag-sold    { background: #f1f5f9; color: var(--text-3); }
.tag-pending { background: #fffbeb; color: #92400e; }

/* ── BUY MODAL ── */
.buy-modal { border-radius: var(--radius); }

.buy-modal-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    color: var(--g);
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-modal-cancel {
    height: 40px;
    padding: 0 18px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    font-family: inherit;
    color: var(--text-2);
    cursor: pointer;
    transition: all var(--transition);
}

.btn-modal-cancel:hover { background: var(--bg); color: var(--text); }

.btn-modal-confirm {
    height: 40px;
    padding: 0 20px;
    background: var(--g);
    border: none;
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 600;
    font-family: inherit;
    color: white;
    cursor: pointer;
    transition: background var(--transition);
}

.btn-modal-confirm:hover { background: var(--g-mid); }

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    background: var(--surface);
    border: 1px dashed rgba(22,163,74,0.25);
    border-radius: var(--radius);
}

.empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    background: var(--g-soft);
    color: var(--g);
    font-size: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
}

.empty-text {
    font-size: 0.9rem;
    color: var(--text-2);
    margin-bottom: 1rem;
}

/* ── PAGINATION ── */
.pagination .page-link {
    border-radius: var(--radius-sm) !important;
    border: 1px solid var(--border);
    color: var(--text-2);
    font-weight: 500;
    font-size: 0.875rem;
    padding: 7px 13px;
    margin: 0 2px;
    transition: all var(--transition);
}

.pagination .page-link:hover {
    background: var(--g-soft);
    border-color: var(--g-border);
    color: var(--g);
}

.pagination .page-item.active .page-link {
    background: var(--g);
    border-color: var(--g);
    color: white;
}

.pagination .page-item.disabled .page-link { opacity: 0.4; }

/* ── UTIL ── */
.fw-500 { font-weight: 500; }
.fw-700 { font-weight: 700; }
</style>
@endsection
