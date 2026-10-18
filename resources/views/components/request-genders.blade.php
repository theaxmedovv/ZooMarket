@props(['request', 'tag'])
{{-- Male / female split of a purchase request (a plain count when the category isn't sold by sex). --}}
@if($request->animal && ! $request->animal->usesGender())
    <span class="{{ $tag }} border-brand/25 bg-brand/10 text-brand"><i class="bi bi-hash"></i>Soni: {{ $request->male_quantity + $request->female_quantity }}</span>
@else
    @if($request->male_quantity > 0)
        <span class="{{ $tag }} border-male/25 bg-male/10 text-male"><i class="bi bi-gender-male"></i>Erkak ♂: {{ $request->male_quantity }}</span>
    @endif
    @if($request->female_quantity > 0)
        <span class="{{ $tag }} border-danger/25 bg-danger/10 text-danger"><i class="bi bi-gender-female"></i>Urg'ochi ♀: {{ $request->female_quantity }}</span>
    @endif
@endif
