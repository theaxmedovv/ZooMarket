@props(['title', 'subtitle' => null])
{{-- One page title pattern for every page: title, optional subtitle, optional page-specific actions.
     Navigation never goes here — it lives in the header and tab row. --}}
<header {{ $attributes->class('mb-4 flex flex-wrap items-end justify-between gap-4 md:mb-6') }}>
    <div class="min-w-0">
        <h1 class="text-2xl leading-tight font-extrabold tracking-tight break-words text-ink md:text-3xl lg:text-4xl">{{ $title }}</h1>
        @if($subtitle)<p class="mt-1.5 max-w-2xl text-sm leading-normal text-muted md:text-base">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:shrink-0">{{ $actions }}</div>
    @endisset
</header>
