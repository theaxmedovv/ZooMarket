@extends('layouts.app')

@section('content')
@php
    $tag = 'inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[11px] font-semibold';
    $action = 'inline-flex h-[38px] items-center justify-center gap-1.5 rounded-[10px] px-3.5 text-[13px] font-bold transition max-md:w-full';
@endphp
<div class="page">
    <x-page-head title="Mening buyurtmalarim" subtitle="Yuborgan xarid so'rovlaringiz va ularning holati." />

    {{-- Filter tabs --}}
    <div class="mb-6 flex flex-wrap gap-2 border-b border-line pb-3">
        <x-status-tab :href="route('user.purchase-requests.index')" :active="empty($status)" :count="$stats['total'] ?? 0">Barchasi</x-status-tab>
        <x-status-tab :href="route('user.purchase-requests.index', ['status' => 'pending'])" :active="($status ?? '') === 'pending'" :count="$stats['pending'] ?? 0" :highlight="($stats['pending'] ?? 0) > 0"><i class="bi bi-clock-history mr-1 text-accent"></i> Kutilmoqda</x-status-tab>
        <x-status-tab :href="route('user.purchase-requests.index', ['status' => 'approved'])" :active="($status ?? '') === 'approved'" :count="$stats['approved'] ?? 0"><i class="bi bi-check-circle-fill mr-1 text-brand"></i> Tasdiqlangan</x-status-tab>
        <x-status-tab :href="route('user.purchase-requests.index', ['status' => 'rejected'])" :active="($status ?? '') === 'rejected'" :count="$stats['rejected'] ?? 0"><i class="bi bi-x-circle-fill mr-1 text-muted"></i> Rad etilgan</x-status-tab>
    </div>

    {{-- Orders --}}
    <div class="flex flex-col gap-[18px]">
        @forelse($requests as $request)
            <div @class([
                'overflow-hidden rounded-[20px] border border-black/6 bg-white shadow-[0_18px_40px_rgba(0,0,0,.12)] transition hover:-translate-y-0.5 hover:border-brand/40',
                'border-l-4 border-l-brand' => $request->status === 'approved',
                'border-l-4 border-l-accent' => $request->status === 'pending',
            ])>
                {{-- Header --}}
                <div class="flex flex-wrap items-center justify-between gap-2.5 border-b border-black/5 bg-white/85 p-3.5 md:px-[18px]">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-lg bg-black/5 px-[9px] py-[5px] font-mono text-[13px] font-bold tracking-wide text-ink"><i class="bi bi-receipt mr-1"></i> #REQ-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="inline-flex items-center gap-1 text-[13px] text-muted">
                            <i class="bi bi-calendar3"></i> {{ $request->created_at->format('d.m.Y H:i') }}
                            <small class="ml-0.5 text-ink/70">({{ $request->created_at->diffForHumans() }})</small>
                        </span>
                    </div>
                    <x-request-status :status="$request->status" />
                </div>

                {{-- Body --}}
                <div class="flex flex-col gap-4 p-3.5 md:flex-row md:items-center md:p-[18px]">
                    <div class="relative flex h-40 w-full shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-black/5 bg-surface md:h-24 md:w-[120px]">
                        @if($request->animal && $request->animal->image)
                            <div class="pointer-events-none absolute -inset-2 bg-cover bg-center opacity-80 blur-[10px] brightness-[.35]" style="background-image: url('{{ $request->animal->imageUrl() }}');"></div>
                            <img src="{{ $request->animal->imageUrl() }}" alt="{{ $request->animal->title }}" class="relative z-[1] size-full object-cover">
                        @else
                            <i class="bi bi-image relative z-[1] text-2xl text-muted"></i>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        @if($request->animal)
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="{{ $tag }} border-brand/20 bg-brand/10 font-extrabold text-brand">{{ $request->animal->category?->name ?? 'Hayvon' }}</span>
                                <span class="{{ $tag }} border-line bg-black/4 text-muted"><i class="bi bi-box-seam text-brand"></i>Miqdor: <strong>{{ $request->quantity ?? 1 }}</strong> ta</span>
                                <x-request-genders :request="$request" :tag="$tag" />
                                @if($request->animal->location)
                                    <span class="{{ $tag }} border-line bg-black/4 text-muted"><i class="bi bi-geo-alt text-brand"></i>{{ $request->animal->location }}</span>
                                @endif
                                @if($request->animal->trashed())
                                    <span class="{{ $tag }} border-danger/25 bg-danger/10 text-danger"><i class="bi bi-trash"></i>E'lon sotuvchi tomonidan olib tashlangan</span>
                                @endif
                            </div>
                            <h4 class="mb-2 text-lg leading-snug font-bold">
                                <a href="{{ route('posts.show', $request->animal) }}" class="text-ink hover:text-brand">{{ $request->animal->title }}</a>
                            </h4>

                            @if($request->animal->user)
                                <div class="mt-2 flex items-center gap-2 text-sm">
                                    <div class="grid size-7 shrink-0 place-items-center rounded-full border border-brand/30 bg-linear-135 from-brand/22 to-black/4 text-xs font-extrabold text-brand">{{ mb_strtoupper(mb_substr($request->animal->user->name, 0, 1)) }}</div>
                                    <div class="flex flex-wrap items-center">
                                        <span class="text-muted">Sotuvchi:</span>
                                        <span class="ml-1 font-semibold text-ink">{{ $request->animal->user->name }}</span>
                                        @if($request->animal->user->phone)
                                            <span class="ml-2 hidden text-muted sm:inline"><i class="bi bi-telephone mr-1 text-brand"></i>{{ $request->animal->user->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="py-2 text-muted italic"><i class="bi bi-exclamation-circle mr-1"></i> Ushbu e'lon sotuvchi tomonidan olib tashlangan.</div>
                        @endif
                    </div>

                    <div class="w-full rounded-[14px] border border-black/4 bg-black/2 px-3 py-2.5 md:w-auto md:min-w-[122px] md:text-right">
                        <div class="mb-1 text-[11px] font-bold tracking-wide text-muted uppercase">Jami to'lov</div>
                        @if($request->animal)
                            @php
                                $reqQty = $request->quantity ?? 1;
                                $totalSum = (float) $request->animal->price * $reqQty;
                            @endphp
                            <div class="text-[1.4rem] font-bold whitespace-nowrap text-brand">
                                {{ number_format($totalSum, 0, '.', ' ') }}
                                <small class="text-ink/75">{{ $request->animal->currency }}</small>
                            </div>
                            @if($reqQty > 1)
                                <div class="text-sm text-muted">{{ $reqQty }} ta &times; {{ number_format((float) $request->animal->price, 0, '.', ' ') }}</div>
                            @endif
                        @else
                            <div class="text-muted">—</div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-black/5 bg-white/90 p-3.5 md:px-[18px]">
                    @php
                        [$hintTone, $hintIcon, $hintText] = match ($request->status) {
                            'approved' => ['text-brand', 'bi-check-circle-fill', "Sotuvchi so'rovingizni tasdiqladi! Chat orqali to'g'ridan-to'g'ri bog'lanishingiz mumkin."],
                            'sold' => ['text-brand', 'bi-bag-check-fill', "Siz tanlagan hayvonlar sotildi. So'rov \"Sold\" holatiga o'tdi."],
                            'rejected' => ['text-muted', 'bi-info-circle', "Ushbu so'rov rad etilgan. Boshqa mavjud e'lonlarni ko'rib chiqishingiz mumkin."],
                            default => ['text-accent', 'bi-hourglass-split', "Sotuvchi so'rovingizni ko'rib chiqmoqda. Tez orada javob olasiz."],
                        };
                    @endphp
                    <div class="flex min-w-0 flex-1 items-center gap-1 text-sm {{ $hintTone }}">
                        <i class="bi {{ $hintIcon }}"></i>
                        <span>{{ $hintText }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 max-md:w-full">
                        @if($request->animal)
                            <a href="{{ route('posts.show', $request->animal) }}" class="{{ $action }} border border-line text-ink hover:border-brand/45 hover:bg-brand/6 hover:text-brand">
                                <i class="bi bi-eye"></i> E'lonni ko'rish
                            </a>
                        @endif

                        @if(($request->status === 'approved' || $request->status === 'sold') && $request->chat)
                            @php $unread = $request->chat->unreadCountFor(auth()->id()); @endphp
                            <a href="{{ route('chats.show', $request->chat) }}" class="{{ $action }} bg-brand text-white shadow-[0_4px_12px_rgba(0,142,204,.25)] hover:-translate-y-px hover:bg-brand-dark">
                                <i class="bi bi-chat-dots-fill"></i> Sotuvchi bilan chat
                                @if($unread > 0)
                                    <span class="rounded-full bg-accent px-1.5 py-0.5 text-[11px] font-extrabold text-white">{{ $unread }}</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state icon="bi-bag-x" title="Buyurtmalar topilmadi" text="Hozircha siz tomonidan yuborilgan xarid so'rovlari mavjud emas.">
                <a href="{{ route('posts.index') }}" class="btn btn-primary"><i class="bi bi-compass"></i> E'lonlarni ko'rish va xarid qilish</a>
            </x-empty-state>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="mt-6">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
