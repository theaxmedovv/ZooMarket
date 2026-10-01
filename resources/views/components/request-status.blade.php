@props(['status'])
{{-- Purchase request status pill. --}}
@php
    [$classes, $icon, $label] = match ($status) {
        'approved' => ['border-brand/35 bg-brand/14 text-brand', 'bi-check-circle-fill', 'Tasdiqlangan'],
        'sold' => ['border-[#6e8eff]/35 bg-[#6e8eff]/12 text-[#1a6fd1]', 'bi-bag-check-fill', 'Sotilgan'],
        'rejected' => ['border-line bg-black/5 text-muted', 'bi-x-circle-fill', 'Rad etilgan'],
        default => ['border-accent/35 bg-accent/15 text-accent', null, 'Kutilmoqda'],
    };
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1 rounded-full border px-3 py-[5px] text-xs font-extrabold whitespace-nowrap', $classes]) }}>
    @if($icon)
        <i class="bi {{ $icon }}"></i>
    @else
        <span class="inline-block size-[7px] animate-pulse-dot rounded-full bg-accent"></span>
    @endif
    {{ $label }}
</span>
