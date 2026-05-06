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

                        <!-- Title is fixed as a hidden field -->
                        <input type="hidden" name="title" value="🐾 YANGI E'LON">

                        <div class="col-md-12">
                            <div class="alert alert-info border-0 rounded-4 mb-0">
                                <h4 class="mb-0"><strong>🐾 YANGI E'LON</strong></h4>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="category" class="form-label fw-semibold">Kategoriya</label>
                            <input type="text" name="category" id="category" value="{{ old('category') }}" class="form-control form-control-lg bg-light border-0" placeholder="Kategoriya kiriting" required>
                        </div>

                        <div class="col-md-6">
                            <label for="breed" class="form-label fw-semibold">Zot</label>
                            <input type="text" name="breed" id="breed" value="{{ old('breed') }}" class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="col-md-6">
                            <label for="quantity" class="form-label fw-semibold">Hayvonlar Soni</label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" class="form-control form-control-lg bg-light border-0" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label for="gender" class="form-label fw-semibold">Jinsi</label>
                            <select name="gender" id="gender" class="form-select form-select-lg bg-light border-0" required>
                                <option value="male" @selected(old('gender') === 'male')>Erkak</option>
                                <option value="female" @selected(old('gender') === 'female')>Ayol</option>
                                <option value="mixed" @selected(old('gender') === 'mixed')>Erkak va Ayol</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="age" class="form-label fw-semibold">Yoshi</label>
                            <input type="text" name="age" id="age" value="{{ old('age') }}" class="form-control form-control-lg bg-light border-0" placeholder="Masalan: 8 oy" required>
                        </div>

                        <div class="col-md-6">
                            <label for="health" class="form-label fw-semibold">Sog'lig'i</label>
                            <input type="text" name="health" id="health" value="{{ old('health') }}" class="form-control form-control-lg bg-light border-0" placeholder="Masalan: Sog'lom, Vaksinalar..." required>
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label fw-semibold">Manzil</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}" class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="col-md-6">
                            <label for="delivery" class="form-label fw-semibold">Dostavka</label>
                            <input type="text" name="delivery" id="delivery" value="{{ old('delivery') }}" class="form-control form-control-lg bg-light border-0" placeholder="Masalan: Bepul, To'lanadigan..." required>
                        </div>

                        <div class="col-md-4">
                            <label for="price" class="form-label fw-semibold">Narx</label>
                            <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price') }}" class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="col-md-4">
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
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="reserved" @selected(old('status') === 'reserved')>Reserved</option>
                                <option value="sold" @selected(old('status') === 'sold')>Sold</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_negotiable" id="is_negotiable" value="1" class="form-check-input" @checked(old('is_negotiable'))>
                                <label for="is_negotiable" class="form-check-label">Narx kelishiladi</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="additional" class="form-label fw-semibold">Qo'shimcha</label>
                            <textarea name="additional" id="additional" rows="3" class="form-control bg-light border-0" placeholder="Qo'shimcha ma'lumotlar...">{{ old('additional') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label for="image" class="form-label fw-semibold">Rasm</label>
                            <input type="file" name="image" id="image" class="form-control form-control-lg bg-light border-0" accept="image/*">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const genderSelect = document.getElementById('gender');
        const mixedOption = genderSelect.querySelector('option[value="mixed"]');

        function updateGenderOptions() {
            const quantity = parseInt(quantityInput.value) || 1;
            if (quantity > 1) {
                mixedOption.style.display = 'block';
            } else {
                mixedOption.style.display = 'none';
                if (genderSelect.value === 'mixed') {
                    genderSelect.value = 'male';
                }
            }
        }

        quantityInput.addEventListener('change', updateGenderOptions);
        quantityInput.addEventListener('input', updateGenderOptions);
        updateGenderOptions();
    });
</script>
@endsection
