@props(['icon', 'title', 'text' => null])
{{-- Dashed "nothing here yet" panel; the slot holds an optional call to action. --}}
<div {{ $attributes->class('rounded-2xl border border-dashed border-line bg-black/[.02] px-5 py-12 text-center') }}>
    <div class="mb-2.5 text-[2.1rem] text-brand"><i class="bi {{ $icon }}"></i></div>
    <h3 class="text-xl font-bold text-ink">{{ $title }}</h3>
    @if($text)<p class="mt-2 text-sm text-muted">{{ $text }}</p>@endif
    @if($slot->isNotEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
