@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-5">
            <h1 class="fw-bold mb-3">Seller Dashboard</h1>
            <p class="text-muted mb-4">Bu sahifaga faqat seller roli bilan kirish mumkin.</p>
            <a href="{{ route('posts.index') }}" class="btn btn-primary">Postlar sahifasiga o'tish</a>
        </div>
    </div>
</div>
@endsection
