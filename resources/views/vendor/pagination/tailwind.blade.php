@if ($paginator->hasPages())
    @php
        $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
        $current = $paginator->currentPage();
        $last = $isLengthAware ? max(1, $paginator->lastPage()) : $current;
        $pages = [];

        if ($isLengthAware) {
            if ($last <= 5) {
                $pages = range(1, $last);
            } elseif ($current <= 2) {
                $pages = [1, 2, 3, '...', $last];
            } elseif ($current >= $last - 2) {
                $pages = [1, '...', $last - 2, $last - 1, $last];
            } else {
                $pages = [1, '...', $current, $current + 1, '...', $last];
            }
        } else {
            $pages = [$current];
        }
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
        @if ($isLengthAware)
            <p class="text-xs font-medium text-slate-500">
                {!! __('Showing') !!}
                <span class="font-semibold text-slate-900">{{ $paginator->firstItem() }}</span>
                {!! __('-') !!}
                <span class="font-semibold text-slate-900">{{ $paginator->lastItem() }}</span>
                {!! __('of') !!}
                <span class="font-semibold text-slate-900">{{ $paginator->total() }}</span>
            </p>
        @endif

        <ul class="flex flex-wrap items-center gap-1" aria-label="{{ __('Pagination Navigation') }}">
            @if ($paginator->onFirstPage())
                <li>
                    <span aria-disabled="true" class="inline-flex items-center rounded-lg border border-slate-100 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-300 cursor-default">
                        {!! __('pagination.previous') !!}
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 hover:text-blue-600">
                        {!! __('pagination.previous') !!}
                    </a>
                </li>
            @endif

            @foreach ($pages as $page)
                @if ($page === '...')
                    <li>
                        <span aria-disabled="true" class="inline-flex h-8 w-8 items-center justify-center text-xs font-semibold text-slate-400">…</span>
                    </li>
                @elseif ($page == $current)
                    <li>
                        <span aria-current="page" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-xs font-semibold text-white shadow-sm shadow-blue-100">
                            {{ $page }}
                        </span>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->url($page) }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 hover:text-blue-600">
                            {{ $page }}
                        </a>
                    </li>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 hover:text-blue-600">
                        {!! __('pagination.next') !!}
                    </a>
                </li>
            @else
                <li>
                    <span aria-disabled="true" class="inline-flex items-center rounded-lg border border-slate-100 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-300 cursor-default">
                        {!! __('pagination.next') !!}
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
