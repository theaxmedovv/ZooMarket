@extends('layouts.app')

@section('content')
@php
    $panel = 'rounded-[20px] border border-line bg-white p-4 md:p-5';
    $grid = 'grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 md:gap-4 lg:grid-cols-3 xl:grid-cols-4';
@endphp
<div class="page">
    {{-- Who they are --}}
    <section class="{{ $panel }} mb-6">
        <div class="flex flex-wrap items-center gap-4">
            @if($user->avatar)
                <img src="{{ $user->avatarUrl() }}" alt="" class="size-16 shrink-0 rounded-full object-cover md:size-20">
            @else
                <span class="grid size-16 shrink-0 place-items-center rounded-full border-[1.5px] border-brand/40 bg-brand-tint text-2xl font-bold text-brand md:size-20 md:text-3xl">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            @endif

            <div class="min-w-0 flex-1">
                <h1 class="text-2xl leading-tight font-extrabold break-words text-ink md:text-3xl">{{ $user->name }}</h1>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted">
                    @if($ratingAverage !== null)
                        <a href="#sharhlar" class="inline-flex items-center gap-1.5 hover:text-brand">
                            <x-stars :rating="$ratingAverage" />
                            <b class="text-ink">{{ number_format($ratingAverage, 1) }}</b> ({{ $ratingCount }} ta sharh)
                        </a>
                    @else
                        <span>Hali baholanmagan</span>
                    @endif
                    <span><i class="bi bi-calendar3 mr-1"></i>{{ $user->created_at->format('d.m.Y') }} dan beri Faunara'da</span>
                </div>
            </div>

            @auth
                @unless($isSelf)
                    <button type="button" data-dialog-open="reportSeller" class="inline-flex min-h-11 cursor-pointer items-center gap-1.5 rounded-xl border border-line px-4 text-sm font-semibold text-muted hover:border-danger hover:text-danger">
                        <i class="bi bi-flag"></i> Shikoyat qilish
                    </button>
                @endunless
            @endauth
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2 border-t border-line pt-4 text-center md:gap-4">
            @foreach([[$stats['active'], 'Sotuvda'], [$stats['sold'], 'Sotilgan'], [$ratingCount, 'Sharhlar']] as [$value, $label])
                <div>
                    <div class="text-xl font-extrabold text-brand md:text-2xl">{{ $value }}</div>
                    <div class="text-xs text-muted md:text-sm">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- On sale now --}}
    <h2 class="mb-4 text-xl font-extrabold text-ink md:text-2xl">Sotuvdagi e'lonlar</h2>
    <div class="{{ $grid }}">
        @forelse($posts as $post)
            @include('partials.post-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
        @empty
            <x-empty-state class="col-span-full" icon="bi-collection" title="Hozir sotuvda e'lon yo'q" text="Sotuvchi yangi e'lon qo'shganda shu yerda ko'rinadi." />
        @endforelse
    </div>
    @if($posts->hasPages())
        <div class="mt-8">{{ $posts->links() }}</div>
    @endif

    {{-- What buyers say --}}
    <section id="sharhlar" class="mt-10 scroll-mt-28">
        <h2 class="mb-4 text-xl font-extrabold text-ink md:text-2xl">Xaridorlar sharhi</h2>

        @if($canReview)
            <form action="{{ route('reviews.store', $user) }}" method="POST" class="{{ $panel }} mb-4">
                @csrf
                <div class="mb-2 font-bold text-ink">{{ $myReview ? 'Sharhingizni tahrirlang' : 'Sotuvchini baholang' }}</div>
                <p class="mb-3 text-sm text-muted">Siz bu sotuvchi bilan savdo qilgansiz, shuning uchun fikringizni qoldirishingiz mumkin.</p>

                {{-- Radios, laid out highest first and reversed so hovering/selecting fills the stars from the left --}}
                <div class="mb-3 flex flex-row-reverse justify-end gap-1" role="radiogroup" aria-label="Baho">
                    @foreach([5, 4, 3, 2, 1] as $n)
                        <input type="radio" name="rating" value="{{ $n }}" id="rate{{ $n }}" class="peer sr-only" required @checked((int) old('rating', $myReview?->rating) === $n)>
                        <label for="rate{{ $n }}" title="{{ $n }} / 5" class="grid size-11 cursor-pointer place-items-center text-3xl text-line transition-colors hover:text-accent peer-checked:text-accent peer-focus-visible:outline-2 peer-focus-visible:outline-brand [&:hover~label]:text-accent">
                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                        </label>
                    @endforeach
                </div>
                @error('rating')<p class="mb-2 text-sm text-danger">{{ $message }}</p>@enderror

                <textarea name="comment" rows="3" maxlength="1000" class="input mb-3 w-full rounded-lg px-3 py-2" placeholder="Sharhingiz (ixtiyoriy)">{{ old('comment', $myReview?->comment) }}</textarea>
                @error('comment')<p class="mb-2 text-sm text-danger">{{ $message }}</p>@enderror

                <button type="submit" class="btn btn-primary h-11 rounded-xl px-6">{{ $myReview ? 'Yangilash' : 'Yuborish' }}</button>
            </form>
        @elseif(! $isSelf)
            <p class="mb-4 rounded-xl border border-dashed border-line bg-soft px-4 py-3 text-sm text-muted"><i class="bi bi-info-circle mr-1 text-brand"></i>Sotuvchini u bilan savdo qilgan xaridorlar baholay oladi.</p>
        @endif

        <div class="flex flex-col gap-3">
            @forelse($reviews as $review)
                <article class="{{ $panel }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-tint text-sm font-bold text-brand">{{ mb_strtoupper(mb_substr($review->reviewer?->name ?? '?', 0, 1)) }}</span>
                            <div class="min-w-0 leading-tight">
                                <b class="block truncate text-sm text-ink">{{ $review->reviewer?->name ?? "O'chirilgan foydalanuvchi" }}</b>
                                <small class="text-xs text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <x-stars :rating="$review->rating" />
                    </div>
                    @if($review->comment)
                        <p class="mt-3 text-sm leading-relaxed break-words whitespace-pre-line text-ink">{{ $review->comment }}</p>
                    @endif
                    @if(auth()->check() && (auth()->id() === $review->reviewer_id || auth()->user()->hasRole('admin')))
                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="mt-2" onsubmit="return confirm('Sharhni o\'chirasizmi?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex min-h-11 cursor-pointer items-center gap-1 text-xs font-semibold text-muted hover:text-danger"><i class="bi bi-trash"></i> O'chirish</button>
                        </form>
                    @endif
                </article>
            @empty
                <x-empty-state icon="bi-chat-square-text" title="Hali sharh yo'q" text="Birinchi sharhni savdo qilgan xaridor qoldiradi." />
            @endforelse
        </div>
        @if($reviews->hasPages())
            <div class="mt-6">{{ $reviews->links() }}</div>
        @endif
    </section>
</div>

@auth
    @unless($isSelf)
        @include('partials.report-dialog', ['id' => 'reportSeller', 'action' => route('reports.user', $user), 'subject' => $user->name])
    @endunless
@endauth
@endsection
