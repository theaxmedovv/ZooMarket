@extends('layouts.app')

@section('content')
<div class="page">
    <x-page-head title="Sotilgan hayvonlar" subtitle="Approved bo'lgan va marketplace'dan yashirilgan hayvonlar ro'yxati.">
        <x-slot:actions>
            <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-outline rounded-full">So'rovlarga qaytish</a>
        </x-slot:actions>
    </x-page-head>

    <x-data-table>
        <thead>
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
                    <td class="font-semibold">{{ $request->animal?->title ?: '—' }}</td>
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
                    <td colspan="4" class="py-12! text-center text-muted">Hozircha sotilgan hayvonlar yo'q.</td>
                </tr>
            @endforelse
        </tbody>

        @if($soldAnimals->hasPages())
            <x-slot:footer>{{ $soldAnimals->links() }}</x-slot:footer>
        @endif
    </x-data-table>
</div>
@endsection
