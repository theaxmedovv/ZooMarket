@props(['icon', 'title', 'desc' => null])
{{-- Card with an icon header, used to group fields on the listing form. --}}
<div {{ $attributes->class('rounded-[18px] border border-line bg-white p-[22px] shadow-[0_8px_24px_rgba(0,0,0,.12)] transition-colors hover:border-brand/25') }}>
    <div class="mb-5 flex items-center gap-3 border-b border-line pb-3.5">
        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand/10 text-lg text-brand"><i class="bi {{ $icon }}"></i></div>
        <div>
            <h5 class="mb-0.5 text-[17px] font-bold text-ink">{{ $title }}</h5>
            @if($desc)<p class="text-xs text-muted">{{ $desc }}</p>@endif
        </div>
    </div>
    {{ $slot }}
</div>
