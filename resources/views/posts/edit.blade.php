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
                        <p class="opacity-75">Hayvon e'loni ma'lumotlarini yangilang.</p>
                    </div>

                    <div class="col-md-8 bg-white p-5">
                        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            @if($errors->any())
                                <div class="alert alert-danger border-0 rounded-4 mb-4">
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="title" class="form-label fw-bold text-dark">Maqola sarlavhasi</label>
                                <input type="text" name="title" value="{{ old('title', $post->title) }}"
                                       class="form-control form-control-lg border-0 bg-light rounded-4 px-4"
                                       placeholder="Sarlavhani kiriting..." required>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label fw-bold text-dark">Kategoriya</label>
                                    <select name="category_id" id="category_id" class="form-select form-select-lg border-0 bg-light rounded-4 px-4" required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="breed" class="form-label fw-bold text-dark">Zot</label>
                                    <input type="text" name="breed" id="breed" value="{{ old('breed', $post->breed) }}" class="form-control form-control-lg border-0 bg-light rounded-4 px-4" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="gender" class="form-label fw-bold text-dark">Jinsi</label>
                                    <select name="gender" id="gender" class="form-select form-select-lg border-0 bg-light rounded-4 px-4" required>
                                        <option value="male" @selected(old('gender', $post->gender) === 'male')>Erkak</option>
                                        <option value="female" @selected(old('gender', $post->gender) === 'female')>Urg'ochi</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="age" class="form-label fw-bold text-dark">Yoshi</label>
                                    <input type="text" name="age" id="age" value="{{ old('age', $post->age) }}" class="form-control form-control-lg border-0 bg-light rounded-4 px-4" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="color" class="form-label fw-bold text-dark">Rangi</label>
                                    <input type="text" name="color" id="color" value="{{ old('color', $post->color) }}" class="form-control form-control-lg border-0 bg-light rounded-4 px-4">
                                </div>
                                <div class="col-md-6">
                                    <label for="price" class="form-label fw-bold text-dark">Narx</label>
                                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $post->price) }}" class="form-control form-control-lg border-0 bg-light rounded-4 px-4" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="currency" class="form-label fw-bold text-dark">Valyuta</label>
                                    <select name="currency" id="currency" class="form-select form-select-lg border-0 bg-light rounded-4 px-4" required>
                                        <option value="UZS" @selected(old('currency', $post->currency) === 'UZS')>UZS</option>
                                        <option value="USD" @selected(old('currency', $post->currency) === 'USD')>USD</option>
                                        <option value="EUR" @selected(old('currency', $post->currency) === 'EUR')>EUR</option>
                                        <option value="RUB" @selected(old('currency', $post->currency) === 'RUB')>RUB</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label fw-bold text-dark">Holat</label>
                                    <select name="status" id="status" class="form-select form-select-lg border-0 bg-light rounded-4 px-4" required>
                                        <option value="active" @selected(old('status', $post->status) === 'active')>Active</option>
                                        <option value="reserved" @selected(old('status', $post->status) === 'reserved')>Reserved</option>
                                        <option value="sold" @selected(old('status', $post->status) === 'sold')>Sold</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="location" class="form-label fw-bold text-dark">Joylashuv</label>
                                    <input type="text" name="location" id="location" value="{{ old('location', $post->location) }}" class="form-control form-control-lg border-0 bg-light rounded-4 px-4" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="is_negotiable" name="is_negotiable" @checked(old('is_negotiable', $post->is_negotiable))>
                                        <label class="form-check-label" for="is_negotiable">Narx kelishiladi</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label fw-bold text-dark">Tavsif</label>
                                <textarea name="description" class="form-control border-0 bg-light rounded-4 px-4 py-3"
                                          rows="6" placeholder="Hayvon haqida yozing..." required>{{ old('description', $post->description ?? $post->content) }}</textarea>
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
