@foreach ($products as $product)
    @php 
        // Use the pre-calculated stock_qty from controller
        $qty = $product->inventory_stocks_sum_quantity ?? 0;
        $assetValue = $qty * $product->purchase_price;
        $potentialSale = $qty * $product->price;
        $profit = $potentialSale - $assetValue;
    @endphp
    <tr>
        <td>
            <div class="d-flex align-items-center">
                @if($product->thumb_image)
                    <img src="{{ asset('storage/'.$product->thumb_image) }}" alt="" width="32" class="rounded mr-2 box-shadow-1">
                @else
                    <div class="rounded mr-2 bg-secondary d-flex align-items-center justify-content-center text-white small" style="width:32px; height:32px;">N/A</div>
                @endif
                <div>
            <div class="font-weight-bold">{{ $product->name }}</div>
                    {{-- <div class="text-small text-muted">SKU: {{ $product->sku }}</div> --}}
                </div>
            </div>
        </td>
        <td>
            <div class="badge badge-light mb-1">{{ $product->category->name ?? '-' }}</div>
            <div class="text-small text-muted">{{ $product->brand->name ?? '-' }}</div>
        </td>
        <td class="text-center">
            @if($qty <= $product->min_inventory_qty)
                <span class="badge badge-danger" data-toggle="tooltip" title="Low Stock!">{{ number_format($qty) }}</span>
            @else
                <span class="badge badge-success">{{ number_format($qty) }}</span>
            @endif
        </td>
        <td class="text-right">{{ $settings->currency_icon }}{{ number_format($product->purchase_price, 2) }}</td>
        <td class="text-right">{{ $settings->currency_icon }}{{ number_format($product->price, 2) }}</td>
        <td class="text-right font-weight-bold text-primary">{{ $settings->currency_icon }}{{ number_format($assetValue, 2) }}</td>
        <td class="text-right text-success">{{ $settings->currency_icon }}{{ number_format($profit, 2) }}</td>
    </tr>
@endforeach
