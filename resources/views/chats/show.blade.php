@extends('layouts.app')

@section('content')
@php
    $me    = auth()->user();
    $other = $chat->otherParticipant($me->id);
    $imgs  = $chat->post?->allImages() ?? [];
    $isSeller = $other->hasRole('seller');
@endphp

<div class="chat-page-container">
    {{-- ── SIDEBAR ── --}}
    <aside class="chat-sidebar">
        <a href="{{ route('chats.index') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Barcha xabarlar
        </a>

        {{-- Post card --}}
        @if($chat->post)
            <div class="sidebar-post-card">
                <div class="sidebar-post-img-box">
                    @if(!empty($imgs))
                        <div class="sidebar-post-backdrop" style="background-image: url('{{ asset('storage/' . $imgs[0]) }}');"></div>
                        <img src="{{ asset('storage/' . $imgs[0]) }}" alt="{{ $chat->post->title }}">
                    @else
                        <div class="sidebar-post-placeholder"><i class="bi bi-image"></i></div>
                    @endif
                </div>
                <div class="sidebar-post-body">
                    <div class="sidebar-post-cat">{{ $chat->post->category?->name ?? 'Hayvon' }}</div>
                    <div class="sidebar-post-title" title="{{ $chat->post->title }}">{{ $chat->post->title }}</div>
                    <div class="sidebar-post-price font-serif text-lime">
                        {{ number_format((float) $chat->post->price, 0, '.', ' ') }}
                        <small class="text-cream opacity-75">{{ $chat->post->currency }}</small>
                    </div>
                    @if($chat->post->status === 'sold')
                        <span class="sidebar-sold-badge">
                            <i class="bi bi-bag-check-fill me-1"></i> Sotilgan
                        </span>
                    @endif
                </div>
            </div>

            <a href="{{ route('posts.show', $chat->post) }}" class="sidebar-view-btn">
                <i class="bi bi-eye me-1"></i> E'lonni to'liq ko'rish
            </a>
        @endif

        {{-- Other participant --}}
        <div class="sidebar-user-card">
            <div class="sidebar-user-avatar">
                {{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}
            </div>
            <div class="sidebar-user-meta">
                <div class="sidebar-user-name">{{ $other->name }}</div>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <span class="badge-role-pill {{ $isSeller ? 'role-seller' : 'role-buyer' }}">
                        {{ $isSeller ? 'Sotuvchi' : 'Xaridor' }}
                    </span>
                    @if($other->phone)
                        <span class="text-muted small ms-1" style="font-size: 0.72rem;">
                            <i class="bi bi-telephone text-lime me-1"></i>{{ $other->phone }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </aside>

    {{-- ── CHAT MAIN ── --}}
    <div class="chat-main">
        {{-- Header --}}
        <div class="chat-header">
            <a href="{{ route('chats.index') }}" class="chat-header-back d-md-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="chat-header-avatar">
                {{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}
            </div>
            <div class="chat-header-info">
                <div class="d-flex align-items-center gap-2">
                    <span class="chat-header-name">{{ $other->name }}</span>
                    <span class="badge-role-pill {{ $isSeller ? 'role-seller' : 'role-buyer' }}">
                        {{ $isSeller ? 'Sotuvchi' : 'Xaridor' }}
                    </span>
                </div>
                <div class="chat-header-sub">
                    <i class="bi bi-box-seam me-1 text-lime"></i> {{ Str::limit($chat->post?->title ?? 'E\'lon', 35) }}
                </div>
            </div>
            @if($chat->post)
                <a href="{{ route('posts.show', $chat->post) }}" class="chat-header-post-link d-none d-sm-inline-flex">
                    <i class="bi bi-box-arrow-up-right me-1"></i> E'lon
                </a>
            @endif
        </div>

        {{-- Messages --}}
        <div class="chat-messages" id="messagesBox">
            @if($chat->messages->isEmpty())
                <div class="no-messages-box">
                    <div class="no-messages-icon"><i class="bi bi-chat-heart"></i></div>
                    <h5>Suhbatni boshlang!</h5>
                    <p>Savdo, yetkazib berish yoki mahsulot holati haqida savollaringizni yozib qoldiring.</p>
                </div>
            @else
                @foreach($chat->messages as $msg)
                    @php $isMine = $msg->sender_id === $me->id; @endphp
                    <div class="msg-row {{ $isMine ? 'mine' : 'theirs' }}">
                        @if(!$isMine)
                            <div class="msg-avatar">{{ mb_strtoupper(mb_substr($msg->sender->name, 0, 1)) }}</div>
                        @endif
                        <div class="msg-bubble {{ $isMine ? 'bubble-mine' : 'bubble-theirs' }}">
                            <div class="msg-body">{{ $msg->body }}</div>
                            <div class="msg-meta">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if($isMine)
                                    @if($msg->read_at)
                                        <i class="bi bi-check2-all text-lime ms-1" title="O'qildi: {{ $msg->read_at->format('H:i') }}"></i>
                                    @else
                                        <i class="bi bi-check2 text-muted ms-1" title="Yuborildi"></i>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Input Bar --}}
        <div class="chat-input-area">
            <form action="{{ route('chats.messages.store', $chat) }}" method="POST" class="chat-input-form">
                @csrf
                <div class="chat-input-wrapper">
                    <textarea name="body"
                              class="chat-input {{ $errors->has('body') ? 'is-invalid' : '' }}"
                              placeholder="Xabaringizni yozing... (Ctrl + Enter yuborish)"
                              rows="1"
                              required
                              maxlength="2000">{{ old('body') }}</textarea>
                </div>
                <button type="submit" class="chat-send-btn" title="Yuborish">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
            @error('body')
                <div class="px-3 pb-2 text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<style>
    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-lime { color: var(--lime) !important; }

    /* Layout */
    .chat-page-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        height: calc(100vh - 70px);
        background: #060d07;
        overflow: hidden;
    }
    @media (max-width: 768px) {
        .chat-page-container { grid-template-columns: 1fr; }
        .chat-sidebar { display: none; }
    }

    /* ── SIDEBAR ── */
    .chat-sidebar {
        background: var(--panel);
        border-right: 1px solid var(--line);
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        overflow-y: auto;
    }

    .back-link {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--muted);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.15s;
    }
    .back-link:hover { color: var(--lime); }

    .sidebar-post-card {
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 12px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .sidebar-post-img-box {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        overflow: hidden;
        background: #040905;
        border: 1px solid var(--line);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-post-backdrop {
        position: absolute;
        inset: -8px;
        background-size: cover;
        background-position: center;
        filter: blur(8px) brightness(0.35);
        opacity: 0.8;
        pointer-events: none;
    }

    .sidebar-post-img-box img {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .sidebar-post-placeholder {
        color: var(--muted);
        font-size: 1.2rem;
    }

    .sidebar-post-body { flex: 1; min-width: 0; }
    .sidebar-post-cat {
        font-size: 0.68rem;
        color: var(--muted);
        text-transform: uppercase;
        font-weight: 700;
    }
    .sidebar-post-title {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--cream);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }
    .sidebar-post-price {
        font-size: 0.9rem;
        font-weight: 700;
    }
    .sidebar-sold-badge {
        display: inline-block;
        margin-top: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 7px;
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
        border: 1px solid rgba(255, 107, 43, 0.3);
        border-radius: 100px;
    }

    .sidebar-view-btn {
        padding: 8px 12px;
        background: transparent;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--cream);
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        display: block;
        transition: all 0.2s;
    }
    .sidebar-view-btn:hover {
        border-color: var(--lime);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.06);
    }

    .sidebar-user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 12px;
        margin-top: auto;
    }

    .sidebar-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #1c2e1e;
        color: var(--lime);
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-user-name {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--cream);
    }

    .badge-role-pill {
        padding: 1px 6px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .role-seller {
        background: rgba(255, 107, 43, 0.15);
        color: var(--orange);
        border: 1px solid rgba(255, 107, 43, 0.3);
    }
    .role-buyer {
        background: rgba(194, 240, 60, 0.15);
        color: var(--lime);
        border: 1px solid rgba(194, 240, 60, 0.3);
    }

    /* ── CHAT MAIN ── */
    .chat-main {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #060d07;
        overflow: hidden;
    }

    /* Header */
    .chat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 20px;
        height: 64px;
        background: var(--panel);
        border-bottom: 1px solid var(--line);
        flex-shrink: 0;
    }
    .chat-header-back {
        color: var(--cream);
        font-size: 1.2rem;
        text-decoration: none;
        margin-right: 4px;
    }
    .chat-header-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #1c2e1e;
        color: var(--lime);
        font-size: 0.88rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .chat-header-info { flex: 1; min-width: 0; }
    .chat-header-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--cream);
        line-height: 1.2;
    }
    .chat-header-sub {
        font-size: 0.74rem;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 320px;
    }

    .chat-header-post-link {
        padding: 5px 12px;
        border: 1px solid var(--line);
        background: #081209;
        color: var(--cream);
        border-radius: 8px;
        font-size: 0.76rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .chat-header-post-link:hover {
        border-color: var(--lime);
        color: var(--lime);
    }

    /* Messages Box */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        scroll-behavior: smooth;
        background: radial-gradient(circle at top right, rgba(194, 240, 60, 0.03) 0%, transparent 60%);
    }

    .no-messages-box {
        text-align: center;
        color: var(--muted);
        margin: auto;
        max-width: 340px;
    }
    .no-messages-icon {
        font-size: 2.5rem;
        color: var(--lime);
        opacity: 0.6;
        margin-bottom: 10px;
    }
    .no-messages-box h5 {
        color: var(--cream);
        font-weight: 700;
    }
    .no-messages-box p {
        font-size: 0.84rem;
    }

    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        max-width: 75%;
    }
    .msg-row.mine {
        align-self: flex-end;
        flex-direction: row-reverse;
    }
    .msg-row.theirs {
        align-self: flex-start;
    }

    .msg-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #1c2e1e;
        color: var(--lime);
        font-size: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .msg-bubble {
        padding: 10px 14px;
        border-radius: 16px;
        max-width: 100%;
        word-break: break-word;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .bubble-mine {
        background: #152c18;
        color: var(--cream);
        border: 1px solid rgba(194, 240, 60, 0.25);
        border-bottom-right-radius: 4px;
    }
    .bubble-theirs {
        background: var(--panel);
        color: var(--cream);
        border: 1px solid var(--line);
        border-bottom-left-radius: 4px;
    }

    .msg-body {
        font-size: 0.88rem;
        line-height: 1.5;
        white-space: pre-wrap;
    }
    .msg-meta {
        font-size: 0.68rem;
        margin-top: 4px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        color: var(--muted);
    }

    /* Input */
    .chat-input-area {
        padding: 14px 20px;
        background: var(--panel);
        border-top: 1px solid var(--line);
        flex-shrink: 0;
    }

    .chat-input-form {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .chat-input-wrapper {
        flex: 1;
        background: #081209;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 2px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .chat-input-wrapper:focus-within {
        border-color: var(--lime);
        box-shadow: 0 0 0 3px rgba(194, 240, 60, 0.15);
    }

    .chat-input {
        width: 100%;
        resize: none;
        border: 0;
        background: transparent;
        color: var(--cream);
        padding: 9px 14px;
        font-size: 0.88rem;
        font-family: inherit;
        line-height: 1.45;
        max-height: 120px;
        overflow-y: auto;
        outline: none;
    }
    .chat-input::placeholder {
        color: var(--muted);
    }

    .chat-send-btn {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        background: var(--lime);
        color: var(--ink);
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(194, 240, 60, 0.25);
    }
    .chat-send-btn:hover {
        background: #d7ff62;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(194, 240, 60, 0.35);
    }
    .chat-send-btn:active {
        transform: scale(0.95);
    }
</style>

<script>
    // Auto-scroll to bottom on load
    const box = document.getElementById('messagesBox');
    if (box) box.scrollTop = box.scrollHeight;

    // Auto-resize textarea
    const ta = document.querySelector('.chat-input');
    if (ta) {
        ta.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        // Submit on Ctrl+Enter / Cmd+Enter
        ta.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
</script>
@endsection
