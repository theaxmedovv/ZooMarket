@props(['href', 'active' => false, 'count' => 0, 'highlight' => false])
{{-- Filter tab with a count bubble; `highlight` turns the bubble orange (e.g. pending items). --}}
<a href="{{ $href }}" @if($active) aria-current="page" @endif
   class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-3.5 py-2 text-[13px] font-bold text-muted transition hover:border-brand/45 hover:text-ink current:border-brand/50 current:bg-brand/12 current:text-brand">
    <span>{{ $slot }}</span>
    <span @class(['min-w-5 rounded-full px-[7px] py-px text-center text-xs', $highlight ? 'bg-accent text-white' : 'bg-black/8 text-ink'])>{{ $count }}</span>
</a>
