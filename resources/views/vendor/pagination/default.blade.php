@if ($paginator->hasPages())
    @php
        // Preserve current query parameters across pagination links
        if (request()->query()) {
            $paginator->appends(request()->query());
        }

        $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
        $current = $paginator->currentPage();
        $last = $isLengthAware ? max(1, $paginator->lastPage()) : $current;

        $pages = [];
        if ($isLengthAware) {
            if ($last <= 4) {
                $pages = range(1, $last);
            } elseif ($current <= 2) {
                // Beginning: 1 2 3 ... last
                $pages = [1, 2, 3, '...', $last];
            } elseif ($current >= $last - 2) {
                // End: 1 ... (last-2) (last-1) last
                $pages = [1, '...', $last - 2, $last - 1, $last];
            } else {
                // Middle: 1 ... current next ... last
                $pages = [1, '...', $current, $current + 1, '...', $last];
            }
        } else {
            $pages = [$current];
        }
    @endphp

    <nav aria-label="Page navigation" class="d-inline-flex align-items-center">
        <ul class="pagination pagination-sm mb-0 align-items-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                    <span class="page-link" aria-hidden="true">Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">Previous</a>
                </li>
            @endif

            {{-- Numbered Page Links --}}
            @foreach ($pages as $page)
                @if ($page === '...')
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">...</span>
                    </li>
                @elseif ($page == $current)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">Next</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                    <span class="page-link" aria-hidden="true">Next</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
