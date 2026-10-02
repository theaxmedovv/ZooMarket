@extends('layouts.app')

@section('content')
@php
    $tag = 'inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[11px] font-semibold';
    $action = 'inline-flex h-8 cursor-pointer items-center gap-1 rounded-lg border px-3 text-xs font-bold whitespace-nowrap transition';
    $approve = $action . ' border-transparent bg-brand text-white hover:-translate-y-px hover:bg-brand-dark';
    $reject = $action . ' border-red-500/30 bg-red-500/15 text-danger hover:bg-danger hover:text-white';
@endphp
<div class="page">
    <x-page-head title="Sotib olish so'rovlari" subtitle="E'lonlaringizga kelgan xaridorlar so'rovlari: tasdiqlang, rad eting yoki sotilgan deb belgilang." />

    {{-- Status tabs --}}
    <div class="mb-6 flex flex-wrap gap-2 border-b border-line pb-3">
        <x-status-tab :href="route('admin.purchase-requests.index')" :active="empty($status)" :count="$stats['total']">Barchasi</x-status-tab>
        <x-status-tab :href="route('admin.purchase-requests.index', ['status' => 'pending'])" :active="$status === 'pending'" :count="$stats['pending']" :highlight="$stats['pending'] > 0"><i class="bi bi-clock-history mr-1 text-accent"></i> Kutilmoqda</x-status-tab>
        <x-status-tab :href="route('admin.purchase-requests.index', ['status' => 'approved'])" :active="$status === 'approved'" :count="$stats['approved']"><i class="bi bi-check-circle-fill mr-1 text-brand"></i> Tasdiqlangan</x-status-tab>
        <x-status-tab :href="route('admin.purchase-requests.index', ['status' => 'sold'])" :active="$status === 'sold'" :count="$stats['sold'] ?? 0"><i class="bi bi-bag-check-fill mr-1 text-brand"></i> Sotilgan</x-status-tab>
        <x-status-tab :href="route('admin.purchase-requests.index', ['status' => 'rejected'])" :active="$status === 'rejected'" :count="$stats['rejected']"><i class="bi bi-x-circle-fill mr-1 text-muted"></i> Rad etilgan</x-status-tab>
    </div>

    <x-data-table>
        <thead>
            <tr>
                <th>Xaridor</th>
                <th>Aloqa ma'lumotlari</th>
                <th>E'lon (Hayvon)</th>
                <th>Narxi</th>
                <th>Status</th>
                <th>Sana</th>
                <th class="text-right">Amallar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr>
                    {{-- Buyer --}}
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="grid size-[34px] shrink-0 place-items-center rounded-full bg-brand-tint text-sm font-bold text-brand">{{ mb_strtoupper(mb_substr($request->user->name, 0, 1)) }}</div>
                            <div>
                                <div class="font-bold text-ink">{{ $request->user->name }}</div>
                                <small class="text-muted">{{ $request->user->email }}</small>
                            </div>
                        </div>
                    </td>

                    {{-- Contact --}}
                    <td class="whitespace-nowrap">
                        @if($request->user->phone)
                            <div class="text-ink"><i class="bi bi-telephone mr-1 text-brand"></i> {{ $request->user->phone }}</div>
                        @else
                            <div class="text-muted">—</div>
                        @endif
                        @if($request->user->telegram_username)
                            <a href="https://t.me/{{ ltrim($request->user->telegram_username, '@') }}" target="_blank" class="text-male hover:underline">
                                <i class="bi bi-telegram mr-1"></i>{{ '@' . ltrim($request->user->telegram_username, '@') }}
                            </a>
                        @endif
                    </td>

                    {{-- Listing --}}
                    <td>
                        @if($request->animal)
                            <div class="flex items-center gap-2">
                                <div class="grid size-[42px] shrink-0 place-items-center overflow-hidden rounded-lg border border-line bg-white">
                                    @if($request->animal->image)
                                        <img src="{{ $request->animal->imageUrl() }}" alt="" class="size-full object-contain">
                                    @else
                                        <i class="bi bi-image text-muted"></i>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('posts.show', $request->animal) }}" class="font-semibold text-ink hover:text-brand">{{ Str::limit($request->animal->title, 28) }}</a>
                                    <div class="mt-1 flex flex-wrap items-center gap-1">
                                        <span class="{{ $tag }} border-line bg-black/4 text-brand"><i class="bi bi-box-seam"></i>{{ $request->quantity ?? 1 }} ta</span>
                                        <x-request-genders :request="$request" :tag="$tag" />
                                        @if($request->animal->trashed())
                                            <span class="{{ $tag }} border-danger/25 bg-danger/10 text-danger"><i class="bi bi-trash"></i>O'chirilgan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-muted italic">E'lon o'chirilgan</span>
                        @endif
                    </td>

                    {{-- Price --}}
                    <td class="whitespace-nowrap">
                        @if($request->animal)
                            @php
                                $orderQty = $request->quantity ?? 1;
                                $totalOrder = (float) $request->animal->price * $orderQty;
                            @endphp
                            <div class="font-bold text-brand">
                                {{ number_format($totalOrder, 0, '.', ' ') }}
                                <small class="text-ink/75">{{ $request->animal->currency }}</small>
                            </div>
                            @if($orderQty > 1)
                                <div class="text-xs text-muted">{{ $orderQty }} ta &times; {{ number_format((float) $request->animal->price, 0, '.', ' ') }}</div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td><x-request-status :status="$request->status" /></td>

                    <td><span class="text-sm whitespace-nowrap text-muted">{{ $request->created_at->format('d.m.Y H:i') }}</span></td>

                    {{-- Actions --}}
                    <td class="text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                            @if($request->status === 'pending')
                                <form action="{{ route('admin.purchase-requests.approve', $request) }}" method="POST" onsubmit="return confirm('Ushbu so\'rovni tasdiqlashni xohlaysizmi?')">
                                    @csrf
                                    <button type="submit" class="{{ $approve }}" title="Tasdiqlash"><i class="bi bi-check2"></i> Tasdiqlash</button>
                                </form>
                                <form action="{{ route('admin.purchase-requests.reject', $request) }}" method="POST" onsubmit="return confirm('Ushbu so\'rovni rad etishni xohlaysizmi?')">
                                    @csrf
                                    <button type="submit" class="{{ $reject }}" title="Rad etish"><i class="bi bi-x-lg"></i> Rad etish</button>
                                </form>
                            @endif

                            @if($request->status === 'approved')
                                <form action="{{ route('admin.purchase-requests.mark-sold', $request) }}" method="POST" onsubmit="return confirm('Ushbu so\'rovdagi hayvonlar sotilgan deb belgilansinmi? (Qolgan hayvonlar e\'londa qoladi; e\'lon faqat hammasi sotilganda arxivga o\'tadi)')">
                                    @csrf
                                    <button type="submit" class="{{ $approve }}" title="Ushbu so'rovni sotilgan deb belgilash"><i class="bi bi-bag-check-fill"></i> Sotildi</button>
                                </form>
                                <form action="{{ route('admin.purchase-requests.reject', $request) }}" method="POST" onsubmit="return confirm('Ushbu tasdiqlangan so\'rovni bekor/rad qilishni xohlaysizmi?')">
                                    @csrf
                                    <button type="submit" class="{{ $reject }}" title="Rad etish"><i class="bi bi-x-lg"></i> Rad etish</button>
                                </form>
                            @endif

                            @if(($request->status === 'approved' || $request->status === 'sold') && $request->chat)
                                @php $unread = $request->chat->unreadCountFor(auth()->id()); @endphp
                                <a href="{{ route('chats.show', $request->chat) }}" class="{{ $action }} border-brand/30 bg-brand-tint text-brand hover:bg-brand/20">
                                    <i class="bi bi-chat-dots-fill"></i> Chat
                                    @if($unread > 0)
                                        <span class="rounded-full bg-accent px-[5px] py-px text-[11px] text-white">{{ $unread }}</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state icon="bi-inbox" title="So'rovlar topilmadi"
                            text="Hozircha sizning e'lonlaringizga hech qanday sotib olish so'rovi kelib tushmagan." />
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if($requests->hasPages())
            <x-slot:footer>{{ $requests->links() }}</x-slot:footer>
        @endif
    </x-data-table>
</div>
@endsection
