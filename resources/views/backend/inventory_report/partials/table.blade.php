<div class="table-responsive">
    <table class="table table-striped" id="table-inventory">
        <thead>
            <tr>
                <th width="80">Image</th>
                <th>Product Name</th>
                <th>Variant</th>
                <th>Item Number</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stocks as $stock)
                <tr>
                    <td>
                        @php
                            $defaultImg = asset('uploads/default.svg');
                            $imgSrc = $defaultImg;
                            if (!empty($stock->product && $stock->product->thumb_image)) {
                                $thumb = $stock->product->thumb_image;
                                if (str_starts_with($thumb, 'http')) {
                                    $imgSrc = $thumb;
                                } elseif (file_exists(public_path($thumb))) {
                                    $imgSrc = asset($thumb);
                                } elseif (file_exists(public_path('storage/' . $thumb))) {
                                    $imgSrc = asset('storage/' . $thumb);
                                }
                            }
                        @endphp
                        <img src="{{ $imgSrc }}" alt="{{ $stock->product->name ?? 'Product' }}"
                            onerror="this.onerror=null;this.src='{{ asset('uploads/default.svg') }}';"
                            style="width: 45px; height: 45px; object-fit: contain; border-radius: 4px; border: 1px solid #e9ecef; background: #fff; padding: 2px;">
                    </td>
                    <td>{{ $stock->product->name ?? 'N/A' }}</td>
                    <td>{{ $stock->variant ? $stock->variant->name : '-' }}</td>
                    <td>{{ $stock->product->product_number ?? '-' }}</td>
                    <td>
                        <div class="font-weight-600">{{ $stock->product->category->name ?? '-' }}</div>
                        {{-- @if ($stock->product && $stock->product->subCategory)
                            <div class="text-muted small"><i class="fas fa-angle-right text-info mr-1"></i>{{ $stock->product->subCategory->name }}</div>
                        @endif
                        @if ($stock->product && $stock->product->childCategory)
                            <div class="text-muted small" style="padding-left: 8px;"><i class="fas fa-angle-double-right text-secondary mr-1"></i>{{ $stock->product->childCategory->name }}</div>
                        @endif --}}
                    </td>
                    <td class="font-weight-bold {{ $stock->quantity <= 5 ? 'text-danger' : 'text-success' }}">
                        {{ $stock->quantity }}
                    </td>
                    <td>
                        @if ($stock->quantity > 0)
                            <div class="badge badge-success">In Stock</div>
                        @else
                            <div class="badge badge-danger">Out of Stock</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                        No inventory stock found matching the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="text-muted mb-3 text-center" style="font-size: 14px; font-weight: 500;">
        Showing
        <span class="text-dark font-weight-bold">
            {{ $stocks->firstItem() ?? 0 }} - {{ $stocks->lastItem() ?? 0 }}
        </span>
        of
        <span class="text-dark font-weight-bold">
            {{ $stocks->total() }}
        </span>
        products |
        Page Qty: <strong>{{ $pageQuantity }}</strong> |
        Total Qty: <strong>{{ $totalQuantity }}</strong>
    </p>

    <div class="d-flex justify-content-center flex-wrap custom-pagination">
        {{ $stocks->links() }}
    </div>
</div>
