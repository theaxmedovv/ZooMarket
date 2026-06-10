@extends('layouts.app')

@section('content')
@php
    $me    = auth()->user();
    $other = $chat->otherParticipant($me->id);
    $imgs  = $chat->post?->allImages() ?? [];
@endphp

<div class="chat-page">

    {{-- ── SIDEBAR ── --}}
    <aside class="chat-sidebar">
        <a href="{{ route('chats.index') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Barcha chatlar
        </a>

        {{-- Post card --}}
        @if($chat->post)
        <div class="sidebar-post">
            <div class="sidebar-post-img">
                @if(!empty($imgs))
                    <img src="{{ asset('storage/' . $imgs[0]) }}" alt="">
                @else
                    <div class="sidebar-post-placeholder"><i class="bi bi-image"></i></div>
                @endif
            </div>
            <div class="sidebar-post-body">
                <div class="sidebar-post-title">{{ $chat->post->title }}</div>
                <div class="sidebar-post-price">
                    {{ number_format((float) $chat->post->price, 0, '.', ' ') }}
                    {{ $chat->post->currency }}
                </div>
                @if($chat->post->status === 'sold')
                    <span class="sidebar-sold-badge">Sotilgan</span>
                @endif
            </div>
        </div>
        @if($chat->post)
            <a href="{{ route('posts.show', $chat->post) }}" class="sidebar-view-link">
                E'lonni ko'rish <i class="bi bi-arrow-up-right ms-1"></i>
            </a>
        @endif
        @endif

        {{-- Other participant --}}
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</div>
            <div>
                <div class="sidebar-user-name">{{ $other->name }}</div>
                <div class="sidebar-user-role text-muted" style="font-size:0.72rem;">
                    {{ $other->hasRole('seller') ? 'Sotuvchi' : 'Xaridor' }}
                </div>
            </div>
        </div>
    </aside>

    {{-- ── CHAT MAIN ── --}}
    <div class="chat-main">

        {{-- Header --}}
        <div class="chat-header">
            <a href="{{ route('chats.index') }}" class="chat-header-back d-lg-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="chat-header-avatar">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</div>
            <div>
                <div class="chat-header-name">{{ $other->name }}</div>
                <div class="chat-header-sub">{{ Str::limit($chat->post?->title ?? '', 35) }}</div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-messages" id="messagesBox">
            @if($chat->messages->isEmpty())
                <div class="no-messages">
                    <i class="bi bi-chat-dots"></i>
                    <p>Suhbatni boshlang!</p>
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
                                {{ $msg->created_at->format('H:i') }}
                                @if($isMine)
                                    @if($msg->read_at)
                                        <i class="bi bi-check2-all ms-1" style="color:#86efac;"></i>
                                    @else
                                        <i class="bi bi-check2 ms-1" style="opacity:0.5;"></i>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Input --}}
        <form action="{{ route('chats.messages.store', $chat) }}" method="POST" class="chat-input-form">
            @csrf
            <textarea name="body"
                      class="chat-input {{ $errors->has('body') ? 'is-invalid' : '' }}"
                      placeholder="Xabar yozing..."
                      rows="1"
                      required
                      maxlength="2000">{{ old('body') }}</textarea>
            <button type="submit" class="chat-send-btn">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        @error('body')
            <div class="px-3 pb-2 text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>

<style>
:root {
    --g: #16a34a; --g-mid: #15803d;
    --g-soft: #f0fdf4; --g-pale: #dcfce7; --g-border: #bbf7d0;
    --text: #0f172a; --text-2: #475569; --text-3: #94a3b8;
    --border: rgba(15,23,42,0.08);
    --radius: 14px; --radius-sm: 9px;
    --header-h: 64px;
    --input-h: 68px;
}

/* Layout */
.chat-page {
    display: grid;
    grid-template-columns: 260px 1fr;
    height: calc(100vh - var(--header-h));
    background: #f8fafc;
    overflow: hidden;
}
@media (max-width: 768px) {
    .chat-page { grid-template-columns: 1fr; }
    .chat-sidebar { display: none; }
}

/* ── SIDEBAR ── */
.chat-sidebar {
    background: white;
    border-right: 1px solid var(--border);
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    overflow-y: auto;
}

.back-link {
    font-size: 0.82rem; font-weight: 600;
    color: var(--text-3); text-decoration: none;
    display: flex; align-items: center; gap: 6px;
    transition: color 0.15s;
}
.back-link:hover { color: var(--g); }

