@if($products->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="best-sellers-table">
            <thead class="bg-whitesmoke">
                <tr>
                    <th class="pl-4" style="width:50px;">#</th>
                    <th>Product Name</th>
                    <th class="text-center">Times Ordered</th>
                    <th class="text-center">Total Qty</th>
                    <th class="text-right pr-4">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $i => $product)
                    <tr>
                        <td class="pl-4 text-muted">{{ ($products->currentPage() - 1) * $products->perPage() + $i + 1 }}</td>
                        <td class="font-weight-bold">{{ $product->product_name }}</td>
                        <td class="text-center">
                            <span class="badge badge-danger px-2" style="font-size:13px;">{{ number_format($product->times_ordered) }}</span>
                        </td>
                        <td class="text-center font-weight-bold">{{ number_format($product->total_qty) }}</td>
                        <td class="text-right pr-4 font-weight-bold text-dark">{!! formatWithCurrency($product->total_value) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <p class="text-muted mb-0" style="font-size:14px;">
            Showing <strong>{{ $products->firstItem() }}</strong>&ndash;<strong>{{ $products->lastItem() }}</strong>
            of <strong>{{ $products->total() }}</strong> products
        </p>
        <div class="custom-pagination">
            {{ $products->links() }}
        </div>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-fire fa-3x mb-3 text-danger" style="opacity:0.3;"></i>
        <p class="mb-0">
            @if(request('search') || request('category_id') || request('sub_category_id') || request('child_category_id'))
                No products found matching your filters.
            @else
                No order data available yet.
            @endif
        </p>
    </div>
@endif
