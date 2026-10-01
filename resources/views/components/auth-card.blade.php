@props(['title', 'subtitle', 'note'])
{{-- Centered login/register card: brand badge, heading, error summary, form slot, footer slot. --}}
<div class="relative overflow-hidden pt-10 pb-16">
    <div class="pointer-events-none absolute top-1/4 left-1/2 size-[550px] -translate-1/2 rounded-full bg-[radial-gradient(circle,rgba(0,142,204,.08)_0%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative mx-auto w-full max-w-[476px] px-4 py-6">
        <div class="rounded-[20px] border border-line bg-white px-7 py-8 shadow-[0_16px_40px_rgba(0,0,0,.12)] sm:px-9 sm:py-10">
            <div class="mb-6 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-line bg-black/4 px-3.5 py-[5px]">
                    <span class="inline-flex size-[22px] -rotate-6 items-center justify-center rounded-[5px] bg-brand text-[13px] font-extrabold text-white">Z</span>
                    <span class="text-[15px] font-bold tracking-tight text-ink">ZooMarket</span>
                </div>
                <h2 class="mb-1.5 text-[1.65rem] font-extrabold tracking-tight text-ink">{{ $title }}</h2>
                <p class="text-sm leading-snug text-muted">{{ $subtitle }}</p>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-accent/35 bg-accent/10 px-4 py-3 text-[13px] text-[#d9541e]">
                    <div class="mb-1 flex items-center gap-2 font-bold">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Iltimos, xatoliklarni to'g'rilang:</span>
                    </div>
                    <ul class="list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}

            <div class="border-t border-line pt-[18px] text-center text-sm">{{ $footer }}</div>
        </div>

        <div class="mt-4 text-center text-xs text-muted"><i class="bi bi-shield-check mr-1 text-brand"></i> {{ $note }}</div>
    </div>
</div>
