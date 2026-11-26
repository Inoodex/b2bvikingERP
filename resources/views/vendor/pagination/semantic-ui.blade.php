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

    <div class="ui pagination menu" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <a class="icon item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')"> <i class="left chevron icon"></i> </a>
        @else
            <a class="icon item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"> <i class="left chevron icon"></i> </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($pages as $page)
            @if ($page === '...')
                <a class="item disabled" aria-disabled="true">…</a>
            @elseif ($page == $current)
                <a class="item active" href="{{ $paginator->url($page) }}" aria-current="page">{{ $page }}</a>
            @else
                <a class="item" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="icon item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"> <i class="right chevron icon"></i> </a>
        @else
            <a class="icon item disabled" aria-disabled="true" aria-label="@lang('pagination.next')"> <i class="right chevron icon"></i> </a>
        @endif
    </div>
@endif
