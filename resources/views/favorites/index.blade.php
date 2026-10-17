@extends('layouts.app')

@section('content')
<div class="page">
    <x-page-head title="Sevimlilar" :subtitle="$posts->total() . ' ta saqlangan e\'lon'" />

    @if($posts->isEmpty())
        <x-empty-state icon="bi-heart" title="Hozircha saqlangan e'lon yo'q" text="Yoqqan e'lonlardagi yurakcha tugmasini bosing — ular shu yerda to'planadi.">
            <a href="{{ route('posts.index') }}" class="btn btn-primary"><i class="bi bi-compass"></i> E'lonlarni ko'rish</a>
        </x-empty-state>
    @else
        {{-- data-favorites-grid: un-liking a card here removes it from the page (resources/js/app.js) --}}
        <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 md:gap-[18px] lg:grid-cols-4 min-[1200px]:grid-cols-5" data-favorites-grid>
            @foreach($posts as $post)
                @include('partials.home-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
            @endforeach
        </div>

        @if($posts->hasPages())
            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    @endif
</div>
@endsection
