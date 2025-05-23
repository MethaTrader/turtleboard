@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <!-- Mobile Pagination -->
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-text-secondary bg-card border border-gray-200 cursor-default leading-5 rounded-button">
                    <i class="fas fa-chevron-left mr-2"></i>
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-text-primary bg-card border border-gray-200 leading-5 rounded-button hover:bg-primary hover:text-white hover:border-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 active:bg-primary active:text-white transition-all duration-300 ease-in-out">
                    <i class="fas fa-chevron-left mr-2"></i>
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-text-primary bg-card border border-gray-200 leading-5 rounded-button hover:bg-primary hover:text-white hover:border-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 active:bg-primary active:text-white transition-all duration-300 ease-in-out">
                    {!! __('pagination.next') !!}
                    <i class="fas fa-chevron-right ml-2"></i>
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-text-secondary bg-card border border-gray-200 cursor-default leading-5 rounded-button">
                    {!! __('pagination.next') !!}
                    <i class="fas fa-chevron-right ml-2"></i>
                </span>
            @endif
        </div>

        <!-- Desktop Pagination -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-text-secondary leading-5 font-medium">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-text-primary">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-semibold text-text-primary">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-semibold text-text-primary">{{ $paginator->count() }}</span>
                    @endif
                    {!! __('of') !!}
                    <span class="font-semibold text-text-primary">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-button">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-text-muted bg-card border border-gray-200 cursor-default rounded-l-button leading-5" aria-hidden="true">
                                <i class="fas fa-chevron-left w-4 h-4"></i>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-text-primary bg-card border border-gray-200 rounded-l-button leading-5 hover:bg-secondary hover:text-white hover:border-secondary focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 active:bg-primary active:text-white transition-all duration-300 ease-in-out" aria-label="{{ __('pagination.previous') }}">
                            <i class="fas fa-chevron-left w-4 h-4"></i>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-text-secondary bg-card border border-gray-200 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-semibold text-white bg-secondary border border-secondary cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-text-primary bg-card border border-gray-200 leading-5 hover:bg-secondary hover:text-white hover:border-secondary focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 active:bg-primary active:text-white transition-all duration-300 ease-in-out" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-text-primary bg-card border border-gray-200 rounded-r-button leading-5 hover:bg-secondary hover:text-white hover:border-secondary focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 active:bg-primary active:text-white transition-all duration-300 ease-in-out" aria-label="{{ __('pagination.next') }}">
                            <i class="fas fa-chevron-right w-4 h-4"></i>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-text-muted bg-card border border-gray-200 cursor-default rounded-r-button leading-5" aria-hidden="true">
                                <i class="fas fa-chevron-right w-4 h-4"></i>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif