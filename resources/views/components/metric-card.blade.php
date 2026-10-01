@props(['icon', 'label', 'tone' => 'bg-brand/12 text-brand'])
<div class="flex min-h-[146px] flex-col gap-2.5 rounded-2xl border border-line bg-white px-4 pt-[18px] pb-3.5 shadow-[0_6px_24px_rgba(0,0,0,.12)]">
    <div class="grid size-[38px] place-items-center rounded-xl text-[17px] {{ $tone }}"><i class="bi {{ $icon }}"></i></div>
    <div class="text-[11px] font-bold tracking-[.08em] text-muted uppercase">{{ $label }}</div>
    <div class="text-base leading-snug font-bold break-words text-ink">{{ $slot }}</div>
</div>
