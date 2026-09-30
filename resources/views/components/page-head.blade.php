@props(['title', 'subtitle' => null])
{{-- One page title pattern for every page: title, optional subtitle, optional page-specific actions.
     Navigation never goes here — it lives in the header and tab row. --}}
<header {{ $attributes->class('zm-page-head') }}>
    <div class="zm-page-head-text">
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="zm-page-actions">{{ $actions }}</div>
    @endisset
</header>
