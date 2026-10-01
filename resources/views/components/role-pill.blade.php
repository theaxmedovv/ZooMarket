@props(['seller' => false])
<span @class([
    'rounded border px-1.5 py-px text-[11px] font-bold uppercase',
    'border-accent/30 bg-accent/15 text-accent' => $seller,
    'border-brand/30 bg-brand/15 text-brand' => ! $seller,
])>{{ $seller ? 'Sotuvchi' : 'Xaridor' }}</span>
