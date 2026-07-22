@if($orders->hasPages())
    @php
        $start = max(1, $orders->currentPage() - 1);
        $end = min($orders->lastPage(), $orders->currentPage() + 1);
    @endphp
    <div class="flex items-center justify-end gap-2">
        @if(!$orders->onFirstPage())
            <a href="{{ $orders->previousPageUrl() }}" class="px-3 py-2 border border-slate-300 rounded-sm text-xs font-bold uppercase tracking-[0.12em] text-slate-700 hover:bg-slate-100">Prev</a>
        @endif

        @foreach(range($start, $end) as $pageNo)
            @if($pageNo === $orders->currentPage())
                <span class="px-3 py-2 border border-slate-900 bg-slate-900 rounded-sm text-xs font-black uppercase tracking-[0.12em] text-white">{{ $pageNo }}</span>
            @else
                <a href="{{ $orders->url($pageNo) }}" class="px-3 py-2 border border-slate-300 rounded-sm text-xs font-bold uppercase tracking-[0.12em] text-slate-700 hover:bg-slate-100">{{ $pageNo }}</a>
            @endif
        @endforeach

        @if($orders->hasMorePages())
            <a href="{{ $orders->nextPageUrl() }}" class="px-5 py-2 border border-rose-700 bg-rose-700 rounded-sm text-xs font-black uppercase tracking-[0.14em] text-white hover:bg-rose-800">Next</a>
        @endif
    </div>
@endif
