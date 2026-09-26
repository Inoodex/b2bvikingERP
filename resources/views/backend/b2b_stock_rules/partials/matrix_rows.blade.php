@forelse($products as $product)
    @php
        $override = $overrides->get($product->id);
        $currentMode = $override ? $override->visibility_mode : 'standard';
        $stock = (float) ($product->inventory_stock ?? $product->inventoryStocks()->sum('quantity'));
        $hasImage = !empty($product->thumb_image) && file_exists(public_path($product->thumb_image));
        $thumb = $hasImage ? asset($product->thumb_image) : asset('uploads/no-image.svg');
        $sku = $product->product_number ?? $product->sku ?? 'N/A';
        $reservedQty = ($override && $override->reserved_qty) ? $override->reserved_qty : null;
    @endphp
    <tr id="matrix-product-row-{{ $product->id }}" class="matrix-row-item" style="border-bottom: 1px solid #f1f5f9;">
        {{-- Column 1: Product & Category Info --}}
        <td style="padding: 12px 14px; vertical-align: middle;">
            <div class="d-flex align-items-center">
                <img src="{{ $thumb }}" alt="{{ $product->name }}" 
                     onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';"
                     class="rounded mr-3 shadow-sm border" 
                     style="width: 44px; height: 44px; object-fit: cover;">
                <div>
                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 13px; max-width: 280px;" title="{{ $product->name }}">
                        {{ $product->name }}
                    </div>
                    <div class="text-muted small" style="font-size: 11px;">
                        <span><strong>SKU:</strong> {{ $sku }}</span>
                        <span class="mx-1">&bull;</span>
                        <span>{{ optional($product->category)->name ?? 'General' }}</span>
                    </div>
                </div>
            </div>
        </td>

        {{-- Column 2: Physical Warehouse Stock --}}
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

        {{-- Column 3: Active Override Status & Live Quota Badge --}}
        <td style="padding: 12px 14px; vertical-align: middle; text-align: center;" class="matrix-status-cell">
            @if(!empty($hasTarget) && $currentMode === 'force_in_stock')
                <span class="matrix-status-pill matrix-status-green">
                    <i class="fas fa-bolt mr-1"></i> Priority ({{ $reservedQty ? number_format($reservedQty) . ' pcs' : 'Quota' }})
                </span>
            @elseif(!empty($hasTarget) && $currentMode === 'force_out_of_stock')
                <span class="matrix-status-pill matrix-status-red">
                    <i class="fas fa-ban mr-1"></i> Restricted (OOS)
                </span>
            @elseif(!empty($hasTarget) && $currentMode === 'hide_product')
                <span class="matrix-status-pill matrix-status-slate">
                    <i class="fas fa-eye-slash mr-1"></i> Hidden
                </span>
            @else
                <span class="matrix-status-pill matrix-status-auto">
                    <i class="fas fa-warehouse mr-1"></i> {{ !empty($hasTarget) ? 'Real Warehouse Stock' : 'Standard Catalog' }}
                </span>
            @endif
        </td>

        {{-- Column 4: Linear Segmented Override Pill + Inline Virtual Quota Input --}}
        <td style="padding: 12px 14px; vertical-align: middle; text-align: right;">
            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 8px;">
                {{-- Linear-style 3-Way Segmented Control --}}
                <div class="matrix-segmented-control" data-product-id="{{ $product->id }}">
                    <button type="button" 
                            class="matrix-seg-btn seg-in-stock {{ (!empty($hasTarget) && $currentMode === 'force_in_stock') ? 'active' : '' }}" 
                            data-mode="force_in_stock" 
                            title="Priority In-Stock: Make available for this client">
                        <i class="fas fa-check-circle"></i>
                        <span>In-Stock</span>
                    </button>
                    <button type="button" 
                            class="matrix-seg-btn seg-oos {{ (!empty($hasTarget) && $currentMode === 'force_out_of_stock') ? 'active' : '' }}" 
                            data-mode="force_out_of_stock" 
                            title="Restricted OOS: Block this client from ordering">
                        <i class="fas fa-ban"></i>
                        <span>OOS</span>
                    </button>
                    <button type="button" 
                            class="matrix-seg-btn seg-auto {{ (!empty($hasTarget) && $currentMode === 'standard') ? 'active' : '' }}" 
                            data-mode="standard" 
                            title="Auto: Revert to real warehouse stock">
                        <i class="fas fa-undo-alt"></i>
                        <span>Auto</span>
                    </button>
                </div>

                {{-- Inline Virtual Stock Quota Quantity Input --}}
                <div class="matrix-quota-wrapper {{ (!empty($hasTarget) && $currentMode === 'force_in_stock') ? 'active' : 'disabled' }}" 
                     data-product-id="{{ $product->id }}" 
                     title="{{ (!empty($hasTarget) && $currentMode === 'force_in_stock') ? 'Virtual stock quota quantity (e.g. 100 pcs)' : 'Set status to In-Stock to specify virtual stock quota' }}">
                    <div class="matrix-quota-box">
                        <input type="number" 
                               class="matrix-quota-input" 
                               data-product-id="{{ $product->id }}"
                               placeholder="Qty" 
                               min="1" 
                               step="1"
                               value="{{ $reservedQty ?? '' }}"
                               {{ (!empty($hasTarget) && $currentMode === 'force_in_stock') ? '' : 'disabled' }}>
                        <span class="matrix-quota-unit">pcs</span>
                    </div>
                    <span class="matrix-quota-feedback" id="quota-feedback-{{ $product->id }}" style="display: none;">
                        <i class="fas fa-check text-success"></i>
                    </span>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-5 bg-white">
            <div class="py-3">
                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                <div class="h6 font-weight-bold text-dark">No products found</div>
                <p class="small text-muted mb-0">Try changing your search, category, or catalog filter.</p>
            </div>
        </td>
    </tr>
@endforelse

@if($products->hasPages())
    <tr>
        <td colspan="4" class="py-3 bg-light">
            <div class="d-flex justify-content-between align-items-center px-2 flex-wrap" style="gap: 10px;">
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
