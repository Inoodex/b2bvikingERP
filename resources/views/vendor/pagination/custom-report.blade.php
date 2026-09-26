@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        
        if ($last <= 7) {
            $pages = range(1, $last);
        } else {
            $pages = [];
            $pages[] = 1;
            
            $start = max(2, $current - 1);
            $end = min($last - 1, $current + 1);
            
            if ($start > 2) {
                $pages[] = '...';
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
            
            if ($end < $last - 1) {
                $pages[] = '...';
            }
            
            $pages[] = $last;
        }
    @endphp
    <nav class="d-inline-flex align-items-center" aria-label="Table pagination">
        <ul class="pagination pagination-sm mb-0 align-items-center" style="gap: 3px; list-style: none; padding-left: 0; margin-bottom: 0;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="border: none; background: transparent; color: #cbd5e1; font-size: 12.5px; font-weight: 500; cursor: not-allowed; padding: 4px 8px;">Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="border: none; background: transparent; color: #4361ee; font-size: 12.5px; font-weight: 500; padding: 4px 8px; text-decoration: none;">Previous</a>
                </li>
            @endif

            {{-- Compact Page Numbers --}}
            @foreach ($pages as $p)
                @if ($p === '...')
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" style="border: none; background: transparent; color: #94a3b8; font-size: 12.5px; padding: 4px 4px;">...</span>
                    </li>
                @elseif ($p == $current)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link shadow-sm" style="border: none; background: #4361ee; color: #ffffff; font-size: 12.5px; font-weight: 600; border-radius: 5px; padding: 4px 9px; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; text-align: center;">{{ $p }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($p) }}" style="border: none; background: transparent; color: #4361ee; font-size: 12.5px; font-weight: 500; border-radius: 5px; padding: 4px 9px; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; text-align: center; text-decoration: none;">{{ $p }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="border: none; background: transparent; color: #4361ee; font-size: 12.5px; font-weight: 500; padding: 4px 8px; text-decoration: none;">Next</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="border: none; background: transparent; color: #cbd5e1; font-size: 12.5px; font-weight: 500; cursor: not-allowed; padding: 4px 8px;">Next</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
