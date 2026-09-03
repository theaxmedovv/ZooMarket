
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

                            @if($post->moderation_status === 'rejected')
                                <div class="alert alert-danger border-danger border-opacity-50 rounded-4 p-3 mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle p-2 bg-danger bg-opacity-25 text-danger flex-shrink-0">
                                            <i class="bi bi-shield-x fs-4"></i>
                                        </div>
                                        <div>
                                            <strong class="text-danger d-block">E'lon avval Groq AI tomonidan rad etilgan</strong>
                                            <div class="small mt-1 text-muted">
                                                <strong>Rad etilish sababi:</strong> {{ $post->moderation_reason }}
                                            </div>
                                            <div class="small text-muted mt-1">
                                                E'lon ma'lumotlari yoki fotosuratlarini to'g'rilab saqlasangiz, u avtomatik ravishda qayta tekshiruvdan o'tkaziladi.
                                            </div>
                                        </div>
                                    </div>
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
                                <div class="col-md-3">
                                    <label for="quantityEdit" class="form-label fw-bold text-dark">Jami soni</label>
                                    <input type="number" name="quantity" id="quantityEdit" min="1" max="100" value="{{ old('quantity', $post->quantity) }}" class="form-control form-control-lg border-0 bg-light rounded-4 px-4" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="genderEdit" class="form-label fw-bold text-dark">Jinsi</label>
                                    <select name="gender" id="genderEdit" class="form-select form-select-lg border-0 bg-light rounded-4 px-4" required>
                                        <option value="male" @selected(old('gender', $post->gender) === 'male')>Erkak</option>
                                        <option value="female" @selected(old('gender', $post->gender) === 'female')>Urg'ochi</option>
                                        <option value="mixed" @selected(old('gender', $post->gender) === 'mixed') id="optMixedEdit">Aralash (Mixed)</option>
                                    </select>
                                </div>
                                <div class="col-12 {{ old('gender', $post->gender) === 'mixed' ? '' : 'd-none' }}" id="mixedBoxEdit">
                                    <div class="p-3 bg-light rounded-4 border">
                                        <div class="fw-semibold small mb-2 text-dark">Aralash jinslar soni taqsimoti:</div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Erkaklar soni</label>
                                                <input type="number" name="male_quantity" id="maleQtyEdit" value="{{ old('male_quantity', $post->male_quantity) }}" class="form-control rounded-3" min="1" max="99">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Urg'ochilar soni</label>
                                                <input type="number" name="female_quantity" id="femaleQtyEdit" value="{{ old('female_quantity', $post->female_quantity) }}" class="form-control rounded-3" min="1" max="99">
                                            </div>
                                        </div>
                                    </div>
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
                                <label class="form-label fw-bold text-dark">Rasmlar (maksimum 3 ta)</label>
                                @php $currentImages = $post->allImages(); @endphp
                                @if(!empty($currentImages))
                                    <div class="d-flex gap-2 mb-3 flex-wrap">
                                        @foreach($currentImages as $img)
                                            <img src="{{ route('images.show', ['path' => $img]) }}" class="rounded-3 shadow-sm" style="height:80px;width:80px;object-fit:cover;">
                                        @endforeach
                                    </div>
                                    <p class="small text-muted mb-3">Yangi rasmlar yuklasangiz, mavjud rasmlar almashtiriladi.</p>
                                @endif
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-1 d-block">1-rasm (asosiy)</label>
                                        <input type="file" name="images[]" class="form-control border-0 bg-light" accept="image/*">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-1 d-block">2-rasm</label>
                                        <input type="file" name="images[]" class="form-control border-0 bg-light" accept="image/*">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small text-muted mb-1 d-block">3-rasm</label>
                                        <input type="file" name="images[]" class="form-control border-0 bg-light" accept="image/*">
                                    </div>
                                </div>
                                <p class="text-muted mt-2" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1"></i>Max: 2MB har bir rasm</p>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('quantityEdit');
    const genderSelect = document.getElementById('genderEdit');
    const optMixed = document.getElementById('optMixedEdit');
    const mixedBox = document.getElementById('mixedBoxEdit');
    const maleQty = document.getElementById('maleQtyEdit');
    const femaleQty = document.getElementById('femaleQtyEdit');

    function syncEdit() {
        const qty = parseInt(qtyInput.value) || 1;
        if (qty === 1) {
            optMixed.disabled = true;
            if (genderSelect.value === 'mixed') {
                genderSelect.value = 'male';
            }
        } else {
            optMixed.disabled = false;
        }

        if (genderSelect.value === 'mixed' && qty > 1) {
            mixedBox.classList.remove('d-none');
            maleQty.required = true;
            femaleQty.required = true;
        } else {
            mixedBox.classList.add('d-none');
            maleQty.required = false;
            femaleQty.required = false;
        }
    }

    if (qtyInput) qtyInput.addEventListener('input', syncEdit);
    if (genderSelect) genderSelect.addEventListener('change', syncEdit);
    syncEdit();
});
</script>
@endsection
