{{-- A batch of listing cards: the first page in a grid, and each "Yana 12 tasini ko'rish" step appended to it. --}}
@foreach($posts as $post)
    @include('partials.post-card', ['post' => $post, 'likedPostIds' => $likedPostIds])
@endforeach
