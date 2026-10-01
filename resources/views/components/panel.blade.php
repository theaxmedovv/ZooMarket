@props(['title', 'subtitle' => null, 'titleId' => null])
{{-- White settings/list panel with a title row; the optional `action` slot sits right of the title. --}}
<div {{ $attributes->class('rounded-2xl border border-line bg-white px-4 py-5 shadow-[0_8px_24px_rgba(0,0,0,.12)] sm:px-5') }}>
    <div class="mb-3 flex items-center justify-between gap-2">
        <div>
            <h3 @if($titleId) id="{{ $titleId }}" @endif class="scroll-mt-28 text-[17px] font-bold text-ink">{{ $title }}</h3>
            @if($subtitle)<p class="mt-1 text-xs text-muted">{{ $subtitle }}</p>@endif
        </div>
        {{ $action ?? '' }}
    </div>
    {{ $slot }}
</div>
