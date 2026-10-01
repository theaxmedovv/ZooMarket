{{-- Site pagination (set as the default in AppServiceProvider). --}}
@if ($paginator->hasPages())
    @php
        $item = 'grid h-10 min-w-10 place-items-center rounded-[10px] border px-3 font-bold';
        $link = $item . ' border-line bg-white text-ink hover:border-brand hover:bg-brand-soft hover:text-brand';
        $disabled = $item . ' border-line bg-[#fafafa] text-[#c4c4c4]';
    @endphp
    <nav role="navigation" aria-label="Sahifalar" class="flex flex-col items-center gap-3">
        <ul class="flex flex-wrap justify-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $disabled }}" aria-disabled="true" aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a class="{{ $link }}" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left"></i></a>
                @endif
            </li>

            @foreach ($elements as $element)
                {{-- "Three dots" separator --}}
                @if (is_string($element))
                    <li class="max-sm:hidden"><span class="{{ $disabled }}" aria-disabled="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="{{ $item }} border-brand bg-brand text-white" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li class="max-sm:hidden"><a class="{{ $link }}" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a class="{{ $link }}" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="{{ $disabled }}" aria-disabled="true" aria-label="@lang('pagination.next')"><i class="bi bi-chevron-right"></i></span>
                @endif
            </li>
        </ul>

        <p class="text-center text-sm text-muted">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}
        </p>
    </nav>
@endif
