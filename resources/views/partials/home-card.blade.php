@php
    $isNew = $post->created_at && $post->created_at->gt(now()->subDays(3));
    $liked = in_array($post->id, $likedPostIds ?? [], true);
    $emoji = \App\Models\Category::emojiFor($post->category?->name);
    $badge = $isNew ? 'YANGI' : ($post->is_negotiable ? 'KELISHUV' : ($post->category?->name ? mb_strtoupper($post->category->name) : null));
@endphp
<article class="p-card">
    <div class="p-card-media">
        <a href="{{ route('posts.show', $post) }}" class="p-card-img-link" aria-label="{{ $post->title }}">
            @if($post->imageUrl())
                <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy">
            @else
                <span class="p-card-placeholder">{{ $emoji }}</span>
            @endif
        </a>
        @if($badge)<span class="p-card-badge">{{ $badge }}</span>@endif
        @auth
            @if(auth()->user()->hasRole('user'))
                <form action="{{ route('posts.like', $post) }}" method="POST" class="p-card-fav">
                    @csrf
                    <button type="submit" class="{{ $liked ? 'is-liked' : '' }}" aria-label="Sevimlilarga qo'shish">
                        <i class="bi {{ $liked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                    </button>
                </form>
            @endif
        @else
            <a href="{{ route('login') }}" class="p-card-fav"><button type="button" aria-label="Sevimlilarga qo'shish"><i class="bi bi-heart"></i></button></a>
        @endauth
    </div>
    <div class="p-card-body">
        <a href="{{ route('posts.show', $post) }}" class="p-card-title">{{ $post->title }}</a>
        <div class="p-card-price">
            @if(!empty($post->price) && (float) $post->price > 0)
                <strong>{{ number_format($post->price, 0, '.', ' ') }} {{ $post->currency ?? 'UZS' }}</strong>
            @else
                <strong>Kelishiladi</strong>
            @endif
            @if($post->breed)<span class="p-card-sub">{{ $post->breed }}</span>@endif
        </div>
        <div class="p-card-foot">
            <i class="bi bi-geo-alt"></i>{{ $post->location ?: "O'zbekiston" }}
        </div>
    </div>
</article>
