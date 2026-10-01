@props(['title', 'subtitle' => null])
{{-- One page title pattern for every page: title, optional subtitle, optional page-specific actions.
     Navigation never goes here — it lives in the header and tab row. --}}
<header {{ $attributes->class('mb-[18px] flex flex-wrap items-end justify-between gap-4 md:mb-6') }}>
    <div class="min-w-0">
        <h1 class="text-[23px] leading-tight font-extrabold tracking-tight text-ink md:text-[28px]">{{ $title }}</h1>
        @if($subtitle)<p class="mt-1.5 max-w-[640px] text-sm leading-normal text-muted md:text-[15px]">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
