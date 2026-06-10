@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">So'rovlarim</h1>
            <p class="text-muted mb-0">Siz yuborgan sotib olish so'rovlari ro'yxati.</p>
        </div>
        <a href="{{ route('user.profile.show') }}" class="btn btn-outline-secondary rounded-pill px-4">Profilga qaytish</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 mb-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hayvon</th>
                        <th>Narx</th>
                        <th>Status</th>
                        <th>Sana</th>
                        <th class="text-end">Amal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td class="fw-semibold">{{ $request->animal?->title ?: '—' }}</td>
                            <td>
                                @if($request->animal)
                                    {{ number_format((float) $request->animal->price, 0, '.', ' ') }} {{ $request->animal->currency }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($request->status === 'approved')
                                    <span class="badge bg-success">Tasdiqlangan</span>
                                @elseif($request->status === 'rejected')
                                    <span class="badge bg-danger">Rad etilgan</span>
                                @else
                                    <span class="badge bg-warning text-dark">Kutilmoqda</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    @if($request->animal)
                                        <a href="{{ route('posts.show', $request->animal) }}"
                                           class="btn btn-sm btn-outline-secondary rounded-pill px-3">Batafsil</a>
                                    @endif
                                    @if($request->status === 'approved' && $request->chat)
                                        @php $unread = $request->chat->unreadCountFor(auth()->id()); @endphp
                                        <a href="{{ route('chats.show', $request->chat) }}"
                                           class="btn btn-sm btn-success rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-chat-dots"></i> Chat
                                            @if($unread > 0)
                                                <span class="badge bg-white text-success" style="font-size:0.65rem;">{{ $unread }}</span>
                                            @endif
                                        </a>
                                    @endif
                                    @if(!$request->animal && $request->status !== 'approved')
                                        <span class="text-muted small">Mavjud emas</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Siz hali so'rov yubormagansiz.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
