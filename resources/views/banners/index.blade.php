@extends('layouts.app')

@section('content')
@php
    $iconBtn = 'grid size-10 cursor-pointer place-items-center rounded-[10px] border border-field bg-white text-muted transition hover:border-brand hover:text-brand';
@endphp
<div class="page">
    <x-page-head title="Bosh sahifa bannerlari" subtitle="Bosh sahifadagi almashib turadigan e'lonlar. Siz qo'shgan bannerlar standart slaydlardan oldin, tartib raqami bo'yicha ko'rsatiladi.">
        <x-slot:actions>
            <a href="{{ route('home') }}#hero" class="btn btn-outline" target="_blank"><i class="bi bi-eye"></i><span class="hidden sm:inline">Ko'rish</span></a>
            <a href="{{ route('banners.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Yangi banner</a>
        </x-slot:actions>
    </x-page-head>

    <div class="flex flex-col gap-4">
        @forelse($banners as $banner)
            <article @class(['grid gap-4 rounded-[20px] border border-line bg-white p-3 shadow-[0_8px_24px_rgba(0,0,0,.06)] md:grid-cols-[320px_1fr_auto] md:items-center md:p-4', 'opacity-60' => ! $banner->is_active])>
                {{-- Small preview of the slide --}}
                <div style="background-color: {{ $banner->color }}" class="relative flex aspect-[2.5/1] items-center overflow-hidden rounded-xl px-5 text-white">
                    @if($banner->imageUrl())
                        <img src="{{ $banner->imageUrl() }}" alt="" class="absolute inset-0 size-full object-cover">
                        <span class="absolute inset-0 bg-linear-to-r from-black/75 via-black/45 to-black/5"></span>
                    @elseif($banner->emoji)
                        <span class="absolute right-4 text-5xl">{{ $banner->emoji }}</span>
                    @endif
                    <div class="relative min-w-0 pr-12">
                        @if($banner->eyebrow)<p class="truncate text-[11px] font-semibold">{{ $banner->eyebrow }}</p>@endif
                        <p class="line-clamp-2 text-lg leading-tight font-black uppercase">{{ $banner->title }}</p>
                        @if($banner->offer)<p class="truncate text-xs font-bold [&_b]:text-[#ffd12e]">{!! $banner->offerHtml() !!}</p>@endif
                    </div>
                </div>

                <div class="min-w-0 text-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-extrabold text-ink">{{ $banner->title }}</h2>
                        <span @class([
                            'rounded border px-1.5 py-px text-[11px] font-bold uppercase',
                            'border-ok/30 bg-ok/10 text-ok' => $banner->is_active,
                            'border-line bg-soft text-muted' => ! $banner->is_active,
                        ])>{{ $banner->is_active ? 'Faol' : "O'chirilgan" }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted">
                        <span><i class="bi bi-sort-numeric-down mr-1 text-brand"></i>Tartib: {{ $banner->sort_order }}</span>
                        @if($banner->link)
                            <a href="{{ $banner->link }}" target="_blank" rel="noopener" class="max-w-[280px] truncate hover:text-brand"><i class="bi bi-link-45deg mr-1 text-brand"></i>{{ $banner->link }}</a>
                        @else
                            <span><i class="bi bi-link-45deg mr-1 text-brand"></i>Havolasiz</span>
                        @endif
                        <span><i class="bi bi-clock mr-1 text-brand"></i>{{ $banner->updated_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('banners.toggle', $banner) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="{{ $iconBtn }}" aria-label="{{ $banner->is_active ? "Yashirish" : "Ko'rsatish" }}" title="{{ $banner->is_active ? "Yashirish" : "Ko'rsatish" }}"><i class="bi {{ $banner->is_active ? 'bi-eye-slash' : 'bi-eye' }}"></i></button>
                    </form>
                    <a href="{{ route('banners.edit', $banner) }}" class="{{ $iconBtn }}" aria-label="Tahrirlash" title="Tahrirlash"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Bannerni o\'chirib tashlaysizmi?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="{{ $iconBtn }} hover:border-danger hover:text-danger" aria-label="O'chirish" title="O'chirish"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </article>
        @empty
            <x-empty-state icon="bi-images" title="Hali banner qo'shilmagan" text="Hozir bosh sahifada faqat standart slaydlar almashib turibdi.">
                <a href="{{ route('banners.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Birinchi bannerni qo'shish</a>
            </x-empty-state>
        @endforelse
    </div>
</div>
@endsection
