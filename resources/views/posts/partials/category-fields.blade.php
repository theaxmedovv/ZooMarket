{{-- The category-specific questions of the listing form (config/listing_types.php).
     Every category's group is rendered; the form script (resources/js/app.js, [data-listing-form]) shows the group of the
     chosen category and disables the others so they are not submitted.
     Needs: $values (saved details, for the edit form), $styles = ['label' => ..., 'field' => ...]. --}}
@php
    $values = $values ?? [];
    $inputClass = $styles['field'];
    $labelClass = $styles['label'];
    $groups = collect(config('listing_types.profiles'))
        ->map(fn ($config, $name) => \App\Support\ListingProfile::forName($name))
        ->filter(fn ($profile) => $profile->fields());
@endphp
<x-form-section icon="bi-clipboard2-check-fill" title="Qo'shimcha ma'lumotlar" desc="Tanlangan kategoriya uchun xaridor bilishi kerak bo'lgan tafsilotlar" data-profile-section data-profile-off>
    @foreach($groups as $name => $profile)
        <div class="grid gap-4 md:grid-cols-2" data-profile-extras="{{ $name }}" data-profile-off>
            @foreach($profile->fields() as $key => $field)
                @php
                    $id = 'detail_' . \Illuminate\Support\Str::slug($name, '_') . '_' . $key;
                    $value = old("details.{$key}", $values[$key] ?? null);
                    $required = $field['required'] ?? false;
                @endphp
                <div>
                    <label for="{{ $id }}" class="{{ $labelClass }}">{{ $field['label'] }}@if($required) <span class="text-brand">*</span>@endif</label>

                    @if($field['type'] === 'select')
                        <select name="details[{{ $key }}]" id="{{ $id }}" class="{{ $inputClass }} cursor-pointer" disabled @required($required)>
                            <option value="">Tanlang</option>
                            @foreach($field['options'] as $option)
                                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'number')
                        <div class="relative">
                            <input type="number" step="any" min="{{ $field['min'] ?? 0 }}" @isset($field['max']) max="{{ $field['max'] }}" @endisset
                                   name="details[{{ $key }}]" id="{{ $id }}" value="{{ $value }}" class="{{ $inputClass }} pr-14" disabled @required($required)>
                            <span class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-sm font-semibold text-muted">{{ $field['unit'] }}</span>
                        </div>
                    @else
                        <input type="text" maxlength="{{ $field['max'] ?? 255 }}" name="details[{{ $key }}]" id="{{ $id }}" value="{{ $value }}" class="{{ $inputClass }}" disabled @required($required)>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</x-form-section>
