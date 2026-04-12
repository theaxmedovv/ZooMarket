@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Sotilgan hayvonlar</h1>
            <p class="text-muted mb-0">Approved bo'lgan va marketplace'dan yashirilgan hayvonlar ro'yxati.</p>
        </div>
        <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-outline-secondary rounded-pill px-4">So'rovlarga qaytish</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hayvon nomi</th>
                        <th>Sotib olgan user</th>
                        <th>Narxi</th>
                        <th>Sana</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($soldAnimals as $request)
                        <tr>
                            <td class="fw-semibold">{{ $request->animal?->title ?: '—' }}</td>
                            <td>{{ $request->user?->name ?: '—' }}</td>
                            <td>
                                @if($request->animal)
                                    {{ number_format((float) $request->animal->price, 0, '.', ' ') }} {{ $request->animal->currency }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $request->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Hozircha sotilgan hayvonlar yo'q.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($soldAnimals->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $soldAnimals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
