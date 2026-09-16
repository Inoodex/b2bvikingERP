@foreach ($products as $product)
    @php 
        // Use the pre-calculated stock_qty from controller
        $qty = $product->inventory_stocks_sum_quantity ?? 0;
        $assetValue = $qty * $product->purchase_price;
        $potentialSale = $qty * $product->price;
        $profit = $potentialSale - $assetValue;

        $imgSrc = asset('uploads/no-image.svg');
        if (!empty($product->thumb_image)) {
            if (str_starts_with($product->thumb_image, 'http')) {
                $imgSrc = $product->thumb_image;
            } elseif (file_exists(public_path('storage/' . $product->thumb_image))) {
                $imgSrc = asset('storage/' . $product->thumb_image);
            } elseif (file_exists(public_path($product->thumb_image))) {
                $imgSrc = asset($product->thumb_image);
            }
        }
    @endphp
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <img src="{{ $imgSrc }}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" alt="{{ $product->name }}" width="36" height="36" class="rounded mr-2 border p-1" style="object-fit: contain; background: #f8fafc; flex-shrink: 0;">
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
