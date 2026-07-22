@if($orders->total() > 0)
    Showing {{ $orders->firstItem() }} - {{ $orders->lastItem() }} of {{ $orders->total() }} orders
@else
    Showing 0 orders
@endif
