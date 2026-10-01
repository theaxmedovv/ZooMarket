@extends('layouts.app')

@section('content')
@php
    $field = 'input h-12 rounded-2xl border-transparent bg-surface px-4 text-base focus:bg-white';
    $label = 'field-label';
    $currentImages = $post->allImages();
@endphp
<div class="page">
    <x-page-head title="E'lonni tahrirlash" :subtitle="$post->title" />

    <div class="card max-w-[880px] p-5 md:p-8">
        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 px-5 py-4 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($post->moderation_status === 'rejected')
                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-danger/50 bg-red-50 p-4">
                    <div class="grid size-11 shrink-0 place-items-center rounded-full bg-danger/25 text-danger"><i class="bi bi-shield-x text-xl"></i></div>
                    <div class="text-sm">
                        <strong class="block text-base text-danger">E'lon avval Groq AI tomonidan rad etilgan</strong>
                        <div class="mt-1 text-muted"><strong>Rad etilish sababi:</strong> {{ $post->moderation_reason }}</div>
                        <div class="mt-1 text-muted">E'lon ma'lumotlari yoki fotosuratlarini to'g'rilab saqlasangiz, u avtomatik ravishda qayta tekshiruvdan o'tkaziladi.</div>
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <label for="title" class="{{ $label }}">E'lon sarlavhasi</label>
                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" class="{{ $field }}" placeholder="Sarlavhani kiriting..." required>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="col-span-2">
                    <label for="category_id" class="{{ $label }}">Kategoriya</label>
                    <select name="category_id" id="category_id" class="{{ $field }}" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="breed" class="{{ $label }}">Zot</label>
                    <input type="text" name="breed" id="breed" value="{{ old('breed', $post->breed) }}" class="{{ $field }}" required>
                </div>
                <div class="col-span-2">
                    <label for="quantityEdit" class="{{ $label }}">Jami soni</label>
                    <input type="number" name="quantity" id="quantityEdit" min="1" max="100" value="{{ old('quantity', $post->quantity) }}" class="{{ $field }}" required>
                </div>
                <div class="col-span-2">
                    <label for="genderEdit" class="{{ $label }}">Jinsi</label>
                    <select name="gender" id="genderEdit" class="{{ $field }}" required>
                        <option value="male" @selected(old('gender', $post->gender) === 'male')>Erkak</option>
                        <option value="female" @selected(old('gender', $post->gender) === 'female')>Urg'ochi</option>
                        <option value="mixed" @selected(old('gender', $post->gender) === 'mixed') id="optMixedEdit">Aralash (Mixed)</option>
                    </select>
                </div>
                <div class="col-span-full" id="mixedBoxEdit" @if(old('gender', $post->gender) !== 'mixed') hidden @endif>
                    <div class="rounded-2xl border border-line bg-surface p-4">
                        <div class="mb-2 text-sm font-semibold text-ink">Aralash jinslar soni taqsimoti:</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="maleQtyEdit" class="mb-1 block text-sm text-muted">Erkaklar soni</label>
                                <input type="number" name="male_quantity" id="maleQtyEdit" value="{{ old('male_quantity', $post->male_quantity) }}" class="input" min="1" max="99">
                            </div>
                            <div>
                                <label for="femaleQtyEdit" class="mb-1 block text-sm text-muted">Urg'ochilar soni</label>
                                <input type="number" name="female_quantity" id="femaleQtyEdit" value="{{ old('female_quantity', $post->female_quantity) }}" class="input" min="1" max="99">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-2">
                    <label for="age" class="{{ $label }}">Yoshi</label>
                    <input type="text" name="age" id="age" value="{{ old('age', $post->age) }}" class="{{ $field }}" required>
                </div>
                <div class="col-span-2">
                    <label for="color" class="{{ $label }}">Rangi</label>
                    <input type="text" name="color" id="color" value="{{ old('color', $post->color) }}" class="{{ $field }}">
                </div>
                <div class="col-span-2">
                    <label for="price" class="{{ $label }}">Narx</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $post->price) }}" class="{{ $field }}" required>
                </div>
                <div>
                    <label for="currency" class="{{ $label }}">Valyuta</label>
                    <select name="currency" id="currency" class="{{ $field }}" required>
                        @foreach(['UZS', 'USD', 'EUR', 'RUB'] as $currency)
                            <option value="{{ $currency }}" @selected(old('currency', $post->currency) === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="{{ $label }}">Holat</label>
                    <select name="status" id="status" class="{{ $field }}" required>
                        <option value="active" @selected(old('status', $post->status) === 'active')>Sotuvda</option>
                        <option value="reserved" @selected(old('status', $post->status) === 'reserved')>Band qilingan</option>
                        <option value="sold" @selected(old('status', $post->status) === 'sold')>Sotilgan</option>
                    </select>
                </div>
                <div class="col-span-full">
                    <label for="location" class="{{ $label }}">Joylashuv</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $post->location) }}" class="{{ $field }}" required>
                </div>
                <label for="is_negotiable" class="col-span-full inline-flex cursor-pointer items-center gap-2 text-sm text-ink">
                    <input type="checkbox" value="1" id="is_negotiable" name="is_negotiable" class="size-4 accent-brand" @checked(old('is_negotiable', $post->is_negotiable))>
                    Narx kelishiladi
                </label>
            </div>

            <div class="mb-6">
                <label for="description" class="{{ $label }}">Tavsif</label>
                <textarea name="description" id="description" rows="6" class="{{ $field }} h-auto py-3 leading-normal" placeholder="Hayvon haqida yozing..." required>{{ old('description', $post->description ?? $post->content) }}</textarea>
            </div>

            <div class="mb-10">
                <span class="{{ $label }}">Rasmlar (maksimum 3 ta)</span>
                @if(!empty($currentImages))
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach($currentImages as $img)
                            <img src="{{ route('images.show', ['path' => $img]) }}" alt="" class="size-20 rounded-lg object-cover shadow-sm">
                        @endforeach
                    </div>
                    <p class="mb-3 text-sm text-muted">Yangi rasmlar yuklasangiz, mavjud rasmlar almashtiriladi.</p>
                @endif
                <div class="grid gap-2 md:grid-cols-3">
                    @foreach(['1-rasm (asosiy)', '2-rasm', '3-rasm'] as $i => $imageLabel)
                        <div>
                            <label for="image{{ $i }}" class="mb-1 block text-sm text-muted">{{ $imageLabel }}</label>
                            <input type="file" name="images[]" id="image{{ $i }}" accept="image/*"
                                   class="w-full cursor-pointer rounded-lg bg-surface text-sm text-muted file:mr-3 file:cursor-pointer file:border-0 file:bg-brand-tint file:px-3 file:py-2.5 file:font-semibold file:text-brand hover:file:bg-brand/20">
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-muted"><i class="bi bi-info-circle mr-1"></i>Max: 2MB har bir rasm</p>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-line pt-6">
                <button type="submit" class="btn btn-primary h-[46px] text-[15px] max-md:flex-1">
                    <i class="bi bi-check-lg"></i> O'zgarishlarni saqlash
                </button>
                <a href="{{ route('posts.show', $post) }}" class="btn btn-outline h-[46px] text-[15px] max-md:flex-1">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

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
        optMixed.disabled = qty === 1;
        if (qty === 1 && genderSelect.value === 'mixed') {
            genderSelect.value = 'male';
        }

        const isMixed = genderSelect.value === 'mixed' && qty > 1;
        mixedBox.hidden = !isMixed;
        maleQty.required = isMixed;
        femaleQty.required = isMixed;
    }

    qtyInput.addEventListener('input', syncEdit);
    genderSelect.addEventListener('change', syncEdit);
    syncEdit();
});
</script>
@endsection
