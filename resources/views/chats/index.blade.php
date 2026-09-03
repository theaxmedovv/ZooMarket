@extends('layouts.app')

@section('content')
<div class="container py-4 page-shell" style="max-width: 860px;">
    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-lime-soft text-lime fw-bold px-2 py-1 rounded-pill">
                    <i class="bi bi-chat-dots-fill me-1"></i> Xabarlar markazi
                </span>
                <h1 class="h3 fw-bold mb-0 text-cream font-serif">Xabarlar</h1>
            </div>
            <p class="text-muted small mb-0">Savdo va buyurtmalar bo'yicha barcha shaxsiy suhbatlaringiz</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('posts.index') }}" class="btn-panel-link">
                <i class="bi bi-compass me-1"></i> E'lonlarni ko'rish
            </a>
            @if(auth()->user()->hasRole('seller'))
                <a href="{{ route('admin.purchase-requests.index') }}" class="btn-panel-link">
                    <i class="bi bi-inbox me-1"></i> So'rovlar
                </a>
            @else
                <a href="{{ route('user.purchase-requests.index') }}" class="btn-panel-link">
                    <i class="bi bi-bag-check me-1"></i> Buyurtmalarim
                </a>
            @endif
        </div>
    </div>

    @if($chats->isEmpty())
        <div class="empty-state py-5">
            <div class="empty-icon"><i class="bi bi-chat-square-dots"></i></div>
            <h3 class="empty-title">Hozircha xabarlar yo'q</h3>
            <p class="empty-text">Xarid so'rovi tasdiqlangandan so'ng, ushbu sahifada sotuvchi va xaridor o'rtasida avtomatik chat ochiladi.</p>
            <a href="{{ route('posts.index') }}" class="btn-filter-apply d-inline-flex mt-3 text-decoration-none px-4">
                <i class="bi bi-compass me-1"></i> Marketplace'ga o'tish
            </a>
        </div>
    @else
        <div class="chat-list-wrapper">
            @foreach($chats as $chat)
                @php
                    $other   = $chat->otherParticipant(auth()->id());
                    $last    = $chat->lastMessage;
                    $isMine  = $last && $last->sender_id === auth()->id();
                    $unread  = $chat->unread;
                    $isSeller = $other->hasRole('seller');
                    $img = $chat->post?->allImages()[0] ?? null;
                @endphp
                <a href="{{ route('chats.show', $chat) }}" class="chat-item-card {{ $unread > 0 ? 'has-unread' : '' }}">
                    {{-- Post Thumbnail --}}
                    <div class="chat-thumb-box">
                        @if($img)
                            <div class="chat-thumb-backdrop" style="background-image: url('{{ route('images.show', ['path' => $img]) }}');"></div>
                            <img src="{{ route('images.show', ['path' => $img]) }}" alt="{{ $chat->post?->title ?? '' }}">
                        @else
                            <div class="chat-thumb-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                    </div>

                    {{-- Main Info --}}
                    <div class="chat-content-body">
                        <div class="chat-top-row">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="chat-post-title text-cream">
                                    {{ $chat->post?->title ?? 'E\'lon' }}
                                </span>
                                @if($chat->post?->category)
                                    <span class="badge-cat-tag">
                                        {{ $chat->post->category->name }}
                                    </span>
                                @endif
                                @if($chat->post && $chat->post->price)
                                    <span class="chat-price-pill font-serif text-lime">
                                        {{ number_format((float) $chat->post->price, 0, '.', ' ') }} {{ $chat->post->currency }}
                                    </span>
                                @if(!empty($chat->is_closed))
                                    <span class="badge bg-secondary bg-opacity-25 text-muted border border-secondary border-opacity-25" style="font-size: 0.68rem;">
                                        <i class="bi bi-lock-fill me-1"></i> Yopilgan
                                    </span>
                                @endif
                            </div>
                            <span class="chat-time-tag">
                                {{ $last ? $last->created_at->diffForHumans(null, true) : $chat->updated_at->diffForHumans(null, true) }}
                            </span>
                        </div>

                        <div class="chat-bottom-row">
                            <div class="chat-participant-info">
                                <div class="participant-avatar">
                                    {{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}
                                </div>
                                <div class="participant-details">
                                    <span class="participant-name">{{ $other->name }}</span>
                                    <span class="badge-role-pill {{ $isSeller ? 'role-seller' : 'role-buyer' }}">
                                        {{ $isSeller ? 'Sotuvchi' : 'Xaridor' }}
                                    </span>
                                </div>
                            </div>

                            <div class="chat-msg-preview-wrap">
                                @if($last)
                                    <span class="chat-last-msg {{ $unread > 0 ? 'unread-msg' : '' }}">
                                        @if($isMine)
                                            <span class="text-lime fw-bold me-1">Siz:</span>
                                        @endif
                                        {{ Str::limit($last->body, 55) }}
                                    </span>
                                @else
                                    <span class="chat-last-msg text-muted fst-italic">Hali xabarlar yo'q. Suhbatni boshlang!</span>
                                @endif

                                @if($unread > 0)
                                    <span class="chat-unread-badge">{{ $unread }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="chat-arrow-indicator">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<style>
    .font-serif { font-family: var(--serif); }
    .text-cream { color: var(--cream) !important; }
    .text-lime { color: var(--lime) !important; }
    .bg-lime-soft { background: rgba(194, 240, 60, 0.12) !important; }

    .btn-panel-link {
        padding: 6px 14px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--cream);
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-panel-link:hover {
        border-color: var(--lime);
        color: var(--lime);
        background: rgba(194, 240, 60, 0.08);
    }

    /* Chat List */
    .chat-list-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .chat-item-card {
        display: flex;
        align-items: center;
        gap: 16px;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 16px 20px;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .chat-item-card:hover {
        border-color: rgba(194, 240, 60, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        color: inherit;
    }

    .chat-item-card.has-unread {
        border-left: 4px solid var(--lime);
        background: rgba(13, 24, 14, 0.95);
    }

    /* Thumbnail */
    .chat-thumb-box {
        width: 72px;
        height: 64px;
        border-radius: 12px;
        overflow: hidden;
        background: #040905;
        border: 1px solid var(--line);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .chat-thumb-backdrop {
        position: absolute;
        inset: -8px;
        background-size: cover;
        background-position: center;
        filter: blur(8px) brightness(0.35);
        opacity: 0.8;
        pointer-events: none;
    }

    .chat-thumb-box img {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .chat-thumb-placeholder {
        color: var(--muted);
        font-size: 1.4rem;
    }

    /* Content Body */
    .chat-content-body {
        flex: 1;
        min-width: 0;
    }

    .chat-top-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 8px;
        gap: 10px;
    }

    .chat-post-title {
        font-size: 0.95rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 280px;
    }

    .badge-cat-tag {
        background: rgba(255, 255, 255, 0.05);
        color: var(--muted);
        border: 1px solid var(--line);
        border-radius: 6px;
        padding: 2px 7px;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .chat-price-pill {
        font-size: 0.84rem;
        font-weight: 700;
    }

    .chat-time-tag {
        font-size: 0.72rem;
        color: var(--muted);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .chat-bottom-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .chat-participant-info {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .participant-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #1c2e1e;
        color: var(--lime);
        display: grid;
        place-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .participant-details {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .participant-name {
        font-size: 0.82rem;
        font-weight: 600;
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

    .chat-msg-preview-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .chat-last-msg {
        font-size: 0.8rem;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 280px;
    }

    .chat-last-msg.unread-msg {
        color: var(--cream);
        font-weight: 600;
    }

    .chat-unread-badge {
        background: var(--lime);
        color: var(--ink);
        font-size: 0.68rem;
        font-weight: 800;
        min-width: 20px;
        height: 20px;
        border-radius: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        flex-shrink: 0;
        box-shadow: 0 0 10px rgba(194, 240, 60, 0.4);
    }

    .chat-arrow-indicator {
        color: var(--muted);
        font-size: 0.9rem;
        transition: transform 0.2s, color 0.2s;
    }

    .chat-item-card:hover .chat-arrow-indicator {
        color: var(--lime);
        transform: translateX(3px);
    }

    @media (max-width: 767px) {
        .chat-item-card {
            padding: 12px 14px;
            gap: 12px;
        }
        .chat-thumb-box {
            width: 58px;
            height: 54px;
        }
        .chat-post-title {
            max-width: 180px;
        }
        .chat-last-msg {
            max-width: 180px;
        }
    }
</style>
@endsection
