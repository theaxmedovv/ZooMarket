@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="{{ route('posts.index') }}" class="text-decoration-none text-muted small hover-primary">
                    <i class="bi bi-arrow-left me-1"></i> Orqaga qaytish
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-5 overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-4 bg-primary p-5 text-white d-flex flex-column justify-content-center">
                        <i class="bi bi-pencil-square display-4 mb-3"></i>
                        <h2 class="fw-bold">Tahrirlash</h2>
                        <p class="opacity-75">Maqola sarlavhasi va mazmunini o'zgartiring. Yangi rasm yuklasangiz, eskisi o'chiriladi.</p>
                    </div>

                    <div class="col-md-8 bg-white p-5">
                        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label for="title" class="form-label fw-bold text-dark">Maqola sarlavhasi</label>
                                <input type="text" name="title" value="{{ $post->title }}"
                                       class="form-control form-control-lg border-0 bg-light rounded-4 px-4"
                                       placeholder="Sarlavhani kiriting..." required>
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label fw-bold text-dark">Asosiy matn</label>
                                <textarea name="content" class="form-control border-0 bg-light rounded-4 px-4 py-3"
                                          rows="6" placeholder="Maqola mazmunini yozing..." required>{{ $post->content }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-bold text-dark">Muqova rasmi</label>

                                <div class="p-3 border-2 border-dashed rounded-4 bg-light text-center position-relative mb-3">
                                    @if($post->image)
                                        <div class="current-image-preview mb-3">
                                            <p class="small text-muted mb-2">Hozirgi rasm:</p>
                                            <img src="{{ asset('storage/' . $post->image) }}" class="rounded-3 shadow-sm" style="max-height: 120px;">
                                        </div>
                                    @endif

                                    <div class="upload-btn-wrapper">
                                        <i class="bi bi-cloud-arrow-up fs-2 text-primary"></i>
                                        <p class="small text-muted mt-2">Yangi rasm tanlash uchun bosing yoki faylni bu yerga tashlang</p>
                                        <input type="file" name="image" class="form-control stretched-link opacity-0" accept="image/*" style="cursor: pointer;">
                                    </div>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle me-1"></i> Tavsiya etilgan o'lcham: 1200x630px (Max: 2MB)
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                    Saqlash
                                </button>
                                <a href="{{ route('posts.index') }}" class="btn btn-light btn-lg rounded-pill px-4 text-muted border-0">
                                    Bekor qilish
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Maxsus dizayn elementlari */
    .border-dashed {
        border-style: dashed !important;
        border-color: #dee2e6 !important;
        transition: all 0.3s ease;
    }
    .border-dashed:hover {
        border-color: #0d6efd !important;
        background-color: #f1f4f9 !important;
    }
    .form-control:focus {
        box-shadow: none;
        background-color: #fff !important;
        border: 1px solid #0d6efd !important;
    }
    .hover-primary:hover {
        color: #0d6efd !important;
    }
    /* Kirish animatsiyasi */
    .card {
        animation: slideUp 0.6s ease-out;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
