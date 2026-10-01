@extends('layouts.app')

@section('content')
<div class="page">
    @include('profile.partials.header', ['role' => 'Xaridor', 'description' => "Saqlangan e'lonlaringiz va profil sozlamalari."])

    <div class="mb-6 grid gap-6 md:grid-cols-3">
        <x-metric-card icon="bi-telephone-fill" label="Telefon" tone="bg-accent/12 text-accent">{{ $user->phone ?: 'Kiritilmagan' }}</x-metric-card>
        <x-metric-card icon="bi-heart-fill" label="Yoqtirganlar" tone="bg-[#ff5a78]/12 text-[#d63384]">{{ $user->liked_posts_count ?? 0 }} <span class="text-xs font-semibold text-muted">ta</span></x-metric-card>
        <x-metric-card icon="bi-telegram" label="Telegram" tone="bg-[#5ea4ff]/12 text-[#1a6fd1]">{{ $user->telegram_username ? '@' . ltrim($user->telegram_username, '@') : 'Kiritilmagan' }}</x-metric-card>
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-12">
        <x-panel class="xl:col-span-7" title="Saqlangan e'lonlar" title-id="saved" subtitle="Sizga yoqqan mahsulotlar ro'yxati">
            <x-slot:action>
                <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-line px-3 py-[7px] text-xs font-semibold text-ink transition hover:border-brand hover:bg-brand/8 hover:text-brand">E'lonlarni ko'rish <i class="bi bi-arrow-right"></i></a>
            </x-slot:action>

            <div class="divide-y divide-line">
                @forelse($likedPosts as $post)
                    <div class="flex items-start justify-between gap-3.5 py-3.5 sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            @if($post->image)
                                <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="size-[62px] shrink-0 rounded-xl border border-line object-cover">
                            @else
                                <div class="grid size-[62px] shrink-0 place-items-center rounded-xl border border-line bg-brand/8 text-xl text-muted"><i class="bi bi-image"></i></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <h6 class="mb-1 truncate text-base leading-snug font-bold">
                                    <a href="{{ route('posts.show', $post) }}" class="text-ink hover:text-brand">{{ $post->title }}</a>
                                </h6>
                                <div class="text-sm text-muted">
                                    <i class="bi bi-person mr-1"></i> {{ $post->user->name }}
                                    <span class="mx-2 text-line">•</span>
                                    <i class="bi bi-calendar3 mr-1"></i> {{ optional($post->pivot->created_at)->format('d M') }}
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <form action="{{ route('posts.like', $post) }}" method="POST" onsubmit="return confirm('Yoqtirilganlardan o\'chirilsinmi?')">
                                @csrf
                                <button type="submit" title="O'chirish" class="inline-flex size-9 cursor-pointer items-center justify-center rounded-[10px] border border-line text-muted transition hover:border-accent/50 hover:bg-brand/8 hover:text-accent"><i class="bi bi-heart-fill"></i></button>
                            </form>
                            <a href="{{ route('posts.show', $post) }}" title="Ko'rish" class="inline-flex size-9 items-center justify-center rounded-[10px] border border-line text-muted transition hover:border-brand hover:bg-brand/8 hover:text-brand"><i class="bi bi-eye"></i></a>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="bi-heart-break" title="Hozircha yo'q" text="Sizga yoqqan e'lonlarni saqlash uchun “Like” tugmasini bosing.">
                        <a href="{{ route('posts.index') }}" class="btn btn-primary"><i class="bi bi-compass"></i> E'lonlarni ko'rish</a>
                    </x-empty-state>
                @endforelse
            </div>
        </x-panel>

        <div class="xl:col-span-5">
            @include('profile.partials.settings', ['updateRoute' => route('user.profile.update'), 'nameLabel' => 'Ism'])
        </div>
    </div>
</div>
@endsection
