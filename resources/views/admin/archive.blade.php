@extends('layouts.app')

@section('content')
@php
    $tag = 'inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[11px] font-semibold';
    $action = 'inline-flex h-8 items-center gap-1 rounded-lg border px-3 text-xs font-bold whitespace-nowrap transition';
    $status = 'inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold whitespace-nowrap';
@endphp
<div class="page">
    <x-page-head title="Arxiv" subtitle="Sotilgan va arxivlangan e'lonlar tarixi. Bu yerdagi ma'lumotlarni o'zgartirib bo'lmaydi." />

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4">
        @foreach([['bi-archive-fill', 'bg-brand/12 text-brand', $totalArchived, 'Jami arxivda'], ['bi-bag-check-fill', 'bg-accent/12 text-accent', $totalSold, 'Muvaffaqiyatli sotilgan']] as [$icon, $tone, $value, $label])
            <div class="flex items-center gap-3.5 rounded-[14px] border border-line bg-white p-4">
                <div class="grid size-11 shrink-0 place-items-center rounded-[10px] text-xl {{ $tone }}"><i class="bi {{ $icon }}"></i></div>
                <div>
                    <div class="text-xl leading-tight font-bold text-ink">{{ $value }} ta</div>
                    <div class="text-xs text-muted">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <x-data-table>
        <thead>
            <tr>
                <th>E'lon (Hayvon)</th>
                <th>Kategoriya</th>
                <th>Xaridor(lar)</th>
                <th>Sotilgan miqdor & Jinsi</th>
                <th>Tranzaksiya / Buyurtma</th>
                <th>Holati</th>
                <th>Sana</th>
                <th class="text-right">Amallar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($archivedPosts as $post)
                @php
                    $soldRequests = $post->purchaseRequests;
                    $primarySold = $soldRequests->first();
                @endphp
                <tr>
                    {{-- Listing --}}
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="grid size-[46px] shrink-0 place-items-center overflow-hidden rounded-[10px] border border-line bg-white">
                                @if($post->image)
                                    <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="size-full object-contain">
                                @else
                                    <i class="bi bi-image text-muted"></i>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('posts.show', $post) }}" class="font-bold text-ink hover:text-brand">{{ $post->title }}</a>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @if($post->breed)
                                        <span class="text-sm text-muted">{{ $post->breed }}</span>
                                    @endif
                                    <span class="rounded border border-black/10 bg-black/4 px-[5px] py-px text-[11px] font-semibold text-muted"><i class="bi bi-lock-fill mr-1"></i>Faqat ko'rish</span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td><span class="rounded-full border border-line bg-black/5 px-[9px] py-[3px] text-xs font-semibold whitespace-nowrap text-muted">{{ $post->category?->name ?? '—' }}</span></td>

                    {{-- Buyers --}}
                    <td>
                        @if($soldRequests->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach($soldRequests as $soldReq)
                                    @if($soldReq->user)
                                        <div class="flex items-center gap-2">
                                            <div class="grid size-7 shrink-0 place-items-center rounded-full bg-brand-tint text-xs font-bold text-brand">{{ mb_strtoupper(mb_substr($soldReq->user->name, 0, 1)) }}</div>
                                            <div>
                                                <div class="text-sm font-semibold text-ink">{{ $soldReq->user->name }}</div>
                                                <div class="text-xs text-muted">
                                                    @if($soldReq->user->phone)
                                                        <i class="bi bi-telephone mr-1 text-brand"></i>{{ $soldReq->user->phone }}
                                                    @else
                                                        {{ $soldReq->user->email }}
                                                    @endif
                                                </div>
                                                @if($soldReq->user->telegram_username)
                                                    <a href="https://t.me/{{ ltrim($soldReq->user->telegram_username, '@') }}" target="_blank" class="text-[11px] text-male hover:underline">
                                                        <i class="bi bi-telegram mr-1"></i>{{ '@' . ltrim($soldReq->user->telegram_username, '@') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-sm text-muted italic">To'g'ridan-to'g'ri sotilgan / Arxiv</span>
                        @endif
                    </td>

                    {{-- Quantity & gender sold --}}
                    <td>
                        @if($soldRequests->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach($soldRequests as $soldReq)
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="{{ $tag }} border-brand/25 bg-brand/12 font-bold text-brand"><i class="bi bi-box-seam"></i>{{ $soldReq->quantity ?? 1 }} ta sotildi</span>
                                        @if($soldReq->gender === 'male')
                                            <span class="{{ $tag }} border-male/25 bg-male/10 text-male"><i class="bi bi-gender-male"></i>Erkak ♂</span>
                                        @elseif($soldReq->gender === 'female')
                                            <span class="{{ $tag }} border-danger/25 bg-danger/10 text-danger"><i class="bi bi-gender-female"></i>Urg'ochi ♀</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-muted">Umumiy: {{ $post->quantity }} ta</div>
                        @endif
                    </td>

                    {{-- Transaction --}}
                    <td class="whitespace-nowrap">
                        @if($soldRequests->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach($soldRequests as $soldReq)
                                    @php
                                        $reqQty = $soldReq->quantity ?? 1;
                                        $totalSum = (float) $post->price * $reqQty;
                                    @endphp
                                    <div>
                                        <div class="inline-block rounded-md bg-black/5 px-1.5 py-0.5 font-mono text-xs font-bold text-ink"><i class="bi bi-receipt mr-1"></i>#REQ-{{ str_pad($soldReq->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        <div class="mt-1 text-sm font-bold text-brand">
                                            {{ number_format($totalSum, 0, '.', ' ') }}
                                            <small class="text-ink/75">{{ $post->currency }}</small>
                                        </div>
                                        @if($reqQty > 1)
                                            <div class="text-[11px] text-muted">({{ $reqQty }} ta &times; {{ number_format((float) $post->price, 0, '.', ' ') }})</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($post->price)
                            <span class="font-bold text-brand">
                                {{ number_format((float) $post->price, 0, '.', ' ') }}
                                <small class="text-ink/75">{{ $post->currency }}</small>
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @if($post->status === 'sold')
                            <span class="{{ $status }} border-accent/30 bg-accent/15 text-accent"><i class="bi bi-bag-check-fill"></i> Sotilgan</span>
                        @else
                            <span class="{{ $status }} border-line bg-black/6 text-muted"><i class="bi bi-archive-fill"></i> Arxivlangan</span>
                        @endif
                    </td>

                    <td class="whitespace-nowrap">
                        <div class="text-sm text-ink">{{ $post->updated_at->format('d.m.Y') }}</div>
                        <small class="text-muted">{{ $post->updated_at->format('H:i') }}</small>
                    </td>

                    {{-- Actions (read-only: no restore) --}}
                    <td class="text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('posts.show', $post) }}" class="{{ $action }} border-line text-ink hover:border-brand hover:bg-brand/6 hover:text-brand" title="E'lonni ko'rish (Faqat o'qish)">
                                <i class="bi bi-eye"></i> Ko'rish
                            </a>
                            @if($primarySold && $primarySold->chat)
                                <a href="{{ route('chats.show', $primarySold->chat) }}" class="{{ $action }} border-brand/25 bg-brand/10 text-brand hover:bg-brand hover:text-white" title="Chat tarixini ko'rish">
                                    <i class="bi bi-chat-left-text"></i> Chat tarixi
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <x-empty-state icon="bi-archive" title="Arxiv bo'sh" text="Hozircha sizda sotilgan yoki arxivga o'tkazilgan e'lonlar mavjud emas." />
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if($archivedPosts->hasPages())
            <x-slot:footer>{{ $archivedPosts->links() }}</x-slot:footer>
        @endif
    </x-data-table>
</div>
@endsection
