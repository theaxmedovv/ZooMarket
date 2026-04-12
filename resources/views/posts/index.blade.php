@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-5 g-4 align-items-end animate-fade-in">
        <div class="col-lg-7">
            <h2 class="fw-black display-6 mb-1 tracking-tight">Barcha mahsulotlar</h2>
            <p class="text-muted mb-0 fs-5 opacity-75">Eng so'nggi e'lonlar va eksklyuziv takliflar bir joyda.</p>
        </div>

        <div class="col-lg-5">
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-lg-end align-items-md-center">
                <form action="{{ route('posts.index') }}" method="GET" class="search-bar-wrapper flex-grow-1">
                    <div class="input-group input-group-lg shadow-sm border rounded-pill overflow-hidden bg-white">
                        <span class="input-group-text border-0 bg-transparent ps-4 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" value="{{ $search ?? '' }}"
                               class="form-control border-0 shadow-none fs-6 py-3"
                               placeholder="Mahsulot yoki brend qidirish...">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Qidirish</button>
                    </div>
                </form>

                @if(auth()->check() && (auth()->user()->hasRole('seller') || auth()->user()->hasRole('admin')))
                    <a href="{{ route('posts.create') }}" class="btn btn-dark btn-lg shadow-sm px-4 rounded-pill hover-lift shrink-0">
                        <i class="bi bi-plus-lg me-2"></i> Sotish
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-custom border-0 shadow-sm rounded-4 d-flex align-items-center mb-5 p-3 slide-down bg-white border-start border-success border-4">
            <div class="icon-box bg-success text-white rounded-circle me-3 p-2 d-flex align-items-center justify-content-center" style="width:35px; height:35px;">
                <i class="bi bi-check2"></i>
            </div>
            <div class="fw-semibold text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($posts as $post)
            <div class="col-md-6 col-lg-4 animate-up" style="animation-delay: {{ $loop->index * 0.05 }}s">
                <article class="post-card h-100">
                    <div class="card h-100 border-0 rounded-5 shadow-sm overflow-hidden bg-white transition-all border-hover position-relative">

                        <div class="position-absolute top-0 start-0 m-3" style="z-index: 10;">
                            <span class="badge bg-white text-primary shadow-sm rounded-pill px-3 py-2 fw-bold small border">
                                <i class="bi bi-tag-fill me-1"></i> {{ $post->category?->name ?? 'Mahsulot' }}
                            </span>
                        </div>

                        <div class="post-image-wrapper position-relative overflow-hidden" style="height: 250px;">
                            @if($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="post-img w-100 h-100 object-fit-cover">
                            @else
                                <div class="no-image-placeholder h-100 bg-light d-flex flex-column align-items-center justify-content-center text-muted opacity-50">
                                    <i class="bi bi-image fs-1 mb-2"></i>
                                    <span class="small fw-bold">Rasm mavjud emas</span>
                                </div>
                            @endif
                            <div class="image-overlay"></div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold border border-primary border-opacity-10" style="width: 34px; height: 34px; font-size: 13px;">
                                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                </div>
                                <div class="lh-1">
                                    <div class="text-dark small fw-bold d-block">{{ $post->user->name }}</div>
                                    <span class="text-muted x-small">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <h5 class="card-title mb-2">
                                <a href="{{ route('posts.show', $post) }}" class="text-dark text-decoration-none post-link fw-bold line-clamp-2 fs-5">
                                    {{ $post->title }}
                                </a>
                            </h5>

                            <p class="text-muted mb-4 small line-clamp-2 flex-grow-1 opacity-75">
                                {{ Str::limit($post->description ?? $post->content, 85) }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                <a href="{{ route('posts.show', $post) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                                    Batafsil
                                </a>

                                <div class="d-flex gap-2">
                                    @if(auth()->check() && auth()->user()->hasRole('user'))
                                        @if($post->status === 'sold')
                                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 fw-bold" disabled>
                                                Sotilgan
                                            </button>
                                        @elseif(in_array($post->id, $requestedAnimalIds ?? []))
                                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold" disabled>
                                                So'rov yuborilgan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#buyModal{{ $post->id }}">
                                                <i class="bi bi-cart-check me-1"></i> Sotib olish
                                            </button>
                                        @endif
                                    @endif

                                    @if(auth()->check() && auth()->user()->hasRole('user'))
                                        <form action="{{ route('posts.like', $post) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-market-action {{ in_array($post->id, $likedPostIds ?? []) ? 'active' : '' }}">
                                                <i class="bi {{ in_array($post->id, $likedPostIds ?? []) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->check() && (auth()->user()->can('delete posts') || auth()->id() === $post->user_id))
                                        <div class="dropdown">
                                            <button class="btn-market-action" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
                                                <li><a class="dropdown-item rounded-3 py-2 small fw-bold" href="{{ route('posts.edit', $post) }}"><i class="bi bi-pencil me-2"></i> Tahrirlash</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li>
                                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Oʻchirilsinmi?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item rounded-3 py-2 small text-danger fw-bold"><i class="bi bi-trash me-2"></i> O'chirish</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                @if(auth()->check() && auth()->user()->hasRole('user') && $post->status !== 'sold' && !in_array($post->id, $requestedAnimalIds ?? []))
                    <div class="modal fade" id="buyModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-body p-4">
                                    <h5 class="fw-bold mb-3">Tasdiqlash</h5>
                                    <p class="text-muted mb-4">Siz rostdan ham ushbu hayvonni sotib olishni xohlaysizmi?</p>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Yo'q</button>
                                        <form action="{{ route('purchase-requests.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="animal_id" value="{{ $post->id }}">
                                            <button type="submit" class="btn btn-success rounded-pill px-4">Ha</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <div class="bg-white p-5 rounded-5 shadow-sm border border-dashed">
                    <i class="bi bi-search fs-1 text-muted opacity-25 mb-3 d-block"></i>
                    <h4 class="fw-bold">Hech narsa topilmadi</h4>
                    <p class="text-muted">Qidiruv natijalari bo'yicha hech qanday mahsulot topilmadi. Boshqa so'z bilan urinib ko'ring.</p>
                    <a href="{{ route('posts.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Barcha postlar</a>
                </div>
            </div>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<style>
    /* Brend ranglari */
    :root {
        --market-primary: #4361ee;
    }

    .fw-black { font-weight: 800; }
    .tracking-tight { letter-spacing: -1.5px; }
    .x-small { font-size: 0.75rem; }

    /* Search Bar Styling */
    .search-bar-wrapper .input-group {
        border-color: rgba(0,0,0,0.08) !important;
        transition: 0.3s ease;
    }
    .search-bar-wrapper .input-group:focus-within {
        border-color: var(--market-primary) !important;
        box-shadow: 0 10px 25px -5px rgba(67, 97, 238, 0.15) !important;
    }

    /* Card Animations & Effects */
    .post-card .card {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .post-card:hover .card {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important;
    }

    .post-image-wrapper { background: #f1f5f9; }
    .image-overlay {
        position: absolute;
        bottom: 0; left: 0; right: 0; height: 50%;
        background: linear-gradient(to top, rgba(0,0,0,0.1), transparent);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Action Buttons */
    .btn-market-action {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        transition: 0.3s;
    }
    .btn-market-action:hover {
        background: #fff;
        color: var(--market-primary);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .btn-market-action.active {
        background: #fee2e2;
        color: #ef4444;
        border-color: #fecaca;
    }

    /* Hover lift effect for buttons */
    .hover-lift { transition: transform 0.2s; }
    .hover-lift:hover { transform: translateY(-2px); }

    /* Custom Scrollbar in Dropdowns */
    .dropdown-menu { animation: slideIn 0.3s ease; }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
