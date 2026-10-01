@props(['href', 'rail' => null])
{{-- Home page section: underlined title (wrap the highlighted word in <span>), optional rail arrows, "Hammasi" link. --}}
<section class="mt-12">
    <div class="mb-6 flex items-end justify-between gap-3 border-b border-line">
        <h2 class="-mb-0.5 border-b-[3px] border-brand pb-3 text-lg font-bold text-muted md:text-[22px] [&_span]:text-brand">{{ $title }}</h2>
        <div class="flex items-center gap-3.5 pb-3">
            @if($rail)
                <div class="hidden gap-1.5 md:flex" data-rail="{{ $rail }}">
                    @foreach([-1 => ['bi-chevron-left', 'Chapga'], 1 => ['bi-chevron-right', "O'ngga"]] as $dir => [$icon, $label])
                        <button type="button" data-dir="{{ $dir }}" aria-label="{{ $label }}" class="grid size-8 cursor-pointer place-items-center rounded-full border border-line bg-white text-brand hover:border-brand hover:bg-brand hover:text-white"><i class="bi {{ $icon }}"></i></button>
                    @endforeach
                </div>
            @endif
            <a href="{{ $href }}" class="inline-flex items-center gap-1.5 text-sm font-bold whitespace-nowrap text-ink hover:text-brand">Hammasi <i class="bi bi-chevron-right text-brand"></i></a>
        </div>
    </div>
    {{ $slot }}
</section>
