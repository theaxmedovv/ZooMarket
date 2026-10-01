@props(['id', 'label', 'icon', 'password' => false])
{{-- Labelled input with a leading icon; password inputs get a show/hide toggle. Extra attributes go to the <input>. --}}
<div class="mb-4">
    <label class="mb-1.5 block text-[13px] font-semibold text-ink" for="{{ $id }}"><i class="bi {{ $icon }} mr-1"></i> {{ $label }}</label>
    <div class="group relative flex items-center">
        <i class="bi {{ $icon }} pointer-events-none absolute left-3.5 text-[15px] text-muted transition-colors group-focus-within:text-brand"></i>
        <input id="{{ $id }}" {{ $attributes->class(['input h-[46px] rounded-xl border-line bg-surface pl-[42px]', 'pr-11' => $password]) }}>
        @if($password)
            <button type="button" data-password-toggle="{{ $id }}" tabindex="-1" title="Parolni ko'rsatish/yashirish" class="absolute right-3 cursor-pointer px-1.5 py-1 text-[15px] text-muted transition-colors hover:text-ink">
                <i class="bi bi-eye"></i>
            </button>
        @endif
    </div>
</div>
