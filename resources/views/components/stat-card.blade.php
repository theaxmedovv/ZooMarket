@props(['icon', 'label', 'value', 'href' => null, 'hint' => null, 'tone' => 'bg-brand/12 text-brand', 'alert' => false])
{{-- Compact number card for dashboards; a link when `href` is given, highlighted when `alert` (something needs attention). --}}
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'flex items-center gap-3.5 rounded-2xl border bg-white px-4 py-3.5 shadow-[0_6px_20px_rgba(0,0,0,.08)] transition',
        'border-amber-400/60 bg-amber-50' => $alert,
        'border-line' => ! $alert,
        'hover:-translate-y-0.5 hover:border-brand hover:shadow-[0_10px_26px_rgba(0,0,0,.12)]' => $href,
    ]) }}>
    <div class="grid size-11 shrink-0 place-items-center rounded-xl text-xl {{ $tone }}"><i class="bi {{ $icon }}"></i></div>
    <div class="min-w-0">
        <div class="text-[26px] leading-none font-extrabold tracking-tight text-ink">{{ $value }}</div>
        <div class="mt-1 truncate text-[13px] font-semibold text-muted">{{ $label }}</div>
        @if($hint)<div class="truncate text-[11px] text-faint">{{ $hint }}</div>@endif
    </div>
</{{ $tag }}>
