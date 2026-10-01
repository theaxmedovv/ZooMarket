@extends('layouts.app')

@section('content')
@php
    $me    = auth()->user();
    $other = $chat->otherParticipant($me->id);
    $imgs  = $chat->post?->allImages() ?? [];
    $isSeller = $other->hasRole('seller');
    $avatar = 'flex shrink-0 items-center justify-center rounded-full bg-brand-tint font-bold text-brand';
@endphp

<div class="grid h-[calc(100dvh-190px)] min-h-[460px] grid-cols-1 overflow-hidden bg-white md:mx-auto md:my-8 md:mb-16 md:h-[min(760px,calc(100dvh-250px))] md:w-[calc(100%-32px)] md:max-w-[1208px] md:grid-cols-[300px_1fr] md:rounded-2xl md:border md:border-line">
    {{-- Sidebar --}}
    <aside class="hidden flex-col gap-4 overflow-y-auto border-r border-line bg-white px-4 py-5 md:flex">
        <a href="{{ route('chats.index') }}" class="flex items-center gap-1.5 text-[13px] font-semibold text-muted transition-colors hover:text-brand">
            <i class="bi bi-arrow-left"></i> Barcha xabarlar
        </a>

        @if($chat->post)
            <a href="{{ route('posts.show', $chat->post) }}" class="flex items-center gap-3 rounded-xl border border-line bg-surface p-3 transition-colors hover:border-brand" title="E'lonni ochish">
                <div class="relative flex size-[60px] shrink-0 items-center justify-center overflow-hidden rounded-[10px] border border-line bg-soft">
                    @if(!empty($imgs))
                        <div class="pointer-events-none absolute -inset-2 bg-cover bg-center opacity-80 blur-[8px] brightness-[.35]" style="background-image: url('{{ route('images.show', ['path' => $imgs[0]]) }}');"></div>
                        <img src="{{ route('images.show', ['path' => $imgs[0]]) }}" alt="{{ $chat->post->title }}" class="relative z-[1] size-full object-contain">
                    @else
                        <i class="bi bi-image text-xl text-muted"></i>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[11px] font-bold text-muted uppercase">{{ $chat->post->category?->name ?? 'Hayvon' }}</div>
                    <div class="mb-0.5 truncate text-sm font-bold text-ink" title="{{ $chat->post->title }}">{{ $chat->post->title }}</div>
                    <div class="text-[15px] font-bold text-brand">
                        {{ number_format((float) $chat->post->price, 0, '.', ' ') }}
                        <small class="text-ink/75">{{ $chat->post->currency }}</small>
                    </div>
                    @if($chat->post->status === 'sold')
                        <span class="mt-1 inline-block rounded-full border border-accent/30 bg-accent/15 px-[7px] py-0.5 text-[11px] font-bold text-accent">
                            <i class="bi bi-bag-check-fill mr-1"></i> Sotilgan
                        </span>
                    @endif
                </div>
            </a>
        @endif
    </aside>

    {{-- Conversation --}}
    <div class="flex h-full flex-col overflow-hidden bg-white">
        {{-- Header --}}
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-line bg-white px-5">
            <a href="{{ route('chats.index') }}" class="mr-1 text-xl text-ink md:hidden" aria-label="Barcha xabarlar">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="{{ $avatar }} size-[38px] text-sm">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[15px] leading-tight font-bold text-ink">{{ $other->name }}</span>
                    <x-role-pill :seller="$isSeller" />
                    @if($chat->isClosed())
                        <span class="rounded-full bg-slate-500 px-2 py-0.5 text-[11px] text-white"><i class="bi bi-lock-fill mr-1"></i> Yopilgan</span>
                    @endif
                </div>
                <div class="max-w-[320px] truncate text-xs text-muted">
                    <i class="bi bi-box-seam mr-1 text-brand"></i> {{ Str::limit($chat->post?->title ?? 'E\'lon', 35) }}
                    @if($other->phone)
                        <span class="ml-2"><i class="bi bi-telephone mr-1 text-brand"></i><a href="tel:{{ $other->phone }}">{{ $other->phone }}</a></span>
                    @endif
                </div>
            </div>
            @if($chat->post)
                <a href="{{ route('posts.show', $chat->post) }}" class="inline-flex items-center gap-1 rounded-lg border border-line bg-surface px-3 py-[5px] text-xs font-semibold text-ink transition hover:border-brand hover:text-brand md:hidden">
                    <i class="bi bi-box-arrow-up-right"></i> E'lon
                </a>
            @endif
        </div>

        {{-- Messages --}}
        <div class="flex flex-1 flex-col gap-3 overflow-y-auto scroll-smooth bg-[radial-gradient(circle_at_top_right,rgba(0,142,204,.03)_0%,transparent_60%)] p-5" id="messagesBox">
            @if($chat->messages->isEmpty())
                <div class="m-auto max-w-[340px] text-center text-muted">
                    <div class="mb-2.5 text-[2.5rem] text-brand opacity-60"><i class="bi bi-chat-heart"></i></div>
                    <h5 class="text-lg font-bold text-ink">Suhbatni boshlang!</h5>
                    <p class="text-sm">Savdo, yetkazib berish yoki mahsulot holati haqida savollaringizni yozib qoldiring.</p>
                </div>
            @else
                @foreach($chat->messages as $msg)
                    @php $isMine = $msg->sender_id === $me->id; @endphp
                    <div @class(['flex max-w-[75%] items-end gap-2', $isMine ? 'flex-row-reverse self-end' : 'self-start'])>
                        @if(!$isMine)
                            <div class="{{ $avatar }} size-7 text-[11px]">{{ mb_strtoupper(mb_substr($msg->sender->name, 0, 1)) }}</div>
                        @endif
                        <div @class([
                            'max-w-full rounded-2xl border px-3.5 py-2.5 break-words text-ink shadow-[0_4px_12px_rgba(0,0,0,.12)]',
                            $isMine ? 'rounded-br-sm border-brand/25 bg-brand-tint' : 'rounded-bl-sm border-line bg-white',
                        ])>
                            <div class="text-sm leading-normal whitespace-pre-wrap">{{ $msg->body }}</div>
                            <div class="mt-1 flex items-center justify-end gap-1 text-[11px] text-muted">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if($isMine)
                                    @if($msg->read_at)
                                        <i class="bi bi-check2-all ml-1 text-brand" title="O'qildi: {{ $msg->read_at->format('H:i') }}"></i>
                                    @else
                                        <i class="bi bi-check2 ml-1" title="Yuborildi"></i>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Composer or closed notice --}}
        <div class="shrink-0 border-t border-line bg-white px-5 py-3.5">
            @if($chat->isClosed())
                <div class="rounded-xl border border-amber-400/25 bg-amber-400/8 px-[18px] py-3">
                    <div class="mb-1 flex items-center justify-center gap-2 font-semibold text-amber-600">
                        <i class="bi bi-lock-fill text-xl"></i>
                        <span>Ushbu savdo suhbati yopilgan</span>
                    </div>
                    <p class="text-center text-sm text-muted">
                        E'lon sotilgan yoki arxivga o'tkazilganligi sababli yangi xabar yuborish imkoniyati to'xtatilgan. Oldingi barcha xabarlar tarixi to'liq saqlanib qolgan.
                    </p>
                </div>
            @else
                <form action="{{ route('chats.messages.store', $chat) }}" method="POST" class="flex items-end gap-2.5">
                    @csrf
                    <div class="flex-1 rounded-[14px] border border-line bg-surface p-0.5 transition focus-within:border-brand focus-within:ring-3 focus-within:ring-brand/15 has-aria-invalid:border-accent">
                        <textarea name="body" id="chatInput" aria-label="Xabar"
                                  class="block max-h-[120px] w-full resize-none overflow-y-auto bg-transparent px-3.5 py-[9px] text-sm leading-snug text-ink outline-none placeholder:text-muted"
                                  @if($errors->has('body')) aria-invalid="true" @endif
                                  placeholder="Xabaringizni yozing... (Ctrl + Enter yuborish)"
                                  rows="1"
                                  required
                                  maxlength="2000">{{ old('body') }}</textarea>
                    </div>
                    <button type="submit" title="Yuborish" class="flex size-11 shrink-0 cursor-pointer items-center justify-center rounded-xl bg-brand text-[17px] text-white shadow-[0_4px_14px_rgba(0,142,204,.25)] transition hover:-translate-y-px hover:bg-brand-dark active:scale-95">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
                @error('body')
                    <div class="px-3 pt-2 text-sm text-danger">{{ $message }}</div>
                @enderror
            @endif
        </div>
    </div>
</div>

<script>
    // Start at the latest message
    const box = document.getElementById('messagesBox');
    if (box) box.scrollTop = box.scrollHeight;

    const ta = document.getElementById('chatInput');
    if (ta) {
        // Grow with the text, up to max-height
        ta.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        // Ctrl+Enter / Cmd+Enter sends
        ta.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.form.requestSubmit();
            }
        });
    }
</script>
@endsection
