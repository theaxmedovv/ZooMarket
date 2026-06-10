@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <a href="{{ route('posts.index') }}" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Orqaga qaytish
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-5">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="fw-bold mb-2">Hayvon sotuv e'loni</h1>
                    <p class="text-muted mb-4">Quyidagi maydonlarni to'ldirib e'lon joylang.</p>

                    @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-4 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="row g-4">
                        @csrf

                        <div class="col-md-12">
                            <label for="title" class="form-label fw-semibold">E'lon sarlavhasi</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}"
                                   class="form-control form-control-lg bg-light border-0"
                                   placeholder="Masalan: Sog'lom it sotuv" required>
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Kategoriya</label>
                            <select name="category_id" id="category_id" class="form-select form-select-lg bg-light border-0" required>
                                <option value="">Kategoriyani tanlang</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="breed" class="form-label fw-semibold">Zot</label>
                            <input type="text" name="breed" id="breed" value="{{ old('breed') }}"
                                   class="form-control form-control-lg bg-light border-0"
                                   placeholder="Masalan: Labrador" required>
                        </div>

                        <div class="col-md-4">
                            <label for="gender" class="form-label fw-semibold">Jinsi</label>
                            <select name="gender" id="gender" class="form-select form-select-lg bg-light border-0" required>
                                <option value="male" @selected(old('gender') === 'male')>Erkak</option>
                                <option value="female" @selected(old('gender') === 'female')>Urg'ochi</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="age" class="form-label fw-semibold">Yoshi</label>
                            <input type="text" name="age" id="age" value="{{ old('age') }}"
                                   class="form-control form-control-lg bg-light border-0"
                                   placeholder="Masalan: 8 oy" required>
                        </div>

                        <div class="col-md-4">
                            <label for="color" class="form-label fw-semibold">Rangi</label>
                            <input type="text" name="color" id="color" value="{{ old('color') }}"
                                   class="form-control form-control-lg bg-light border-0"
                                   placeholder="Masalan: Qora">
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label fw-semibold">Manzil</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                   class="form-control form-control-lg bg-light border-0"
                                   placeholder="Masalan: Toshkent" required>
                        </div>

                        <div class="col-md-3">
                            <label for="price" class="form-label fw-semibold">Narx</label>
                            <input type="number" step="0.01" min="0" name="price" id="price"
                                   value="{{ old('price') }}"
                                   class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="col-md-3">
                            <label for="currency" class="form-label fw-semibold">Valyuta</label>
                            <select name="currency" id="currency" class="form-select form-select-lg bg-light border-0" required>
                                <option value="UZS" @selected(old('currency', 'UZS') === 'UZS')>UZS</option>
                                <option value="USD" @selected(old('currency') === 'USD')>USD</option>
                                <option value="EUR" @selected(old('currency') === 'EUR')>EUR</option>
                                <option value="RUB" @selected(old('currency') === 'RUB')>RUB</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label fw-semibold">Holat</label>
                            <select name="status" id="status" class="form-select form-select-lg bg-light border-0" required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Aktiv</option>
                                <option value="reserved" @selected(old('status') === 'reserved')>Rezerv</option>
                                <option value="sold" @selected(old('status') === 'sold')>Sotilgan</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_negotiable" id="is_negotiable" value="1"
                                       class="form-check-input" @checked(old('is_negotiable'))>
                                <label for="is_negotiable" class="form-check-label">Narx kelishiladi</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Tavsif</label>
                            <textarea name="description" id="description" rows="4"
                                      class="form-control bg-light border-0"
                                      placeholder="Hayvon haqida batafsil ma'lumot..." required>{{ old('description') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Rasmlar (maksimum 3 ta)</label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="small text-muted mb-1 d-block">1-rasm (asosiy)</label>
                                    <input type="file" name="images[]" class="form-control bg-light border-0" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted mb-1 d-block">2-rasm</label>
                                    <input type="file" name="images[]" class="form-control bg-light border-0" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted mb-1 d-block">3-rasm</label>
                                    <input type="file" name="images[]" class="form-control bg-light border-0" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4">E'lon joylash</button>
                            <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">Bekor qilish</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        border-color: #4361ee;
    }
</style>
@endsection
