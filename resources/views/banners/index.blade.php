@extends('layouts.app')

@section('content')
@php
    $iconBtn = 'grid size-11 cursor-pointer place-items-center rounded-[10px] border border-field bg-white text-muted transition hover:border-brand hover:text-brand';
    $statusPill = [
        'pending' => ['border-amber-400/40 bg-amber-100 text-amber-700', 'bi-clock-history', 'Tekshiruvda'],
        'approved' => ['border-ok/30 bg-ok/10 text-ok', 'bi-shield-check', 'Tasdiqlangan'],
        'rejected' => ['border-danger/30 bg-danger/10 text-danger', 'bi-shield-x', 'Rad etilgan'],
    ];
    $tabs = ['' => 'Hammasi', 'pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'];
    $rejectField = 'input min-w-0 flex-1 rounded-lg px-3';
    $rejectBtn = 'btn rounded-lg border-danger/40 bg-white px-3 text-[13px] text-danger hover:bg-danger/10';
@endphp
<div class="page">
    <x-page-head :title="$isAdmin ? 'Bosh sahifa bannerlari' : 'Mening bannerlarim'"
                 :subtitle="$isAdmin ? 'Foydalanuvchilar joylagan bannerlar shu yerda tekshiriladi. Tasdiqlanganlari standart slaydlardan oldin, tartib raqami bo\'yicha ko\'rsatiladi.' : 'Bosh sahifadagi slaydlar. Banner tasdiqlangach ko\'rinadi.'">
        <x-slot:actions>
            <a href="{{ route('home') }}#hero" class="btn btn-outline" target="_blank"><i class="bi bi-eye"></i><span class="hidden sm:inline">Ko'rish</span></a>
            <a href="{{ route('banners.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Yangi banner</a>
        </x-slot:actions>
    </x-page-head>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach($tabs as $key => $label)
            @php $count = $key === '' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
            <a href="{{ route('banners.index', $key === '' ? [] : ['status' => $key]) }}" @if(($status ?? '') === $key) aria-current="page" @endif
               class="inline-flex h-11 items-center gap-1.5 rounded-full bg-soft px-4 text-sm font-semibold text-ink transition-colors hover:bg-brand-tint current:bg-brand current:text-white">{{ $label }} <span class="text-xs opacity-75">{{ $count }}</span></a>
        @endforeach
    </div>

    <div class="flex flex-col gap-4">
        @forelse($banners as $banner)
            <article @class(['grid gap-4 rounded-[20px] border border-line bg-white p-3  md:grid-cols-[320px_1fr_auto] md:items-center md:p-4', 'opacity-60' => ! $banner->is_active && $banner->moderation_status === 'approved'])>
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
                        @if($banner->isImageOnly())<p class="text-[11px] font-semibold opacity-90">Faqat rasm</p>@endif
                        @if($banner->offer)<p class="truncate text-xs font-bold [&_b]:text-[#ffd12e]">{!! $banner->offerHtml() !!}</p>@endif
                    </div>
                </div>

                <div class="min-w-0 text-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-extrabold text-ink">{{ $banner->label() }}</h2>
                        @php [$pillClass, $pillIcon, $pillLabel] = $statusPill[$banner->moderation_status] ?? $statusPill['pending']; @endphp
                        <span class="inline-flex items-center gap-1 rounded border px-1.5 py-px text-[11px] font-bold uppercase {{ $pillClass }}"><i class="bi {{ $pillIcon }}"></i>{{ $pillLabel }}</span>
                        @unless($banner->is_active)
                            <span class="rounded border border-line bg-soft px-1.5 py-px text-[11px] font-bold text-muted uppercase">O'chirilgan</span>
                        @endunless
                    </div>
                    @if($banner->moderation_status === 'rejected' && $banner->moderation_reason)
                        <p class="mt-2 rounded-lg border border-danger/25 bg-danger/8 px-3 py-2 text-xs text-danger"><b>Rad etish sababi:</b> {{ $banner->moderation_reason }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted">
                        @if($isAdmin)
                            <span><i class="bi bi-person mr-1 text-brand"></i>{{ $banner->creator?->name ?? "O'chirilgan foydalanuvchi" }}</span>
                            <span><i class="bi bi-sort-numeric-down mr-1 text-brand"></i>Tartib: {{ $banner->sort_order }}</span>
                        @endif
                        @if($banner->link)
                            <a href="{{ $banner->link }}" target="_blank" rel="noopener" class="max-w-[280px] truncate hover:text-brand"><i class="bi bi-link-45deg mr-1 text-brand"></i>{{ $banner->link }}</a>
                        @else
                            <span><i class="bi bi-link-45deg mr-1 text-brand"></i>Havolasiz</span>
                        @endif
                        <span><i class="bi bi-clock mr-1 text-brand"></i>{{ $banner->updated_at->diffForHumans() }}</span>
                    </div>

                    {{-- The admin's decision: approve to show it on the home page, or reject with a reason the owner will see --}}
                    @if($isAdmin)
                        <div class="mt-3 flex flex-wrap items-start gap-2">
                            @if($banner->moderation_status !== 'approved')
                                <form action="{{ route('banners.approve', $banner) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary rounded-lg px-3 text-[13px]"><i class="bi bi-check-lg"></i> Tasdiqlash</button>
                                </form>
                            @endif
                            <form action="{{ route('banners.reject', $banner) }}" method="POST" class="flex min-w-[220px] max-w-[460px] flex-1 gap-2">
                                @csrf
                                <input type="text" name="reason" required minlength="5" maxlength="1000" placeholder="Rad etish sababi..." aria-label="Rad etish sababi" class="{{ $rejectField }}">
                                <button type="submit" class="{{ $rejectBtn }}"><i class="bi bi-x-lg"></i> Rad etish</button>
                            </form>
                        </div>
                    @endif
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
            <x-empty-state icon="bi-images" :title="$status ? 'Bu holatda banner yo\'q' : 'Hali banner qo\'shilmagan'" text="Hozir bosh sahifada faqat tasdiqlangan va standart slaydlar almashib turibdi.">
                <a href="{{ route('banners.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Banner qo'shish</a>
            </x-empty-state>
        @endforelse
    </div>
</div>
@endsection
