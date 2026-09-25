@forelse($products as $product)
    @php
        $override = $overrides->get($product->id);
        $currentMode = $override ? $override->visibility_mode : 'standard';
        $stock = (float) ($product->inventory_stock ?? $product->inventoryStocks()->sum('quantity'));
        $hasImage = !empty($product->thumb_image) && file_exists(public_path($product->thumb_image));
        $thumb = $hasImage ? asset($product->thumb_image) : asset('uploads/no-image.svg');
        $sku = $product->product_number ?? $product->sku ?? 'N/A';
    @endphp
    <tr id="matrix-product-row-{{ $product->id }}" style="border-bottom: 1px solid #f1f5f9;">
        <td style="padding: 12px 14px; vertical-align: middle;">
            <div class="d-flex align-items-center">
                <img src="{{ $thumb }}" alt="{{ $product->name }}" 
                     onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';"
                     class="rounded mr-3 shadow-sm border" 
                     style="width: 44px; height: 44px; object-fit: cover;">
                <div>
                    <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $product->name }}</div>
                    <div class="text-muted small" style="font-size: 11px;">
                        <span><strong>SKU:</strong> {{ $sku }}</span>
                        <span class="mx-1">&bull;</span>
                        <span>{{ optional($product->category)->name ?? 'General' }}</span>
                    </div>
                </div>
            </div>
        </td>
        <td style="padding: 12px 14px; vertical-align: middle; text-align: center;">
            @if($stock > 0)
                <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ number_format($stock) }} {{ optional($product->unit)->name ?? 'pcs' }}
                </span>
            @else
                <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 11px;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> 0 pcs (Out of Stock)
                </span>
            @endif
        </td>
        <td style="padding: 12px 14px; vertical-align: middle; text-align: center;">
            @if(!empty($hasTarget) && $currentMode === 'force_in_stock')
                <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 20px; font-size: 11px;">
                    <i class="fas fa-check-circle mr-1"></i> Priority In-Stock
                </span>
            @elseif(!empty($hasTarget) && $currentMode === 'force_out_of_stock')
                <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; font-size: 11px;">
                    <i class="fas fa-ban mr-1"></i> Restricted (OOS)
                </span>
            @elseif(!empty($hasTarget) && $currentMode === 'hide_product')
                <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(100, 116, 139, 0.15); color: #475569; border: 1px solid rgba(100, 116, 139, 0.3); border-radius: 20px; font-size: 11px;">
                    <i class="fas fa-eye-slash mr-1"></i> Hidden
                </span>
            @else
                <span class="badge badge-light border text-muted px-2 py-1 font-weight-bold" style="font-size: 11px;">
                    <i class="fas fa-cube mr-1"></i> {{ !empty($hasTarget) ? 'Real Warehouse Stock' : 'Standard Catalog' }}
                </span>
            @endif
        </td>
        <td style="padding: 12px 14px; vertical-align: middle; text-align: right;">
            <div class="btn-group btn-group-sm matrix-toggle-group" role="group" data-product-id="{{ $product->id }}">
                <button type="button" 
                        class="btn btn-sm btn-matrix-toggle {{ (!empty($hasTarget) && $currentMode === 'force_in_stock') ? 'btn-success text-white font-weight-bold active' : 'btn-outline-success' }}"
                        data-mode="force_in_stock" 
                        title="Force Priority In-Stock for this client">
                    <i class="fas fa-check mr-1"></i> Force In-Stock
                </button>
                <button type="button" 
                        class="btn btn-sm btn-matrix-toggle {{ (!empty($hasTarget) && $currentMode === 'force_out_of_stock') ? 'btn-danger text-white font-weight-bold active' : 'btn-outline-danger' }}"
                        data-mode="force_out_of_stock" 
                        title="Restrict Out-of-Stock for this client">
                    <i class="fas fa-ban mr-1"></i> Force OOS
                </button>
                <button type="button" 
                        class="btn btn-sm btn-matrix-toggle {{ (!empty($hasTarget) && $currentMode === 'standard') ? 'btn-secondary text-white font-weight-bold active' : 'btn-outline-secondary' }}"
                        data-mode="standard" 
                        title="Revert to real warehouse stock">
                    <i class="fas fa-undo mr-1"></i> Reset Real Stock
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-5 bg-white">
            <div class="py-3">
                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                <div class="h6 font-weight-bold text-dark">No products found</div>
                <p class="small text-muted mb-0">Try changing your search or category filter.</p>
            </div>
        </td>
    </tr>
@endforelse

@if($products->hasPages())
    <tr>
        <td colspan="4" class="py-3 bg-light">
            <div class="d-flex justify-content-between align-items-center px-2">
                <span class="small text-muted">
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                </span>
                <div class="matrix-pagination">
                    {{ $products->links() }}
                </div>
            </div>
        </td>
    </tr>
@endif
