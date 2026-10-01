@extends('layouts.app')

@section('content')
<div class="page">
    @include('profile.partials.header', ['role' => 'Sotuvchi', 'description' => "E'lonlaringiz statistikasi va profil sozlamalari."])

    <div class="mb-6 grid gap-6 md:grid-cols-3">
        <x-metric-card icon="bi-postcard-fill" label="Postlar" tone="bg-[#5ea4ff]/12 text-[#1a6fd1]">{{ $user->posts_count ?? 0 }} <span class="text-xs font-semibold text-muted">ta</span></x-metric-card>
        <x-metric-card icon="bi-telephone-fill" label="Telefon" tone="bg-accent/12 text-accent">{{ $user->phone ?: 'Kiritilmagan' }}</x-metric-card>
        <x-metric-card icon="bi-telegram" label="Telegram" tone="bg-[#5ea4ff]/12 text-[#1a6fd1]">{{ $user->telegram_username ? '@' . ltrim($user->telegram_username, '@') : 'Kiritilmagan' }}</x-metric-card>
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-12">
        <x-panel class="xl:col-span-7" title="Mening e'lonlarim" subtitle="Yaratilgan postlar ro'yxati">
            <x-slot:action>
                <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-line px-3 py-[7px] text-xs font-semibold text-ink transition hover:border-brand hover:bg-brand/8 hover:text-brand">Hammasi <i class="bi bi-arrow-right"></i></a>
            </x-slot:action>

            <div class="divide-y divide-line">
                @forelse($recentPosts as $post)
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
                                <div class="text-sm text-muted"><i class="bi bi-calendar3 mr-1"></i> {{ $post->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <a href="{{ route('posts.show', $post) }}" title="Ko'rish" class="inline-flex size-9 shrink-0 items-center justify-center rounded-[10px] border border-line text-muted transition hover:border-brand hover:bg-brand/8 hover:text-brand"><i class="bi bi-eye"></i></a>
                    </div>
                @empty
                    <x-empty-state icon="bi-file-earmark-text" title="Hozircha e'lon yo'q" text="Yangi hayvon yoki mahsulot e'lonini qo'shish uchun “Yangi e'lon” tugmasini bosing.">
                        <a href="{{ route('posts.index') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Yangi e'lon yaratish</a>
                    </x-empty-state>
                @endforelse
            </div>
        </x-panel>

        <div class="xl:col-span-5">
            @include('profile.partials.settings', ['updateRoute' => route('admin.profile.update'), 'nameLabel' => "To'liq ism"])
        </div>
    </div>
</div>
@endsection
