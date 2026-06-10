@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px;">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-0" style="font-size:1.5rem;">Xabarlar</h1>
            <p class="text-muted small mb-0">Savdo bo'yicha shaxsiy chatlar</p>
        </div>
    </div>

    @if($chats->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-chat-dots"></i></div>
            <h5 class="fw-bold mb-1">Hozircha xabar yo'q</h5>
            <p class="text-muted small mb-0">Savdo tasdiqlangandan so'ng chat avtomatik ochiladi.</p>
        </div>
    @else
        <div class="chat-list">
            @foreach($chats as $chat)
                @php
                    $other   = $chat->otherParticipant(auth()->id());
                    $last    = $chat->lastMessage;
                    $isMine  = $last && $last->sender_id === auth()->id();
                    $unread  = $chat->unread;
                @endphp
                <a href="{{ route('chats.show', $chat) }}" class="chat-item {{ $unread > 0 ? 'has-unread' : '' }}">
                    {{-- Post thumbnail --}}
                    <div class="chat-thumb">
                        @php $img = $chat->post?->allImages()[0] ?? null; @endphp
                        @if($img)
                            <img src="{{ asset('storage/' . $img) }}" alt="">
                        @else
                            <div class="chat-thumb-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="chat-info">
                        <div class="chat-top">
                            <span class="chat-post-title">{{ Str::limit($chat->post?->title ?? '—', 40) }}</span>
                            <span class="chat-time">{{ $last ? $last->created_at->diffForHumans(null, true) : '' }}</span>
                        </div>
                        <div class="chat-bottom">
                            <div class="chat-user">
                                <div class="chat-user-avatar">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</div>
                                <span class="chat-user-name">{{ $other->name }}</span>
                            </div>
                            <div class="chat-preview-wrap">
                                @if($last)
                                    <span class="chat-preview {{ $unread > 0 ? 'unread' : '' }}">
                                        {{ $isMine ? 'Siz: ' : '' }}{{ Str::limit($last->body, 50) }}
                                    </span>
                                @else
                                    <span class="chat-preview text-muted fst-italic">Xabar yo'q</span>
                                @endif
                                @if($unread > 0)
                                    <span class="unread-dot">{{ $unread }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<style>
:root {
    --g: #16a34a; --g-mid: #15803d;
    --g-soft: #f0fdf4; --g-pale: #dcfce7; --g-border: #bbf7d0;
    --text: #0f172a; --text-2: #475569; --text-3: #94a3b8;
    --border: rgba(15,23,42,0.08);
    --radius: 14px; --radius-sm: 9px;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border: 1px dashed rgba(22,163,74,0.2);
    border-radius: var(--radius);
}
.empty-icon {
    width: 56px; height: 56px;
    background: var(--g-soft); border: 1px solid var(--g-border); color: var(--g);
    font-size: 1.5rem; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
}

/* Chat list */
.chat-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.chat-item {
    display: flex;
    gap: 14px;
    align-items: center;
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 16px;
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.15s, border-color 0.15s, background 0.15s;
}
.chat-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); border-color: var(--g-border); }
.chat-item.has-unread { background: var(--g-soft); border-color: var(--g-border); }

/* Thumbnail */
.chat-thumb {
    width: 56px; height: 56px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    flex-shrink: 0;
    background: #f1f5f9;
}
.chat-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.chat-thumb-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-3); font-size: 1.2rem;
}

/* Info */
.chat-info { flex: 1; min-width: 0; }

.chat-top {
    display: flex; justify-content: space-between; align-items: baseline;
    margin-bottom: 6px;
}
.chat-post-title {
    font-size: 0.9rem; font-weight: 700; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 70%;
}
.chat-time { font-size: 0.72rem; color: var(--text-3); white-space: nowrap; flex-shrink: 0; }

.chat-bottom { display: flex; justify-content: space-between; align-items: center; gap: 8px; }

.chat-user { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.chat-user-avatar {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--g-pale); border: 1px solid var(--g-border);
    color: var(--g); font-size: 0.65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.chat-user-name { font-size: 0.78rem; color: var(--text-2); font-weight: 500; }

.chat-preview-wrap { display: flex; align-items: center; gap: 8px; min-width: 0; }
.chat-preview {
    font-size: 0.8rem; color: var(--text-3);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.chat-preview.unread { color: var(--text-2); font-weight: 600; }

.unread-dot {
    background: var(--g); color: white;
    font-size: 0.65rem; font-weight: 700;
    min-width: 18px; height: 18px; border-radius: 100px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 5px; flex-shrink: 0;
}
</style>
@endsection
