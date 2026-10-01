@props(['type' => 'info', 'text' => ''])
@php
    $icon = ['success' => 'bi-check-circle-fill', 'info' => 'bi-info-circle-fill', 'warning' => 'bi-exclamation-triangle-fill', 'error' => 'bi-x-circle-fill'][$type] ?? 'bi-info-circle-fill';
@endphp
<div data-toast="{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}"
     class="group pointer-events-auto flex animate-toast-in items-start gap-2.5 rounded-xl border border-l-4 border-line border-l-brand bg-white py-[13px] pr-3 pl-4 text-sm leading-snug font-semibold text-ink shadow-[0_14px_34px_rgba(0,0,0,.14)] transition duration-200
            data-[toast=success]:border-l-ok data-[toast=warning]:border-l-warn data-[toast=error]:border-l-danger
            data-hiding:translate-x-4 data-hiding:opacity-0">
    <i class="bi {{ $icon }} text-lg leading-tight text-brand group-data-[toast=success]:text-ok group-data-[toast=warning]:text-warn group-data-[toast=error]:text-danger"></i>
    <span class="flex-1" data-toast-text>{{ $text }}</span>
    <button type="button" data-toast-close aria-label="Yopish" class="cursor-pointer rounded-md px-1 py-0.5 leading-none text-faint hover:bg-soft hover:text-ink"><i class="bi bi-x-lg"></i></button>
</div>
