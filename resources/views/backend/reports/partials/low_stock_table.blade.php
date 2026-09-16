@if($products->count() > 0)
    <div class="alert alert-warning d-flex align-items-center mb-3">
        <i class="fas fa-exclamation-triangle mr-2" style="font-size: 16px;"></i> 
        <div>
            <strong>{{ $products->total() }}</strong> product(s) found
            @if(request('search'))
                matching "{{ request('search') }}"
            @else
                below or at minimum inventory threshold
            @endif
            @if(request('category_id') || request('brand_id') || request('vendor_id'))
                (filtered)
            @endif
        </div>
    </div>

    <div class="sr-table-wrap">
        <table class="table" id="low-stock-table">
            <thead>
                <tr>
                    <th class="th-check">
                        <input type="checkbox" id="select_all" title="Select All" style="cursor: pointer;">
                    </th>
                    <th class="th-img">Image</th>
                    <th class="th-name">Product</th>
                    <th class="th-cat">Category</th>
                    <th class="th-brand">Brand</th>
                    <th class="th-stock text-center">Current Stock</th>
                    <th class="th-stat text-center">Status</th>
                    <th class="th-act">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $currentStock = $product->inventory_stocks_sum_quantity ?? 0;
                        $minQty = $product->min_inventory_qty ?? 10;
                        $isCritical = $currentStock == 0;
                        $hasVariants = $product->variants->isNotEmpty();
                        $totalVariants = $hasVariants ? $product->variants->count() : 0;
                        
                        $lowVariantsCount = 0;
                        $outOfStockVariantsCount = 0;
                        if ($hasVariants) {
                            foreach ($product->variants as $variant) {
                                $vQty = (float) $variant->inventoryStocks->sum('quantity');
                                if ($vQty == 0) {
                                    $outOfStockVariantsCount++;
                                } elseif ($vQty <= $minQty) {
                                    $lowVariantsCount++;
                                }
                            }
                        }
                        $hasVariantDeficit = $hasVariants && ($outOfStockVariantsCount > 0 || $lowVariantsCount > 0) && ($currentStock > $minQty);
                    @endphp
                    <tr class="{{ $isCritical ? 'table-danger' : ($hasVariantDeficit ? 'table-warning-soft' : '') }}" id="product-row-{{ $product->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="product-checkbox" value="{{ $product->id }}" data-product-name="{{ $product->name }}" style="cursor: pointer;">
                        </td>
                        <td class="text-center">
                            @if($product->thumb_image)
                                <img src="{{ asset('storage/' . $product->thumb_image) }}"
                                     alt="{{ $product->name }}"
                                     class="rounded"
                                     style="width:38px;height:38px;object-fit:cover;border:1px solid #e2e8f0;"
                                     loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                                <div class="rounded align-items-center justify-content-center text-muted"
                                     style="display:none;width:38px;height:38px;background:#f8f9fa;border:1px solid #e2e8f0;">
                                    <i class="fas fa-image" style="font-size:12px;"></i>
                                </div>
                            @else
                                <div class="rounded d-inline-flex align-items-center justify-content-center text-muted"
                                     style="width:38px;height:38px;background:#f8f9fa;border:1px solid #e2e8f0;">
                                    <i class="fas fa-image" style="font-size:12px;"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="product-title">{{ $product->name }}</div>
                            <div class="product-meta">
                                @if($product->sku)
                                    <span><i class="fas fa-barcode mr-1"></i>{{ $product->sku }}</span>
                                @endif
                                @if($product->min_inventory_qty)
                                    <span class="text-muted">(Min: {{ $product->min_inventory_qty }})</span>
                                @endif
                            </div>
                            @if($hasVariants)
                                <div class="mt-1">
                                    <button type="button" class="btn btn-xs btn-outline-info toggle-variant-drawer py-0 px-2" data-target="#variant-drawer-{{ $product->id }}" style="font-size: 10.5px; border-radius: 4px; font-weight: 600; line-height: 20px; border-color: #cbd5e1; color: #334155; background: #f8fafc;">
                                        <i class="fas fa-layer-group text-primary mr-1"></i>
                                        <span>{{ $totalVariants }} Variants</span>
                                        @if($outOfStockVariantsCount > 0)
                                            <span class="badge badge-danger ml-1" style="font-size: 9px; padding: 2px 5px;">{{ $outOfStockVariantsCount }} Out</span>
                                        @endif
                                        @if($lowVariantsCount > 0)
                                            <span class="badge badge-warning text-dark ml-1" style="font-size: 9px; padding: 2px 5px;">{{ $lowVariantsCount }} Low</span>
                                        @endif
                                        <i class="fas fa-chevron-down ml-1 drawer-chevron" style="font-size: 8.5px; transition: transform 0.2s;"></i>
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="sr-badge-pill cat" title="{{ $product->category->name ?? 'N/A' }}">
                                {{ $product->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="sr-badge-pill brand" title="{{ $product->brand->name ?? 'N/A' }}">
                                {{ $product->brand->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $isCritical ? 'danger' : ($hasVariantDeficit ? 'info' : 'warning') }}" style="font-size: 11.5px; padding: 5px 8px;">
                                {{ $currentStock }} {{ $product->unit->name ?? '' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($isCritical)
                                <span class="badge badge-danger" style="font-size: 10px; letter-spacing: 0.5px;">OUT OF STOCK</span>
                            @elseif($hasVariantDeficit)
                                <span class="badge badge-info" style="font-size: 10px; letter-spacing: 0.5px;" title="Total stock is adequate, but 1 or more specific variants are out/low">VARIANT DEFICIT</span>
                            @else
                                <span class="badge badge-warning" style="font-size: 10px; letter-spacing: 0.5px;">LOW STOCK</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @can('Manage Order Place')
                            <button type="button" 
                                    class="btn btn-outline-warning btn-sm add-to-basket font-weight-bold" 
                                    data-id="{{ $product->id }}" 
                                    data-has-variants="{{ $hasVariants ? '1' : '0' }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-img="{{ $product->thumb_image ? asset('storage/' . $product->thumb_image) : asset('uploads/no-image.svg') }}"
                                    data-vendor-name="{{ $product->vendor->shop_name ?? 'Primary Supplier' }}"
                                    title="{{ $hasVariants ? 'Select Variants for Procurement' : 'Add to Procurement Basket' }}" 
                                    style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
                                <i class="fas {{ $hasVariants ? 'fa-layer-group' : 'fa-shopping-basket' }}"></i>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @if($hasVariants)
                        <tr id="variant-drawer-{{ $product->id }}" class="variant-drawer-row" style="display: none; background: #f8fafc;">
                            <td colspan="8" style="padding: 0 !important; border-bottom: 2px solid #e2e8f0 !important;">
                                <div class="p-3 bg-light border-bottom" style="background: #f8fafc; border-left: 4px solid #f59e0b;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 12.5px;">
                                            <i class="fas fa-layer-group text-primary mr-1"></i> Variant Stock Breakdown: <span class="text-primary">{{ $product->name }}</span>
                                        </h6>
                                        <span class="text-muted" style="font-size: 11.5px;">
                                            Low Threshold: &le; {{ $minQty }} {{ $product->unit->name ?? 'units' }}
                                        </span>
                                    </div>
                                    <div class="table-responsive bg-white rounded border">
                                        <table class="table table-sm table-hover mb-0" style="font-size: 12px; table-layout: fixed; width: 100%;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 35%;">Variant Name / Specs</th>
                                                    <th style="width: 20%;">SKU / Code</th>
                                                    <th class="text-center" style="width: 15%;">Variant Stock</th>
                                                    <th class="text-center" style="width: 15%;">Status</th>
                                                    <th class="text-center" style="width: 15%;">Procurement</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variants as $variant)
                                                    @php
                                                        $vStock = (float) $variant->inventoryStocks->sum('quantity');
                                                        $isVOut = $vStock == 0;
                                                        $isVLow = $vStock <= $minQty;
                                                        $vName = $variant->name;
                                                        if (!$vName) {
                                                            $specs = [];
                                                            if (optional($variant->color)->name) $specs[] = $variant->color->name;
                                                            if (optional($variant->size)->name) $specs[] = $variant->size->name;
                                                            $vName = count($specs) ? implode(' - ', $specs) : 'Variant #' . $variant->id;
                                                        }
                                                    @endphp
                                                    <tr class="{{ $isVOut ? 'table-danger' : ($isVLow ? 'table-warning' : '') }}">
                                                        <td class="align-middle font-weight-bold text-truncate" title="{{ $vName }}">
                                                            <i class="fas fa-tag text-muted mr-1" style="font-size: 10px;"></i> {{ $vName }}
                                                        </td>
                                                        <td class="align-middle text-muted text-truncate" title="{{ $variant->sku ?? ($product->sku ?? '-') }}">
                                                            {{ $variant->sku ?? ($product->sku ?? '-') }}
                                                        </td>
                                                        <td class="text-center align-middle font-weight-bold">
                                                            <span class="badge badge-{{ $isVOut ? 'danger' : ($isVLow ? 'warning text-dark' : 'success') }}">
                                                                {{ $vStock }} {{ $product->unit->name ?? '' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            @if($isVOut)
                                                                <span class="badge badge-danger" style="font-size: 9.5px;">OUT OF STOCK</span>
                                                            @elseif($isVLow)
                                                                <span class="badge badge-warning text-dark" style="font-size: 9.5px;">LOW STOCK</span>
                                                            @else
                                                                <span class="badge badge-success" style="font-size: 9.5px;">HEALTHY</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            @can('Manage Order Place')
                                                            <button type="button" 
                                                                    class="btn btn-xs btn-outline-warning add-variant-to-basket font-weight-bold" 
                                                                    data-product-id="{{ $product->id }}" 
                                                                    data-variant-id="{{ $variant->id }}" 
                                                                    data-variant-name="{{ $vName }}"
                                                                    data-product-name="{{ $product->name }}"
                                                                    data-price="{{ $variant->price ?: ($product->purchase_price ?: $product->price) }}"
                                                                    title="Add {{ $vName }} to Procurement Basket"
                                                                    style="padding: 2px 8px; font-size: 11px; border-radius: 4px;">
                                                                <i class="fas fa-cart-plus mr-1"></i> Reorder
                                                            </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-muted mb-1 mt-3 text-center" style="font-size: 13.5px; font-weight: 500;">
        Showing 
        <span class="text-dark font-weight-bold">
            {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
        </span> 
        of 
        <span class="text-dark font-weight-bold">
            {{ $products->total() }}
        </span> 
        low stock products
    </p>

    <div class="mt-3 d-flex justify-content-center flex-wrap custom-pagination" id="pagination-container">
        {{ $products->links() }}
    </div>
@else
    <div class="alert alert-success d-flex align-items-center">
        <i class="fas fa-check-circle mr-2" style="font-size: 16px;"></i> 
        <div>
            @if(request('search'))
                No products found matching "{{ request('search') }}"
            @elseif(request('category_id') || request('brand_id') || request('vendor_id'))
                No products found with low stock for the selected filters.
            @else
                All products are adequately stocked!
            @endif
        </div>
    </div>
@endif
