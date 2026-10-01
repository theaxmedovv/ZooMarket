{{-- Bordered, horizontally scrollable table panel; the `footer` slot holds pagination. --}}
<div {{ $attributes->class('overflow-hidden rounded-2xl border border-line bg-white shadow-[0_10px_30px_rgba(0,0,0,.12)]') }}>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left [&_td]:border-b [&_td]:border-line [&_td]:px-[18px] [&_td]:py-3.5 [&_td]:align-middle [&_td]:text-sm [&_th]:border-b [&_th]:border-line [&_th]:bg-surface [&_th]:px-[18px] [&_th]:py-3.5 [&_th]:text-xs [&_th]:font-bold [&_th]:tracking-wide [&_th]:whitespace-nowrap [&_th]:text-muted [&_th]:uppercase [&_tbody_tr]:hover:bg-black/2">
            {{ $slot }}
        </table>
    </div>
    @if(isset($footer) && $footer->isNotEmpty())
        <div class="border-t border-line bg-surface px-[18px] py-3.5">{{ $footer }}</div>
    @endif
</div>
