@extends('layouts.app')

@section('content')
<div class="page">
    <x-page-head title="Sevimlilar" :subtitle="$posts->total() . ' ta saqlangan e\'lon'" />

    @if($posts->isEmpty())
        <x-empty-state icon="bi-heart" title="Hozircha saqlangan e'lon yo'q" text="Yoqqan e'lonlardagi yurakcha tugmasini bosing — ular shu yerda to'planadi.">
            <a href="{{ route('home') }}" class="btn btn-primary"><i class="bi bi-compass"></i> E'lonlarni ko'rish</a>
        </x-empty-state>
    @else
        {{-- data-favorites-grid: un-liking a card here removes it from the page (resources/js/app.js) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" data-favorites-grid>
            @foreach($posts as $post)
                @include('partials.post-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
            @endforeach
        </div>

        @if($posts->hasPages())
            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    @endif
</div>
@endsection
