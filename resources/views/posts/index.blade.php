@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-5 align-items-end animate-fade-in">
        <div class="col-lg-5">
            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase">
                <i class="bi bi-lightning-charge-fill me-1"></i> Blog va Yangiliklar
            </span>
            <h1 class="display-4 fw-black text-dark tracking-tight">Eng so'nggi maqolalar</h1>
        </div>

        <div class="col-lg-4 mt-3 mt-lg-0">
            <form action="{{ route('posts.index') }}" method="GET" class="search-glass p-2 rounded-pill d-flex align-items-center gap-2">
                <i class="bi bi-search text-primary ms-2"></i>
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    class="form-control border-0 shadow-none bg-transparent"
                    placeholder="Maqola nomi yoki muallif..."
                    aria-label="Post qidirish"
                >
                <button type="submit" class="btn btn-primary rounded-pill px-3">Qidirish</button>
            </form>
        </div>

        <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
            @if(auth()->check() && auth()->user()->can('create posts'))
                <a href="{{ route('posts.create') }}" class="btn btn-primary btn-lg shadow-blue px-5 rounded-pill hover-lift">
                    <i class="bi bi-plus-lg me-2"></i> Yangi post
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-custom border-0 shadow-sm rounded-4 d-flex align-items-center mb-5 p-3 slide-down" role="alert">
            <div class="icon-box bg-success text-white rounded-circle me-3">
                <i class="bi bi-check2"></i>
            </div>
            <div class="fw-medium text-success">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($posts as $post)
            <div class="col-md-6 col-xl-4 animate-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                <article class="post-card h-100">
                    <div class="card h-100 border-0 rounded-5 shadow-hover overflow-hidden bg-white">

                        <div class="post-image-wrapper position-relative overflow-hidden">
                            @if($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="post-img">
                            @else
                                <div class="no-image-placeholder">
                                    <i class="bi bi-brush fs-1 opacity-25"></i>
                                </div>
                            @endif

                            <div class="post-badge glass-effect">
                                <div class="avatar-xs me-2">
                                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                </div>
                                <span class="text-white small fw-bold">{{ $post->user->name }}</span>
                            </div>
                        </div>

                        <div class="card-body p-4 pt-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="text-muted x-small">
                                    <i class="bi bi-clock me-1"></i> {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <h4 class="card-title mb-3">
                                <a href="{{ route('posts.show', $post) }}" class="text-dark text-decoration-none post-link fw-bold">
                                    {{ $post->title }}
                                </a>
                            </h4>

                            <p class="text-secondary mb-4 line-clamp-3 small-medium">
                                {{ Str::limit($post->content, 110) }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto">
                                <a href="{{ route('posts.show', $post) }}" class="btn-read-more">
                                    O'qish <i class="bi bi-arrow-right-short ms-1"></i>
                                </a>

                                <div class="d-flex gap-2 align-items-center">
                                    @if(auth()->check() && auth()->user()->hasRole('user'))
                                        <form action="{{ route('posts.like', $post) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-like {{ in_array($post->id, $likedPostIds ?? []) ? 'is-liked' : '' }}" title="Yoqtirish">
                                                <i class="bi {{ in_array($post->id, $likedPostIds ?? []) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->check() && (auth()->user()->can('delete posts') || auth()->user()->hasRole('seller') || auth()->id() === $post->user_id))
                                        <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirmPostDelete(this)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm rounded-circle shadow-none">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->check() && auth()->user()->can('edit posts'))
                                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-soft-warning btn-sm rounded-circle shadow-none">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12 animate-up">
                <div class="empty-state rounded-5 p-5 text-center bg-white shadow-sm border-0">
                    <div class="empty-icon mx-auto mb-3">
                        <i class="bi bi-journal-x"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Hozircha maqolalar yo'q</h3>
                    <p class="text-secondary mb-0">Yangi maqolalar tez orada shu yerda ko'rinadi.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div class="d-flex justify-content-center mt-5">
            <div class="pagination-glass px-3 py-2 rounded-pill shadow-sm">
                {{ $posts->links() }}
            </div>
        </div>
    @endif

    <script>
        function confirmPostDelete(form) {
            return window.confirm("Rostdan ham bu postni o'chirmoqchimisiz?");
        }
    </script>
</div>

<style>
    /* 1. Global & Typography */
    :root {
        --primary-color: #4361ee;
        --success-color: #2ec4b6;
    }
    .fw-black { font-weight: 900; }
    .tracking-tight { letter-spacing: -1.5px; }
    .x-small { font-size: 0.75rem; }
    .small-medium { font-size: 0.92rem; line-height: 1.6; }

    /* 2. Card Styling */
    .post-card {
        perspective: 1000px;
    }
    .shadow-hover {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    }
    .post-card:hover .shadow-hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.12);
    }

    /* 3. Image Styling */
    .post-image-wrapper { height: 240px; }
    .post-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s ease;
    }
    .post-card:hover .post-img { transform: scale(1.08); }

    .no-image-placeholder {
        height: 100%;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* 4. Badges & Buttons */
    .bg-primary-soft { background-color: rgba(67, 97, 238, 0.1); }
    .search-glass {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(67, 97, 238, 0.15);
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.1);
    }
    .glass-effect {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 6px 14px;
        border-radius: 50px;
        display: flex;
        align-items: center;
    }
    .avatar-xs {
        width: 24px;
        height: 24px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-read-more {
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    .btn-read-more:hover { color: #000; }
    .btn-read-more:hover i { padding-left: 5px; }

    /* Soft Buttons */
    .btn-soft-warning { background: #fff9e6; color: #ffc107; border: none; width: 34px; height: 34px; }
    .btn-soft-danger { background: #fff5f5; color: #ff4d4d; border: none; width: 34px; height: 34px; }
    .btn-soft-warning:hover { background: #ffc107; color: #fff; }
    .btn-soft-danger:hover { background: #ff4d4d; color: #fff; }

    .btn-like {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: #fff0f3;
        color: #e11d48;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
    }

    .btn-like:hover,
    .btn-like.is-liked {
        background: #e11d48;
        color: #fff;
        transform: translateY(-1px);
    }

    .empty-state {
        border: 1px solid rgba(67, 97, 238, 0.1);
        background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
    }
    .empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(67, 97, 238, 0.12);
        color: var(--primary-color);
        font-size: 1.8rem;
    }

    .pagination-glass {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(67, 97, 238, 0.12);
    }
    .pagination-glass nav {
        margin-bottom: 0;
    }
    .pagination-glass .pagination {
        margin-bottom: 0;
    }

    /* 5. Animations */
    .animate-up {
        opacity: 0;
        animation: fadeInUp 0.8s forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3) !important;
    }

    /* 6. Alerts Custom */
    .alert-custom {
        background: #fff;
        border-left: 5px solid var(--success-color) !important;
    }
    .icon-box {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