.sidebar-post {
    display: flex; gap: 10px; align-items: flex-start;
}
.sidebar-post-img {
    width: 52px; height: 52px;
    border-radius: var(--radius-sm);
    overflow: hidden; flex-shrink: 0;
    background: #f1f5f9;
}
.sidebar-post-img img { width:100%; height:100%; object-fit:cover; display:block; }
.sidebar-post-placeholder {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    color:var(--text-3); font-size:1rem;
}
.sidebar-post-body { flex: 1; min-width: 0; }
.sidebar-post-title {
    font-size: 0.82rem; font-weight: 700; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 2px;
}
.sidebar-post-price { font-size: 0.875rem; font-weight: 700; color: var(--g); }
.sidebar-sold-badge {
    display: inline-block; margin-top: 4px;
    font-size: 0.68rem; font-weight: 700; padding: 2px 8px;
    background: #f1f5f9; color: var(--text-3); border-radius: 100px;
}

.sidebar-view-link {
    font-size: 0.78rem; font-weight: 600; color: var(--g);
    text-decoration: none; display: flex; align-items: center;
    transition: color 0.15s;
}
.sidebar-view-link:hover { color: var(--g-mid); }

.sidebar-user {
    display: flex; align-items: center; gap: 10px;
    padding: 12px; background: var(--g-soft);
    border: 1px solid var(--g-border); border-radius: var(--radius-sm);
    margin-top: auto;
}
.sidebar-user-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--g); color: white;
    font-size: 0.85rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sidebar-user-name { font-size: 0.875rem; font-weight: 600; color: var(--text); }

/* ── CHAT MAIN ── */
.chat-main {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

/* Header */
.chat-header {
    display: flex; align-items: center; gap: 12px;
    padding: 0 20px; height: 60px;
    background: white; border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.chat-header-back {
    color: var(--text-2); font-size: 1.1rem;
    text-decoration: none; margin-right: 4px;
}
.chat-header-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--g); color: white;
    font-size: 0.85rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.chat-header-name { font-size: 0.9rem; font-weight: 700; color: var(--text); line-height: 1.2; }
.chat-header-sub  { font-size: 0.72rem; color: var(--text-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }

/* Messages */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px 20px 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}

.no-messages {
    text-align: center;
    color: var(--text-3);
    margin: auto;
    font-size: 0.875rem;
}
.no-messages i { font-size: 2rem; display: block; margin-bottom: 8px; }

.msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 75%;
}
.msg-row.mine   { align-self: flex-end; flex-direction: row-reverse; }
.msg-row.theirs { align-self: flex-start; }

.msg-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--g-pale); border: 1px solid var(--g-border);
    color: var(--g); font-size: 0.7rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.msg-bubble {
    padding: 9px 13px;
    border-radius: 16px;
    max-width: 100%;
    word-break: break-word;
}
.bubble-mine {
    background: var(--g); color: white;
    border-bottom-right-radius: 4px;
}
.bubble-theirs {
    background: white; color: var(--text);
    border: 1px solid var(--border);
    border-bottom-left-radius: 4px;
}

.msg-body { font-size: 0.875rem; line-height: 1.5; white-space: pre-wrap; }
.msg-meta {
    font-size: 0.68rem; margin-top: 4px;
    display: flex; align-items: center; justify-content: flex-end;
    gap: 2px;
}
.bubble-mine   .msg-meta { color: rgba(255,255,255,0.65); }
.bubble-theirs .msg-meta { color: var(--text-3); }

/* Input */
.chat-input-form {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    padding: 12px 16px;
    background: white;
    border-top: 1px solid var(--border);
    flex-shrink: 0;
}

.chat-input {
    flex: 1;
    resize: none;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 10px 16px;
    font-size: 0.875rem;
    font-family: inherit;
    line-height: 1.5;
    max-height: 120px;
    overflow-y: auto;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    background: #f8fafc;
    color: var(--text);
}
.chat-input:focus {
    border-color: var(--g);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
    background: white;
}

.chat-send-btn {
    width: 42px; height: 42px; flex-shrink: 0;
    background: var(--g); color: white; border: none;
    border-radius: 50%; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background 0.15s, transform 0.1s;
}
.chat-send-btn:hover  { background: var(--g-mid); }
.chat-send-btn:active { transform: scale(0.93); }
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
