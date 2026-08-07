@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
        <div class="flex items-center justify-center gap-1.5 flex-wrap">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-md border border-gray-200 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-400 cursor-not-allowed select-none">
                    &laquo; Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-300 shadow-2xs transition-colors">
                    &laquo; Prev
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center px-2 py-1.5 text-xs font-bold text-gray-400 select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3.5 py-1.5 text-xs font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-300 shadow-2xs transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-300 shadow-2xs transition-colors">
                    Next &raquo;
                </a>
            @else
                <span class="inline-flex items-center rounded-md border border-gray-200 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-400 cursor-not-allowed select-none">
                    Next &raquo;
                </span>
            @endif
        </div>
    </nav>
@endif
