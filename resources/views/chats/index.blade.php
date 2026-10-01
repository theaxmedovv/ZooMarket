@extends('layouts.app')

@section('content')
<div class="page max-w-[860px]">
    <x-page-head title="Xabarlar" subtitle="Tasdiqlangan xaridlar bo'yicha sotuvchi va xaridor suhbatlari." />

    @if($chats->isEmpty())
        <x-empty-state icon="bi-chat-square-dots" title="Hozircha xabarlar yo'q"
            text="Xarid so'rovi tasdiqlangandan so'ng, ushbu sahifada sotuvchi va xaridor o'rtasida avtomatik chat ochiladi.">
            <a href="{{ route('posts.index') }}" class="btn btn-primary"><i class="bi bi-compass"></i> Marketplace'ga o'tish</a>
        </x-empty-state>
    @else
        <div class="flex flex-col gap-3">
            @foreach($chats as $chat)
                @php
                    $other   = $chat->otherParticipant(auth()->id());
                    $last    = $chat->lastMessage;
                    $isMine  = $last && $last->sender_id === auth()->id();
                    $unread  = $chat->unread;
                    $isSeller = $other->hasRole('seller');
                    $img = $chat->post?->allImages()[0] ?? null;
                @endphp
                <a href="{{ route('chats.show', $chat) }}" @class([
                    'group relative flex items-center gap-3 overflow-hidden rounded-2xl border border-line bg-white px-3.5 py-3 transition hover:-translate-y-0.5 hover:border-brand/40 hover:shadow-[0_8px_24px_rgba(0,0,0,.12)] md:gap-4 md:px-5 md:py-4',
                    'border-l-4 border-l-brand' => $unread > 0,
                ])>
                    {{-- Post thumbnail --}}
                    <div class="relative flex h-[54px] w-[58px] shrink-0 items-center justify-center overflow-hidden rounded-xl border border-line bg-soft md:h-16 md:w-[72px]">
                        @if($img)
                            <div class="pointer-events-none absolute -inset-2 bg-cover bg-center opacity-80 blur-[8px] brightness-[.35]" style="background-image: url('{{ route('images.show', ['path' => $img]) }}');"></div>
                            <img src="{{ route('images.show', ['path' => $img]) }}" alt="{{ $chat->post?->title ?? '' }}" class="relative z-[1] size-full object-contain">
                        @else
                            <i class="bi bi-image text-[1.4rem] text-muted"></i>
                        @endif
                    </div>

                    {{-- Main info --}}
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex items-baseline justify-between gap-2.5">
                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                <span class="max-w-[180px] truncate text-[15px] font-bold text-ink md:max-w-[280px]">{{ $chat->post?->title ?? 'E\'lon' }}</span>
                                @if($chat->post?->category)
                                    <span class="rounded-md border border-line bg-black/5 px-[7px] py-0.5 text-[11px] font-semibold text-muted">{{ $chat->post->category->name }}</span>
                                @endif
                                @if($chat->post && $chat->post->price)
                                    <span class="text-sm font-bold text-brand">{{ number_format((float) $chat->post->price, 0, '.', ' ') }} {{ $chat->post->currency }}</span>
                                @endif
                                @if(!empty($chat->is_closed))
                                    <span class="rounded-md border border-slate-400/25 bg-slate-400/25 px-1.5 py-0.5 text-[11px] text-muted"><i class="bi bi-lock-fill mr-1"></i> Yopilgan</span>
                                @endif
                            </div>
                            <span class="shrink-0 text-xs whitespace-nowrap text-muted">
                                {{ $last ? $last->created_at->diffForHumans(null, true) : $chat->updated_at->diffForHumans(null, true) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex shrink-0 items-center gap-2">
                                <div class="grid size-[26px] shrink-0 place-items-center rounded-full bg-brand-tint text-xs font-bold text-brand">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</div>
                                <span class="text-[13px] font-semibold text-ink">{{ $other->name }}</span>
                                <x-role-pill :seller="$isSeller" />
                            </div>

                            <div class="flex min-w-0 items-center gap-2">
                                @if($last)
                                    <span @class(['max-w-[180px] truncate text-[13px] md:max-w-[280px]', $unread > 0 ? 'font-semibold text-ink' : 'text-muted'])>
                                        @if($isMine)<span class="mr-1 font-bold text-brand">Siz:</span>@endif{{ Str::limit($last->body, 55) }}
                                    </span>
                                @else
                                    <span class="text-[13px] text-muted italic">Hali xabarlar yo'q. Suhbatni boshlang!</span>
                                @endif

                                @if($unread > 0)
                                    <span class="flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-brand px-1.5 text-[11px] font-extrabold text-white shadow-[0_0_10px_rgba(0,142,204,.4)]">{{ $unread }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <i class="bi bi-chevron-right text-sm text-muted transition group-hover:translate-x-[3px] group-hover:text-brand"></i>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
