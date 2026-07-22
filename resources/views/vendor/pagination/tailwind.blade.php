@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between py-4">
        <div class="flex justify-between flex-1 sm:hidden gap-4">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-default">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-50 transition-all">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-50 transition-all">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-default">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">
                    {!! __('Showing') !!}
                    <span class="font-semibold text-slate-900">{{ $paginator->firstItem() }}</span>
                    {!! __('-') !!}
                    <span class="font-semibold text-slate-900">{{ $paginator->lastItem() }}</span>
                    {!! __('of') !!}
                    <span class="font-semibold text-slate-900">{{ $paginator->total() }}</span>
                </p>
            </div>

            <div>
                @php
                    $current = $paginator->currentPage();
                    $last = $paginator->lastPage();
                    $start = max(1, $current - 4);
                    $end = min($last, $current + 4);
                    $pages = range($start, $end);
                @endphp
                <span class="relative z-0 inline-flex gap-1.5">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center w-8 h-8 justify-center bg-white border border-slate-100 text-slate-200 rounded-lg cursor-default" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center w-8 h-8 justify-center bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Compact Pages (3 around current + last page) --}}
                    @foreach ($pages as $page)
                        @if ($page == $current)
                            <span aria-current="page">
                                <span class="relative inline-flex items-center w-8 h-8 justify-center bg-blue-600 text-white text-xs font-semibold rounded-lg shadow-sm shadow-blue-100 cursor-default">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $paginator->url($page) }}" class="relative inline-flex items-center w-8 h-8 justify-center bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    @if ($last > $end)
                        @if ($last > $end + 1)
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center w-8 h-8 justify-center text-slate-400 text-xs font-semibold">…</span>
                            </span>
                        @endif
                        <a href="{{ $paginator->url($last) }}" class="relative inline-flex items-center w-8 h-8 justify-center bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all" aria-label="{{ __('Go to page :page', ['page' => $last]) }}">
                            {{ $last }}
                        </a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center w-8 h-8 justify-center bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:ring-2 focus:ring-blue-50 transition-all" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center w-8 h-8 justify-center bg-white border border-slate-100 text-slate-200 rounded-lg cursor-default" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
