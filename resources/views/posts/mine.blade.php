@extends('layouts.app')

@section('content')
<div class="page">
    <x-page-head title="Mening e'lonlarim"
        :subtitle="$search !== '' ? '“' . $search . '” bo\'yicha: ' . $posts->total() . ' ta e\'lon' : $posts->total() . ' ta e\'lon'" />

    {{-- Every moderation state is shown here, with its badge; only approved listings reach the home page. --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
    @forelse($posts as $post)
        @include('partials.post-card', ['post' => $post, 'likedPostIds' => [], 'showModeration' => true])
    @empty
        @if($search !== '')
            <x-empty-state class="col-span-full" icon="bi-search" title="Hech narsa topilmadi" text="Boshqa so'z bilan qidirib ko'ring.">
                <a href="{{ route('posts.mine') }}" class="btn btn-primary">Qidiruvni tozalash</a>
            </x-empty-state>
        @else
            <x-empty-state class="col-span-full" icon="bi-collection" title="Sizda hali faol e'lonlar yo'q" text="Birinchi e'loningizni joylashtiring va xaridorlarga taklif qiling!">
                <a href="{{ route('posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Yangi e'lon qo'shish</a>
            </x-empty-state>
        @endif
    @endforelse
    </div>

    @if($posts->hasPages())
        <div class="mt-12">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
