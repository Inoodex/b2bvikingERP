@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>New Stock Transfer</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></div>
            <div class="breadcrumb-item">Create</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.stock-transfers.store') }}" method="POST" id="transfer_form">
            @csrf
            <div class="row">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-route mr-2 text-primary"></i> Route & Logistics</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">From Warehouse (Source) <span class="text-danger">*</span></label>
                                <select name="from_outlet_id" id="from_outlet_select" class="form-control select2" required>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $outlet->name ?? $outlet->outlet_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">To Outlet / Branch (Destination) <span class="text-danger">*</span></label>
                                <select name="to_outlet_id" id="to_outlet_select" class="form-control select2" required>
                                    <option value="" disabled selected>Select Destination...</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->name ?? $outlet->outlet_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Vehicle / Truck No</label>
                                        <input type="text" name="vehicle_no" class="form-control" placeholder="e.g. DHAKA-METRO-1234" style="border-radius: 8px;">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Challan / Ref No</label>
                                        <input type="text" name="challan_no" class="form-control" placeholder="Optional" style="border-radius: 8px;">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Driver Name</label>
                                        <input type="text" name="driver_name" class="form-control" placeholder="Driver name" style="border-radius: 8px;">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Driver Phone</label>
                                        <input type="text" name="driver_phone" class="form-control" placeholder="Mobile no" style="border-radius: 8px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Transfer Notes</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Special handling or dispatch notes..." style="border-radius: 8px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row align-items-center w-100 no-gutters">
                                <div class="col-md-5">
                                    <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> Products to Transfer</h4>
                                </div>
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center">
                                        <select id="product_picker_select" class="form-control select2" style="width: 100%;">
                                            <option value="" disabled selected>+ Select Product to Add to Transfer...</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-has-variants="{{ $p->variants->isNotEmpty() ? '1' : '0' }}">
                                                    {{ $p->name }} ({{ $p->variants->isNotEmpty() ? $p->variants->count() . ' Variants' : 'Single' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3" style="background: #f8fafc; min-height: 280px;">
                            <div id="transfer_products_container">
                                {{-- Master Product Cards with Nested Variant Grids --}}
                            </div>

                            <div id="empty_products_notice" class="text-center py-5 text-muted" style="display: none;">
                                <div class="mb-3 d-inline-flex align-items-center justify-content-center bg-white border rounded-circle shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-dolly fa-2x text-primary"></i>
                                </div>
                                <h5 class="text-dark font-weight-bold">No Products Added Yet</h5>
                                <p class="small text-muted mb-0">Select a product from the dropdown above to begin building this transfer.</p>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top text-right py-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="text-muted font-weight-bold mr-2 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Grand Total Units:</span>
                                <span id="grand_total_transfer_units" class="badge badge-primary font-weight-bold px-3 py-2" style="font-size: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(103,119,239,0.25);">0.00</span>
                            </div>
                            <div>
                                <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-light border mr-2 font-weight-bold px-3" style="border-radius: 8px;">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold" style="border-radius: 8px;">
                                    <i class="fas fa-save mr-1"></i> Create Transfer (Draft)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<style>
    /* Ultra Modern Enterprise Master Card */
    .product-master-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        margin-bottom: 16px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        transition: all 0.2s ease-in-out;
    }
    .product-master-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    }
    .master-card-header {
        padding: 12px 18px;
        cursor: pointer;
        background: #ffffff;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        transition: background 0.15s ease;
    }
    .master-card-header:hover {
        background: #fbfcfe;
    }
    .master-card-header.expanded {
        border-bottom: 1px solid #edf2f7;
    }

    /* Modern KPI Stat Boxes */
    .kpi-stat-box {
        display: inline-flex;
        flex-direction: column;
        justify-content: center;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        min-width: 110px;
        text-align: right;
    }
    .kpi-stat-box.kpi-source {
        background: #f0f9ff;
        border-color: #bae6fd;
    }
    .kpi-stat-box.kpi-transfer {
        background: #f5f3ff;
        border-color: #ddd6fe;
    }
    .kpi-label {
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1px;
    }
    .kpi-stat-box.kpi-source .kpi-label {
        color: #0369a1;
    }
    .kpi-stat-box.kpi-transfer .kpi-label {
        color: #6d28d9;
    }
    .kpi-value {
        font-size: 13px;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Variant Sub-grid Styling */
    .variant-subgrid {
        background: #f8fafc;
        padding: 14px 18px;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    .variant-table-card {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .variant-table-card thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 14px;
    }
    .variant-table-card tbody td {
        padding: 10px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .variant-table-card tbody tr:last-child td {
        border-bottom: none;
    }
    .variant-table-card tbody tr:hover {
        background: #fbfcfe;
    }

    /* Buttons & Inputs */
    .btn-toggle-circle {
        width: 32px;
        height: 32px;
        min-width: 32px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-toggle-circle:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .chevron-icon {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .chevron-icon.rotate {
        transform: rotate(180deg);
    }

    .btn-card-trash {
        width: 36px;
        height: 36px;
        min-width: 36px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-card-trash:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
    }

    .modern-qty-input {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        height: 36px;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        transition: all 0.2s;
    }
    .modern-qty-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }

    .modern-note-input {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        height: 36px;
        font-size: 12px;
    }
</style>

<script>
    $(document).ready(function () {
        let globalItemIndex = 0;
        const activeProductIds = new Set();

        // Check empty state
        function checkEmptyState() {
            if ($('#transfer_products_container .product-master-card').length === 0) {
                $('#empty_products_notice').show();
            } else {
                $('#empty_products_notice').hide();
            }
            calculateGrandTotal();
        }

        // Calculate Grand Total Units
        function calculateGrandTotal() {
            let total = 0;
            $('.item-transfer-qty-input').each(function() {
                const val = parseFloat($(this).val()) || 0;
                total += val;
            });
            $('#grand_total_transfer_units').text(total.toFixed(2));
        }

        // Add Product to Transfer Container
        window.addProductToTransfer = function(productId, prefilledQuantities = {}) {
            if (activeProductIds.has(Number(productId))) {
                if (window.toastr) toastr.info('This product is already in the transfer list.');
                return;
            }

            const fromOutletId = $('#from_outlet_select').val() || 1;
            const $select = $('#product_picker_select');
            const $selectContainer = $select.next('.select2-container');

            // Inline Select2 Loading Feedback
            $select.prop('disabled', true);
            $selectContainer.find('.select2-selection__rendered').html('<span class="text-primary font-weight-bold"><i class="fas fa-spinner fa-spin mr-2"></i> Loading product details...</span>');

            $.ajax({
                url: "{{ route('admin.stock-transfers.get-product-stock') }}",
                method: "GET",
                data: {
                    product_id: productId,
                    outlet_id: fromOutletId
                },
                success: function(res) {
                    if (!res.success) {
                        if (window.toastr) toastr.error(res.message || 'Error loading product');
                        return;
                    }

                    activeProductIds.add(Number(productId));
                    renderProductCard(res, prefilledQuantities);
                    checkEmptyState();
                },
                error: function() {
                    if (window.toastr) toastr.error('Failed to fetch product stock details');
                },
                complete: function() {
                    $select.prop('disabled', false);
                    $select.val('').trigger('change.select2');
                }
            });
        };

        // Get Product Thumb HTML (with proper No Image fallback)
        function getProductThumbHtml(thumbImage) {
            if (thumbImage) {
                return `
                    <div class="product-thumb-wrapper mr-3 position-relative" style="width: 48px; height: 48px; min-width: 48px;">
                        <img src="${thumbImage}" onerror="this.onerror=null; this.style.display='none'; $(this).next('.no-img-box').css('display', 'flex');" class="rounded border shadow-2xs" style="width: 48px; height: 48px; object-fit: cover; background: #fff; border-radius: 10px;">
                        <div class="no-img-box rounded border align-items-center justify-content-center bg-light text-muted" style="width: 48px; height: 48px; display: none; flex-direction: column; border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                            <i class="fas fa-image text-secondary" style="font-size: 16px;"></i>
                            <span style="font-size: 8px; line-height: 1; margin-top: 3px; color: #64748b; font-weight: 700;">NO IMG</span>
                        </div>
                    </div>
                `;
            } else {
                return `
                    <div class="rounded mr-3 border d-flex align-items-center justify-content-center bg-light text-muted shadow-2xs" style="width: 48px; height: 48px; min-width: 48px; flex-direction: column; border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                        <i class="fas fa-image text-secondary" style="font-size: 16px;"></i>
                        <span style="font-size: 8px; line-height: 1; margin-top: 3px; color: #64748b; font-weight: 700;">NO IMG</span>
                    </div>
                `;
            }
        }

        // Render Product Card (Master-Variant Architecture)
        function renderProductCard(p, prefilledQuantities = {}) {
            const cardId = `pcard_${p.product_id}`;
            const thumbHtml = getProductThumbHtml(p.thumb_image);

            if (p.is_variable && p.variants && p.variants.length > 0) {
                // Variable Product (Nested Sub-Grid)
                let variantRowsHtml = '';
                let totalProductTransferQty = 0;

                p.variants.forEach(function(v) {
                    const itemIdx = globalItemIndex++;
                    const prefilledQty = (prefilledQuantities[v.id] !== undefined) ? prefilledQuantities[v.id] : '';
                    if (prefilledQty) totalProductTransferQty += parseFloat(prefilledQty) || 0;

                    const stock = parseFloat(v.stock) || 0;
                    const stockBadgeHtml = stock > 0 
                        ? `<span class="badge font-weight-bold px-3 py-1 variant-stock-badge" data-stock="${stock}" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-check-circle mr-1"></i>${stock.toFixed(2)} ${p.unit}</span>`
                        : `<span class="badge font-weight-bold px-3 py-1 variant-stock-badge" data-stock="${stock}" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-times-circle mr-1"></i>0.00 (Out of Stock)</span>`;

                    variantRowsHtml += `
                        <tr class="variant-row" data-variant-id="${v.id}" data-product-id="${p.product_id}">
                            <td style="width: 36%;">
                                <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                                    <span>${v.name}</span>
                                    ${v.sku ? `<small class="text-muted ml-2 font-weight-normal">(SKU: ${v.sku})</small>` : ''}
                                </div>
                                <input type="hidden" name="items[${itemIdx}][product_id]" value="${p.product_id}">
                                <input type="hidden" name="items[${itemIdx}][variant_id]" value="${v.id}">
                                <input type="hidden" name="items[${itemIdx}][unit_cost]" value="${v.unit_cost}">
                            </td>
                            <td class="text-center" style="width: 24%;">
                                ${stockBadgeHtml}
                            </td>
                            <td style="width: 20%;">
                                <input type="number" step="any" min="0" name="items[${itemIdx}][qty]" 
                                       class="form-control form-control-sm text-center modern-qty-input item-transfer-qty-input variant-qty-input" 
                                       placeholder="0" value="${prefilledQty}">
                            </td>
                            <td style="width: 20%;">
                                <input type="text" name="items[${itemIdx}][item_note]" class="form-control form-control-sm modern-note-input" placeholder="Line note">
                            </td>
                        </tr>
                    `;
                });

                const cardHtml = `
                    <div class="product-master-card" id="${cardId}" data-product-id="${p.product_id}">
                        <div class="master-card-header expanded d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#collapse_${cardId}">
                            <div class="d-flex align-items-center mr-3" style="min-width: 0;">
                                <button type="button" class="btn-toggle-circle mr-3">
                                    <i class="fas fa-chevron-down text-primary chevron-icon rotate" style="font-size: 12px;"></i>
                                </button>
                                ${thumbHtml}
                                <div style="min-width: 0;">
                                    <h6 class="mb-1 text-dark font-weight-bold text-truncate" style="font-size: 15px; letter-spacing: -0.2px;">${p.name}</h6>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge mr-1" style="background: #ede9fe; color: #6d28d9; font-weight: 700; font-size: 10.5px; border-radius: 6px; padding: 3px 8px;">
                                            <i class="fas fa-layer-group mr-1"></i> ${p.variants.length} Variants
                                        </span>
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 10.5px; border-radius: 6px; padding: 3px 8px;">
                                            <i class="fas fa-tag mr-1 text-muted"></i> ${p.category_name || 'General'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-shrink-0">
                                <div class="kpi-stat-box kpi-source mr-2">
                                    <div class="kpi-label"><i class="fas fa-warehouse mr-1"></i> Source Stock</div>
                                    <div class="kpi-value text-info font-weight-bold">
                                        <span class="master-total-source-stock">${parseFloat(p.total_stock).toFixed(2)}</span> <small class="text-muted font-weight-normal">${p.unit}</small>
                                    </div>
                                </div>
                                <div class="kpi-stat-box kpi-transfer mr-2">
                                    <div class="kpi-label"><i class="fas fa-dolly mr-1"></i> Transfer Qty</div>
                                    <div class="kpi-value text-primary font-weight-bold">
                                        <span class="master-total-transfer-qty">${totalProductTransferQty.toFixed(2)}</span> <small class="text-primary font-weight-normal">${p.unit}</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-card-trash remove-product-card-btn" data-product-id="${p.product_id}" title="Remove this product">
                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="collapse show variant-subgrid" id="collapse_${cardId}">
                            <div class="variant-table-card">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Variant Dimension / Specification</th>
                                            <th class="text-center">Available in Source</th>
                                            <th class="text-center">Transfer Qty <span class="text-danger">*</span></th>
                                            <th>Line Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${variantRowsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                $('#transfer_products_container').append(cardHtml);

            } else {
                // Simple Product (Single Row Card)
                const itemIdx = globalItemIndex++;
                const prefilledQty = (prefilledQuantities['null'] !== undefined) ? prefilledQuantities['null'] : (prefilledQuantities[''] !== undefined ? prefilledQuantities[''] : 1);
                const stock = parseFloat(p.simple_stock) || 0;
                const stockBadgeHtml = stock > 0 
                    ? `<span class="badge font-weight-bold px-3 py-1 simple-stock-badge" data-stock="${stock}" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-check-circle mr-1"></i>${stock.toFixed(2)} ${p.unit}</span>`
                    : `<span class="badge font-weight-bold px-3 py-1 simple-stock-badge" data-stock="${stock}" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-times-circle mr-1"></i>0.00 (Out of Stock)</span>`;

                const cardHtml = `
                    <div class="product-master-card p-3" id="${cardId}" data-product-id="${p.product_id}">
                        <div class="row align-items-center">
                            <div class="col-md-4 d-flex align-items-center">
                                ${thumbHtml}
                                <div>
                                    <h6 class="mb-1 text-dark font-weight-bold" style="font-size: 15px;">${p.name}</h6>
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 10.5px; border-radius: 6px; padding: 2px 7px;">Single Item</span>
                                    <input type="hidden" name="items[${itemIdx}][product_id]" value="${p.product_id}">
                                    <input type="hidden" name="items[${itemIdx}][variant_id]" value="">
                                    <input type="hidden" name="items[${itemIdx}][unit_cost]" value="${p.unit_cost}">
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <small class="text-muted d-block font-weight-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Source Stock</small>
                                ${stockBadgeHtml}
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block font-weight-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Transfer Qty <span class="text-danger">*</span></small>
                                <input type="number" step="any" min="0.01" name="items[${itemIdx}][qty]" 
                                       class="form-control form-control-sm text-center modern-qty-input item-transfer-qty-input" 
                                       value="${prefilledQty}" required>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block font-weight-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Line Note</small>
                                <input type="text" name="items[${itemIdx}][item_note]" class="form-control form-control-sm modern-note-input" placeholder="Note">
                            </div>
                            <div class="col-md-1 text-right">
                                <button type="button" class="btn-card-trash remove-product-card-btn mt-3" data-product-id="${p.product_id}" title="Remove Product">
                                    <i class="fas fa-trash-alt" style="font-size: 13px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                $('#transfer_products_container').append(cardHtml);
            }
        }

        // Product Picker Dropdown select
        $('#product_picker_select').on('change', function() {
            const pId = $(this).val();
            if (pId) {
                addProductToTransfer(pId);
            }
        });

        // Toggle Chevron animation
        $(document).on('click', '.master-card-header', function(e) {
            if ($(e.target).closest('.remove-product-card-btn').length) return;
            const $chevron = $(this).find('.chevron-icon');
            $chevron.toggleClass('rotate');
            $(this).toggleClass('expanded');
        });

        // Remove Product Card
        $(document).on('click', '.remove-product-card-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const pId = Number($(this).data('product-id'));
            $(`#pcard_${pId}`).fadeOut(200, function() {
                $(this).remove();
                activeProductIds.delete(pId);
                checkEmptyState();
            });
        });

        // Variant Qty Input Change Listener (Real-time Parent Total Update)
        $(document).on('input change', '.variant-qty-input', function() {
            const $card = $(this).closest('.product-master-card');
            let total = 0;
            $card.find('.variant-qty-input').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $card.find('.master-total-transfer-qty').text(total.toFixed(2));
            calculateGrandTotal();
        });

        $(document).on('input change', '.item-transfer-qty-input', function() {
            calculateGrandTotal();
        });

        // Refresh Stock when Source Warehouse changes
        $('#from_outlet_select').on('change', function() {
            const fromOutletId = $(this).val();
            if (!fromOutletId) return;

            $('.product-master-card').each(function() {
                const $card = $(this);
                const pId = $card.data('product-id');

                $.ajax({
                    url: "{{ route('admin.stock-transfers.get-product-stock') }}",
                    method: "GET",
                    data: {
                        product_id: pId,
                        outlet_id: fromOutletId
                    },
                    success: function(res) {
                        if (res.success) {
                            if (res.is_variable && res.variants) {
                                let totalMasterStock = 0;
                                res.variants.forEach(function(v) {
                                    const $row = $card.find(`.variant-row[data-variant-id="${v.id}"]`);
                                    const stockVal = parseFloat(v.stock) || 0;
                                    totalMasterStock += stockVal;

                                    const $cell = $row.find('.variant-stock-badge').parent();
                                    const newBadge = stockVal > 0 
                                        ? `<span class="badge font-weight-bold px-3 py-1 variant-stock-badge" data-stock="${stockVal}" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-check-circle mr-1"></i>${stockVal.toFixed(2)} ${res.unit}</span>`
                                        : `<span class="badge font-weight-bold px-3 py-1 variant-stock-badge" data-stock="${stockVal}" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-times-circle mr-1"></i>0.00 (Out of Stock)</span>`;
                                    $cell.html(newBadge);
                                });
                                $card.find('.master-total-source-stock').text(totalMasterStock.toFixed(2));
                            } else {
                                const stockVal = parseFloat(res.simple_stock) || 0;
                                const $cell = $card.find('.simple-stock-badge').parent();
                                const newBadge = stockVal > 0 
                                    ? `<span class="badge font-weight-bold px-3 py-1 simple-stock-badge" data-stock="${stockVal}" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-check-circle mr-1"></i>${stockVal.toFixed(2)} ${res.unit}</span>`
                                    : `<span class="badge font-weight-bold px-3 py-1 simple-stock-badge" data-stock="${stockVal}" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 20px; font-size: 11.5px;"><i class="fas fa-times-circle mr-1"></i>0.00 (Out of Stock)</span>`;
                                $cell.html(newBadge);
                            }
                        }
                    }
                });
            });
        });

        // Preload Cart Items (Grouped by Product)
        @if(isset($cartItems) && $cartItems->count() > 0)
            @php
                $grouped = $cartItems->groupBy('product_id');
            @endphp
            @foreach($grouped as $productId => $itemsGroup)
                @php
                    $quantities = [];
                    foreach($itemsGroup as $item) {
                        $vKey = $item->variant_id ?: 'null';
                        $quantities[$vKey] = (float)($item->quantity ?: 1);
                    }
                @endphp
                addProductToTransfer({{ $productId }}, @json($quantities));
            @endforeach
        @endif

        checkEmptyState();

        // Form Submit Validation & Cart Clear
        $('#transfer_form').on('submit', function(e) {
            const hasAnyQuantity = $('.item-transfer-qty-input').toArray().some(input => parseFloat($(input).val()) > 0);
            if (!hasAnyQuantity) {
                e.preventDefault();
                if (window.toastr) toastr.warning('Please enter a transfer quantity for at least one item.');
                return false;
            }

            if (window.cartStore && window.cartStore.request) {
                window.cartStore.request.items = [];
                window.cartStore.request.ids = [];
                window.cartStore.request.count = 0;
                if (window.updateGlobalCartBadges) {
                    window.updateGlobalCartBadges(undefined, 0);
                }
            }
        });
    });
</script>
@endpush
