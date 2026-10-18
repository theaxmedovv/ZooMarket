@extends('layouts.app')

@section('content')
@php
    $chip = 'inline-flex items-center gap-1 rounded-lg border border-black/7 bg-black/4 px-[9px] py-[3px] text-xs text-muted';
@endphp
<div class="page">
    <x-page-head title="E'lonlarni tekshirish" subtitle="Sotuvchilar joylagan e'lonlar shu yerda kutadi. Ma'lumotlarni tekshiring: to'g'ri bo'lsa tasdiqlang — e'lon hammaga ko'rinadi; noto'g'ri bo'lsa sababini yozib rad eting — sotuvchi sababni ko'radi." />

    <div class="mb-6 flex flex-wrap gap-2 border-b border-line pb-3">
        <x-status-tab :href="route('moderation.index', ['status' => 'pending'])" :active="$status === 'pending'" :count="$counts['pending'] ?? 0" :highlight="($counts['pending'] ?? 0) > 0"><i class="bi bi-hourglass-split mr-1 text-accent"></i> Kutilmoqda</x-status-tab>
        <x-status-tab :href="route('moderation.index', ['status' => 'approved'])" :active="$status === 'approved'" :count="$counts['approved'] ?? 0"><i class="bi bi-check-circle-fill mr-1 text-brand"></i> Tasdiqlangan</x-status-tab>
        <x-status-tab :href="route('moderation.index', ['status' => 'rejected'])" :active="$status === 'rejected'" :count="$counts['rejected'] ?? 0"><i class="bi bi-x-circle-fill mr-1 text-muted"></i> Rad etilgan</x-status-tab>
    </div>

    <div class="flex flex-col gap-5">
        @forelse($posts as $post)
            <article class="grid gap-4 rounded-[20px] border border-line bg-white p-4 shadow-[0_8px_24px_rgba(0,0,0,.06)] md:grid-cols-[220px_1fr] md:p-5">
                {{-- Photos: the admin must see every image the seller uploaded --}}
                <div class="flex flex-col gap-2">
                    @php $images = $post->allImageUrls(); @endphp
                    @if($images)
                        <a href="{{ $images[0] }}" target="_blank" class="block aspect-[4/3] overflow-hidden rounded-xl border border-line bg-soft">
                            <img src="{{ $images[0] }}" alt="{{ $post->title }}" class="size-full object-cover">
                        </a>
                        @if(count($images) > 1)
                            <div class="flex gap-2">
                                @foreach(array_slice($images, 1) as $url)
                                    <a href="{{ $url }}" target="_blank" class="block size-14 overflow-hidden rounded-lg border border-line bg-soft"><img src="{{ $url }}" alt="" class="size-full object-cover"></a>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="grid aspect-[4/3] place-items-center rounded-xl border border-dashed border-line bg-soft text-sm text-muted"><span><i class="bi bi-image mr-1"></i> Rasm yo'q</span></div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h2 class="text-lg font-extrabold text-ink"><a href="{{ route('posts.show', $post) }}" class="hover:text-brand">{{ $post->title }}</a></h2>
                        <span class="text-base font-extrabold whitespace-nowrap text-brand">{{ number_format((float) $post->price, 0, '.', ' ') }} {{ $post->currency }}@if($post->is_negotiable) <small class="font-semibold text-muted">· kelishiladi</small>@endif</span>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="{{ $chip }}"><i class="bi bi-tag"></i>{{ $post->category?->name ?? '—' }}</span>
                        <span class="{{ $chip }}">Turi: {{ $post->breed }}</span>
                        @if($post->age)<span class="{{ $chip }}">Yosh: {{ $post->age }}</span>@endif
                        @if($post->color)<span class="{{ $chip }}">Rang: {{ $post->color }}</span>@endif
                        <span class="{{ $chip }}"><i class="bi bi-box-seam"></i>{{ $post->quantity }} ta @if($post->usesGender()) ({{ $post->male_quantity }} ♂ / {{ $post->female_quantity }} ♀)@endif</span>
                        @foreach($post->detailRows() as $row)
                            <span class="{{ $chip }}">{{ $row['label'] }}: {{ $row['value'] }}</span>
                        @endforeach
                        <span class="{{ $chip }}"><i class="bi bi-geo-alt"></i>{{ $post->fullLocation() }}</span>
                    </div>

                    <p class="mt-3 line-clamp-6 text-sm whitespace-pre-line text-ink">{{ $post->description }}</p>

                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
                        <span><i class="bi bi-person mr-1 text-brand"></i>{{ $post->user?->name }} · {{ $post->user?->email }}</span>
                        @if($post->user?->phone)<span><i class="bi bi-telephone mr-1 text-brand"></i>{{ $post->user->phone }}</span>@endif
                        <span><i class="bi bi-clock mr-1 text-brand"></i>{{ $post->updated_at->diffForHumans() }}</span>
                    </div>

                    <x-moderation-panel :post="$post" class="mt-4 bg-surface shadow-none" />
                </div>
            </article>
        @empty
            <x-empty-state icon="bi-inbox" :title="$status === 'pending' ? 'Tekshiriladigan e\'lon yo\'q' : 'Bu bo\'limda e\'lon yo\'q'" :text="$status === 'pending' ? 'Yangi e\'lonlar joylanganda shu yerda paydo bo\'ladi.' : null" />
        @endforelse
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>
</div>
@endsection
