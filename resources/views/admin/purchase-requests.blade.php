@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Sotib olish so'rovlari</h1>
            <p class="text-muted mb-0">Yuborilgan buyurtma so'rovlarini boshqaring.</p>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-pill px-4">Profilga qaytish</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 mb-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User ismi</th>
                        <th>Telefon</th>
                        <th>Hayvon nomi</th>
                        <th>Narxi</th>
                        <th>Status</th>
                        <th>Sana</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td class="fw-semibold">{{ $request->user->name }}</td>
                            <td>{{ $request->user->phone ?: '—' }}</td>
                            <td>{{ $request->animal?->title ?: '—' }}</td>
                            <td>
                                @if($request->animal)
                                    {{ number_format((float) $request->animal->price, 0, '.', ' ') }} {{ $request->animal->currency }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($request->status === 'approved')
                                    <span class="badge bg-success">approved</span>
                                @elseif($request->status === 'rejected')
                                    <span class="badge bg-danger">rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark">pending</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <form action="{{ route('admin.purchase-requests.approve', $request) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" @disabled($request->status !== 'pending')>
                                            Tasdiqlash
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.purchase-requests.reject', $request) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3" @disabled($request->status !== 'pending')>
                                            Rad etish
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Hozircha so'rovlar yo'q.</td>
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
