@extends('layouts.app')

@section('content')
<article class="post-details py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('posts.index') }}" class="text-decoration-none text-primary">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($post->title, 30) }}</li>
                    </ol>
                </nav>

                <header class="post-header mb-5">
                    <h1 class="display-4 fw-black text-dark tracking-tight mb-4">{{ $post->title }}</h1>

                    <div class="d-flex align-items-center justify-content-between border-bottom border-top py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 48px; height: 48px;">
                                {{ strtoupper(substr($post->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ $post->user->name }}</h6>
                                <span class="text-muted small">{{ $post->created_at->format('d-M, Y') }} • 5 min o'qish</span>
                            </div>
                        </div>

                        <div class="post-actions">
                            @if(auth()->check() && auth()->user()->hasRole('user'))
                                <form action="{{ route('posts.like', $post) }}" method="POST" class="d-inline me-2">
                                    @csrf
                                    <button type="submit" class="btn btn-soft-like btn-sm rounded-pill px-3">
                                        <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }} me-1"></i>
                                        {{ $isLiked ? 'Yoqtirilgan' : 'Yoqtirish' }}
                                        <span class="ms-1">({{ $post->liked_by_users_count }})</span>
                                    </button>
                                </form>
                            @endif

                            @if(auth()->check() && auth()->user()->can('edit posts'))
                                <a href="{{ route('posts.edit', $post) }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm border">
                                    <i class="bi bi-pencil me-1"></i> Tahrirlash
                                </a>
                            @endif
                        </div>
                    </div>
                </header>

                @if($post->image)
                    <div class="post-hero-image mb-5">
                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="img-fluid rounded-5 shadow-lg w-100">
                    </div>
                @else
                    <div class="bg-light rounded-5 d-flex align-items-center justify-content-center mb-5" style="height: 300px; border: 2px dashed #dee2e6;">
                        <i class="bi bi-image text-muted display-1 opacity-25"></i>
                    </div>
                @endif

                <div class="post-content fs-5 leading-relaxed text-secondary mb-5">
                    {!! nl2br(e($post->content)) !!}
                </div>

                <footer class="post-footer pt-5 border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('posts.index') }}" class="btn btn-outline-dark rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i> Barcha maqolalar
                    </a>

                    <div class="share-buttons d-flex gap-2">
                        <span class="text-muted small me-2 mt-1">Ulashish:</span>
                        <button class="btn btn-soft-primary btn-sm rounded-circle"><i class="bi bi-telegram"></i></button>
                        <button class="btn btn-soft-primary btn-sm rounded-circle"><i class="bi bi-facebook"></i></button>
                        <button class="btn btn-soft-primary btn-sm rounded-circle"><i class="bi bi-twitter-x"></i></button>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</article>

<style>
    /* Premium Tipografiya */
    .fw-black { font-weight: 900; }
    .tracking-tight { letter-spacing: -1px; }
    .leading-relaxed { line-height: 1.8; }

    .post-content {
        color: #374151;
        letter-spacing: -0.01em;
    }

    .post-hero-image img {
        max-height: 500px;
        object-fit: cover;
    }

    .btn-soft-primary {
        background: #f0f3ff;
        color: #4361ee;
        border: none;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .btn-soft-primary:hover {
        background: #4361ee;
        color: white;
        transform: translateY(-2px);
    }

    .btn-soft-like {
        background: #fff0f3;
        color: #e11d48;
        border: 1px solid rgba(225, 29, 72, 0.15);
        transition: all 0.25s ease;
    }

    .btn-soft-like:hover {
        background: #e11d48;
        color: #fff;
        transform: translateY(-2px);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "→";
        font-size: 0.8rem;
        vertical-align: middle;
    }

    /* Maqola ichidagi paragraflar orasini ochish */
    .post-content p {
        margin-bottom: 1.5rem;
    }
</style>
@endsection
