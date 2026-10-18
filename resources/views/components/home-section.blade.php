{{-- Home page section: underlined title (wrap the highlighted word in <span>). Pass an id to make it a link target. --}}
<section {{ $attributes->class('mt-12 scroll-mt-28') }}>
    <div class="mb-6 border-b border-line">
        <h2 class="-mb-0.5 inline-block border-b-[3px] border-brand pb-3 text-xl font-bold text-muted md:text-2xl [&_span]:text-brand">{{ $title }}</h2>
    </div>
    {{ $slot }}
</section>
