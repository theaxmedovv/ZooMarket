@php
    $isNew = $post->created_at && $post->created_at->gt(now()->subDays(3));
    $liked = in_array($post->id, $likedPostIds ?? [], true);
    $emoji = \App\Models\Category::emojiFor($post->category?->name);
    $badge = $isNew ? 'YANGI' : ($post->is_negotiable ? 'KELISHUV' : ($post->category?->name ? mb_strtoupper($post->category->name) : null));
    $favButton = 'grid size-[34px] cursor-pointer place-items-center rounded-full bg-white text-base text-ink shadow-[0_2px_8px_rgba(0,0,0,.08)] hover:text-hot';
@endphp
<article class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-white transition duration-200 hover:-translate-y-[3px] hover:border-brand hover:shadow-[0_10px_26px_rgba(0,142,204,.14)]">
    <div class="relative aspect-[1/0.92] bg-soft">
        <a href="{{ route('posts.show', $post) }}" class="grid size-full place-items-center overflow-hidden" aria-label="{{ $post->title }}">
            @if($post->imageUrl())
                <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy" class="size-full object-cover transition-transform duration-400 group-hover:scale-105">
            @else
                <span class="text-[80px]">{{ $emoji }}</span>
            @endif
        </a>
        @if($badge)<span class="absolute top-0 right-0 max-w-[60%] rounded-bl-[14px] bg-brand pt-2.5 pr-2.5 pb-2 pl-3 text-center text-[11px] leading-[1.1] font-extrabold tracking-wide text-white">{{ $badge }}</span>@endif
        @auth
            @if(auth()->user()->hasRole('user'))
                <form action="{{ route('posts.like', $post) }}" method="POST" class="absolute top-2.5 left-2.5" data-like-form>
                    @csrf
                    <button type="submit" aria-pressed="{{ $liked ? 'true' : 'false' }}" aria-label="Sevimlilarga qo'shish" class="{{ $favButton }} aria-pressed:text-hot">
                        <i class="bi {{ $liked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                    </button>
                </form>
            @endif
        @else
            <a href="{{ route('login') }}" class="absolute top-2.5 left-2.5" aria-label="Sevimlilarga qo'shish uchun kiring"><span class="{{ $favButton }}"><i class="bi bi-heart"></i></span></a>
        @endauth
    </div>
    <div class="flex flex-1 flex-col gap-2.5 px-4 pt-3.5 pb-4">
        <a href="{{ route('posts.show', $post) }}" class="line-clamp-2 min-h-10 text-[15px] leading-snug font-bold text-muted hover:text-brand">{{ $post->title }}</a>
        <div class="mt-auto flex flex-col gap-0.5 border-b border-line pb-2.5">
            @if(!empty($post->price) && (float) $post->price > 0)
                <strong class="text-[17px] font-extrabold text-ink">{{ number_format($post->price, 0, '.', ' ') }} {{ $post->currency ?? 'UZS' }}</strong>
            @else
                <strong class="text-[17px] font-extrabold text-ink">Kelishiladi</strong>
            @endif
            @if($post->breed)<span class="text-[13px] text-faint">{{ $post->breed }}</span>@endif
        </div>
        <div class="flex items-center gap-1.5 truncate text-sm font-bold text-ok">
            <i class="bi bi-geo-alt"></i>{{ $post->location ?: "O'zbekiston" }}
        </div>
    </div>
</article>
