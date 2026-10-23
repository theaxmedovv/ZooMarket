@extends('layouts.app')

@section('content')
@php
    $editing = $banner->exists;
    $field = 'input h-12 rounded-2xl border-transparent bg-surface px-4 text-base focus:bg-white';
    $label = 'field-label';
    $hint = 'mt-1 text-xs text-muted';
    $color = old('color', $banner->color);
    $isAdmin = auth()->user()->hasRole('admin');
@endphp
<div class="page">
    <x-page-head :title="$editing ? 'Bannerni tahrirlash' : 'Yangi banner'" subtitle="{{ $isAdmin ? 'Banner bosh sahifadagi almashib turadigan slaydlar orasida ko\'rsatiladi.' : 'Banner tasdiqlangach bosh sahifada ko\'rinadi. Tahrirlansa, qayta tekshiriladi.' }}" />

    <div class="card max-w-[880px] p-5 md:p-8">
        <form action="{{ $editing ? route('banners.update', $banner) : route('banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($editing) @method('PUT') @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 px-5 py-4 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="eyebrow" class="{{ $label }}">Kichik sarlavha</label>
                    <input type="text" name="eyebrow" id="eyebrow" value="{{ old('eyebrow', $banner->eyebrow) }}" class="{{ $field }}" maxlength="80" placeholder="Masalan: Faqat shu hafta">
                </div>
                <div>
                    <label for="title" class="{{ $label }}">Asosiy sarlavha @unless($isAdmin)<span class="text-brand">*</span>@endunless</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}" class="{{ $field }}" maxlength="80" @unless($isAdmin) required @endunless placeholder="Masalan: Itlar uchun ozuqa">
                    @if($isAdmin)
                        <p class="{{ $hint }}">Matn yozish ixtiyoriy: faqat rasm qo'ysangiz ham bo'ladi. Rasm ham, sarlavha ham bo'lmasa, banner saqlanmaydi.</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label for="offer" class="{{ $label }}">Taklif matni</label>
                    <input type="text" name="offer" id="offer" value="{{ old('offer', $banner->offer) }}" class="{{ $field }}" maxlength="140" placeholder="Masalan: Barcha mahsulotlarga **20% chegirma**">
                    <p class="{{ $hint }}">Sariq rangda ajratmoqchi bo'lgan qismni <b>**</b> belgilari orasiga yozing: <code>**20% chegirma**</code></p>
                </div>

                <div>
                    <label for="link" class="{{ $label }}">Havola</label>
                    <input type="text" name="link" id="link" value="{{ old('link', $banner->link) }}" class="{{ $field }}" maxlength="255" placeholder="/?category_id=1 yoki https://...">
                    <p class="{{ $hint }}">Bo'sh qoldirsangiz, bannerda tugma ko'rinmaydi.</p>
                </div>
                <div>
                    <label for="button_text" class="{{ $label }}">Tugma matni</label>
                    <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $banner->button_text) }}" class="{{ $field }}" maxlength="30" placeholder="Batafsil">
                </div>

                <div class="md:col-span-2">
                    <span class="{{ $label }}">Fon rangi</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach(\App\Models\Banner::COLORS as $hex => $name)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $hex }}" class="peer sr-only" @checked($color === $hex)>
                                <span class="flex items-center gap-2 rounded-xl border-2 border-transparent bg-surface py-1.5 pr-3 pl-1.5 text-sm font-semibold text-muted peer-checked:border-brand peer-checked:text-ink peer-focus-visible:outline-2 peer-focus-visible:outline-brand">
                                    <span style="background-color: {{ $hex }}" class="size-7 rounded-lg"></span>{{ $name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="{{ $label }}">Rasm</span>
                    @if($banner->imageUrl())
                        <div class="mb-3 flex items-center gap-3">
                            <img src="{{ $banner->imageUrl() }}" alt="" class="h-16 w-40 rounded-lg object-cover ">
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                                <input type="checkbox" name="remove_image" value="1" class="size-4 accent-danger"> Rasmni olib tashlash
                            </label>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                           class="w-full cursor-pointer rounded-lg bg-surface text-sm text-muted file:mr-3 file:cursor-pointer file:border-0 file:bg-brand-tint file:px-3 file:py-2.5 file:font-semibold file:text-brand hover:file:bg-brand/20">
                    <p class="{{ $hint }}">
                        @if($isAdmin)
                            Rasm butun banner foniga yoyiladi. Faqat rasm qo'yilsa, u o'zgarishsiz ko'rinadi, shuning uchun 3:1 shaklda (masalan, 1600×533 px) tayyorlang. Max: 4MB
                        @else
                            Ixtiyoriy. Rasm banner foniga yoyiladi, ustida sarlavhangiz ko'rinadi. Kengligi 1600px atrofida bo'lsin. Max: 4MB
                        @endif
                    </p>
                </div>
                <div>
                    <label for="emoji" class="{{ $label }}">Emoji (rasm bo'lmasa)</label>
                    <input type="text" name="emoji" id="emoji" value="{{ old('emoji', $banner->emoji) }}" class="{{ $field }}" maxlength="16" placeholder="🐕">
                    <p class="{{ $hint }}">Rasm yuklanmagan bo'lsa, bannerning o'ng tomonida katta qilib ko'rsatiladi.</p>
                </div>

                @if($isAdmin)
                    <div>
                        <label for="sort_order" class="{{ $label }}">Tartib raqami</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="{{ $field }}" min="0" max="999">
                        <p class="{{ $hint }}">Kichik raqamli banner birinchi chiqadi.</p>
                    </div>
                @endif
                <div class="flex items-center">
                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-ink">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="size-5 accent-brand" @checked(old('is_active', $banner->is_active))>
                        Bosh sahifada ko'rsatish
                    </label>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-line pt-6">
                <button type="submit" class="btn btn-primary h-[46px] text-[15px] max-md:flex-1">
                    <i class="bi bi-check-lg"></i> {{ $editing ? "O'zgarishlarni saqlash" : "Bannerni qo'shish" }}
                </button>
                <a href="{{ route('banners.index') }}" class="btn btn-outline h-[46px] text-[15px] max-md:flex-1">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>
@endsection
