{{-- Home page section: underlined title (wrap the highlighted word in <span>). Pass an id to make it a link target. --}}
<section {{ $attributes->class('mt-12 scroll-mt-28') }}>
    <div class="mb-6 border-b border-line">
        <h2 class="-mb-0.5 inline-block border-b-[3px] border-brand pb-3 text-lg font-bold text-muted md:text-[22px] [&_span]:text-brand">{{ $title }}</h2>
    </div>
    {{ $slot }}
</section>
