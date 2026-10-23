@props(['rating' => 0])
{{-- Read-only 1-5 star row (halves rounded to the nearest half). The number is for screen readers. --}}
@php $rounded = round(((float) $rating) * 2) / 2; @endphp
<span {{ $attributes->class('inline-flex items-center gap-0.5 text-accent') }} role="img" aria-label="{{ rtrim(rtrim(number_format((float) $rating, 1), '0'), '.') }} / 5">
    @for($i = 1; $i <= 5; $i++)
        <i class="bi {{ $rounded >= $i ? 'bi-star-fill' : ($rounded >= $i - 0.5 ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
    @endfor
</span>
