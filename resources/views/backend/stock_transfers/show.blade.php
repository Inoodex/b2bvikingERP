@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Stock Transfer #{{ $stockTransfer->transfer_no }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></div>
            <div class="breadcrumb-item">Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-route mr-2 text-primary"></i> Route & Logistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 p-3 bg-light rounded text-center border">
                            <div class="font-weight-bold text-muted small text-uppercase">Transfer Route</div>
                            <h6 class="text-primary font-weight-bold mb-1 mt-2">
                                <i class="fas fa-warehouse mr-1"></i> {{ $stockTransfer->fromOutlet ? ($stockTransfer->fromOutlet->outlet_name ?? $stockTransfer->fromOutlet->name) : 'Central Warehouse' }}
                            </h6>
                            <i class="fas fa-arrow-down my-1 text-muted"></i>
                            <h6 class="text-success font-weight-bold mb-0">
                                <i class="fas fa-store mr-1"></i> {{ $stockTransfer->toOutlet ? ($stockTransfer->toOutlet->outlet_name ?? $stockTransfer->toOutlet->name) : 'Outlet #' . $stockTransfer->to_outlet_id }}
                            </h6>
                        </div>

                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width: 140px;">Status:</th>
                                <td>
                                    @if($stockTransfer->status === 'received')
                                        <span class="badge badge-success"><i class="fas fa-check-double mr-1"></i> Received (Stock In)</span>
                                    @elseif($stockTransfer->status === 'dispatched')
                                        <span class="badge badge-primary"><i class="fas fa-truck mr-1"></i> Dispatched (In Transit)</span>
                                    @elseif($stockTransfer->status === 'cancelled')
                                        <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Cancelled</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Draft (Pending Dispatch)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Transfer Date:</th>
                                <td>{{ optional($stockTransfer->transfer_date)->format('d M, Y') ?: optional($stockTransfer->created_at)->format('d M, Y') }}</td>
                            </tr>
                            @if($stockTransfer->dispatched_at)
                            <tr>
                                <th class="text-muted">Dispatched At:</th>
                                <td>{{ optional($stockTransfer->dispatched_at)->format('d M, Y h:i A') }}</td>
                            </tr>
                            @endif
                            @if($stockTransfer->received_at)
                            <tr>
                                <th class="text-muted">Received At:</th>
                                <td>{{ optional($stockTransfer->received_at)->format('d M, Y h:i A') }}</td>
                            </tr>
                            @endif
                            @if($stockTransfer->vehicle_no)
                            <tr>
                                <th class="text-muted">Vehicle No:</th>
                                <td><strong>{{ $stockTransfer->vehicle_no }}</strong></td>
                            </tr>
                            @endif
                            @if($stockTransfer->driver_name)
                            <tr>
                                <th class="text-muted">Driver:</th>
                                <td>{{ $stockTransfer->driver_name }} ({{ $stockTransfer->driver_phone ?: 'No phone' }})</td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-muted">Initiated By:</th>
                                <td>{{ $stockTransfer->requestedByUser->name ?? 'System' }}</td>
                            </tr>
                        </table>

                        @if($stockTransfer->note)
                            <div class="alert alert-light border mt-3 mb-0 small">
                                <strong>Notes:</strong> {{ $stockTransfer->note }}
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light border-top py-3">
                        @if($stockTransfer->status === 'draft')
                            <form id="dispatch_transfer_form" action="{{ route('admin.stock-transfers.dispatch', $stockTransfer->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="button" class="btn btn-primary btn-block font-weight-bold shadow-sm py-2" id="btn_dispatch_transfer">
                                    <i class="fas fa-truck mr-1"></i> Dispatch Transfer (Deduct Source Stock)
                                </button>
                            </form>
                            <form id="cancel_transfer_form" action="{{ route('admin.stock-transfers.cancel', $stockTransfer->id) }}" method="POST">
                                @csrf
                                <button type="button" class="btn btn-outline-danger btn-block font-weight-bold py-2" id="btn_cancel_transfer">
                                    <i class="fas fa-times-circle mr-1"></i> Cancel Transfer
                                </button>
                            </form>
                        @elseif($stockTransfer->status === 'dispatched')
                            <a href="{{ route('admin.stock-transfers.receive-form', $stockTransfer->id) }}" class="btn btn-success btn-block font-weight-bold shadow-sm py-2 mb-2">
                                <i class="fas fa-box-check mr-1"></i> Receive & Verify Stock at Destination
                            </a>
                            <a href="{{ route('admin.stock-transfers.pdf', $stockTransfer->id) }}" target="_blank" class="btn btn-danger btn-block font-weight-bold py-2">
                                <i class="fas fa-file-pdf mr-1"></i> Download Gate Pass / Challan PDF
                            </a>
                        @elseif($stockTransfer->status === 'received')
                            <a href="{{ route('admin.stock-transfers.pdf', $stockTransfer->id) }}" target="_blank" class="btn btn-danger btn-block font-weight-bold py-2">
                                <i class="fas fa-file-pdf mr-1"></i> Download Transfer Challan PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> Transferred Items (<span id="transfer_items_count">{{ $stockTransfer->items->count() }}</span>)</h4>
                    </div>
                    <div class="card-body p-0">
                        <div id="insufficient_stock_alert" class="alert alert-warning m-3 border-warning d-flex align-items-center" style="background: #fffbeb; display: {{ (!empty($hasInsufficientStock) && $stockTransfer->status === 'draft') ? 'flex' : 'none' }} !important;">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>
                            <div>
                                <strong class="text-dark">Insufficient Source Stock Alert:</strong>
                                <div class="text-muted small mt-1">
                                    One or more products have lower available stock in the Source Warehouse (<strong>{{ $stockTransfer->fromOutlet ? ($stockTransfer->fromOutlet->outlet_name ?? $stockTransfer->fromOutlet->name) : 'Source Warehouse' }}</strong>). You can adjust the transfer quantity to match available stock or remove the deficient item below to dispatch immediately.
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40%;">Product / Variant Specification</th>
                                        @if($stockTransfer->status === 'draft')
                                            <th class="text-center" style="width: 20%;">Source In-Stock</th>
                                        @endif
                                        <th class="text-center" style="width: 15%;">Transfer Qty</th>
                                        <th class="text-center" style="width: 15%;">Received Qty</th>
                                        @if($stockTransfer->status === 'draft')
                                            <th class="text-center" style="width: 10%;">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="transfer_items_tbody">
                                    @php
                                        $groupedItems = $stockTransfer->items->groupBy('product_id');
                                    @endphp
                                    @foreach($groupedItems as $productId => $group)
                                        @php
                                            $firstItem = $group->first();
                                            $product = $firstItem->product;
                                            $hasVariants = $group->count() > 1 || $firstItem->variant_id !== null;
                                            $totalProductQty = $group->sum('qty');
                                            $totalReceivedQty = $group->sum('received_qty');
                                            $hasGroupStockDeficiency = ($stockTransfer->status === 'draft') && $group->contains(function($it) {
                                                return ((float)($it->source_current_stock ?? 0) < (float)$it->qty);
                                            });

                                            $imgSrc = null;
                                            if ($product && !empty($product->thumb_image)) {
                                                if (str_starts_with($product->thumb_image, 'http')) {
                                                    $imgSrc = $product->thumb_image;
                                                } elseif (str_starts_with($product->thumb_image, 'uploads/')) {
                                                    $imgSrc = asset($product->thumb_image);
                                                } else {
                                                    $imgSrc = asset('storage/' . $product->thumb_image);
                                                }
                                            }
                                        @endphp

                                        @if($hasVariants)
                                            {{-- Master Product Header Row --}}
                                            <tr class="bg-light font-weight-bold {{ $hasGroupStockDeficiency ? 'table-warning' : '' }}">
                                                <td colspan="{{ $stockTransfer->status === 'draft' ? 2 : 1 }}">
                                                    <div class="d-flex align-items-center">
                                                        @if($imgSrc)
                                                            <img src="{{ $imgSrc }}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" class="rounded mr-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded mr-2 border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 34px; height: 34px; min-width: 34px; font-size: 8px; flex-direction: column;">
                                                                <i class="fas fa-image text-secondary" style="font-size: 11px;"></i>
                                                                <span>No img</span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span class="text-dark">{{ $product->name ?? 'Product #' . $productId }}</span>
                                                            <span class="badge badge-secondary ml-2 font-weight-normal" style="font-size: 11px;">{{ $group->count() }} Variants</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center text-primary font-weight-bold">
                                                    {{ number_format($totalProductQty, 2) }} {{ $product->unit->name ?? 'pcs' }}
                                                </td>
                                                <td class="text-center">
                                                    @if($stockTransfer->status === 'received')
                                                        <span class="badge badge-success font-weight-bold">{{ number_format($totalReceivedQty, 2) }} {{ $product->unit->name ?? 'pcs' }}</span>
                                                    @else
                                                        <span class="text-muted small">Pending</span>
                                                    @endif
                                                </td>
                                                @if($stockTransfer->status === 'draft')
                                                    <td></td>
                                                @endif
                                            </tr>

                                            {{-- Variant Sub-Rows --}}
                                            @foreach($group as $item)
                                                @php
                                                    $isStockDeficient = ($stockTransfer->status === 'draft') && ((float)($item->source_current_stock ?? 0) < (float)$item->qty);
                                                    $vTitle = trim($item->variant->name ?? '');
                                                    if (empty($vTitle) && $item->variant) {
                                                        $cName = $item->variant->color->name ?? $item->variant->color ?? '';
                                                        $sName = $item->variant->size->name ?? $item->variant->size ?? '';
                                                        $vTitle = trim($cName . ' ' . $sName);
                                                    }
                                                    if (empty($vTitle)) {
                                                        $vTitle = $item->variant_id ? ('Variant #' . $item->variant_id) : 'Standard';
                                                    }
                                                @endphp
                                                <tr id="item_row_{{ $item->id }}" class="{{ $isStockDeficient ? 'table-danger' : '' }}" data-item-id="{{ $item->id }}" data-source-stock="{{ (float)($item->source_current_stock ?? 0) }}" data-unit="{{ $item->product->unit->name ?? 'pcs' }}">
                                                    <td style="padding-left: 35px;">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2"></i>
                                                            <div>
                                                                <span class="font-weight-bold text-dark">{{ $vTitle }}</span>
                                                                @if($item->item_note)
                                                                    <br><small class="text-muted"><i class="fas fa-info-circle mr-1"></i>{{ $item->item_note }}</small>
                                                                @endif
                                                            </div>
                                                            @if($isStockDeficient)
                                                                <span class="badge badge-danger text-white font-weight-bold out-of-stock-badge ml-2" style="font-size: 10px;">
                                                                    Insufficient ({{ number_format((float)$item->source_current_stock, 2) }} in Stock)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    @if($stockTransfer->status === 'draft')
                                                        <td class="text-center">
                                                            <span class="badge {{ $isStockDeficient ? 'badge-danger' : 'badge-success' }} font-weight-bold source-stock-badge">
                                                                {{ number_format((float)($item->source_current_stock ?? 0), 2) }} {{ $item->product->unit->name ?? 'pcs' }}
                                                            </span>
                                                        </td>
                                                    @endif
                                                    <td class="text-center">
                                                        @if($stockTransfer->status === 'draft')
                                                            <div class="d-inline-flex align-items-center">
                                                                <span class="badge badge-primary font-weight-bold transfer-qty-display mr-1" style="font-size: 12px;">
                                                                    {{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}
                                                                </span>
                                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-item-qty" data-id="{{ $item->id }}" data-qty="{{ (float)$item->qty }}" title="Adjust Transfer Quantity" style="padding: 1px 5px; font-size: 10px;">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="badge badge-primary font-weight-bold">{{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($stockTransfer->status === 'received')
                                                            <span class="badge badge-success font-weight-bold">{{ number_format((float)$item->received_qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}</span>
                                                        @else
                                                            <span class="text-muted small">Pending</span>
                                                        @endif
                                                    </td>
                                                    @if($stockTransfer->status === 'draft')
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" data-id="{{ $item->id }}" title="Remove this variant" style="padding: 1px 6px; font-size: 11px;">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- Single Simple Product Row --}}
                                            @php
                                                $item = $firstItem;
                                                $isStockDeficient = ($stockTransfer->status === 'draft') && ((float)($item->source_current_stock ?? 0) < (float)$item->qty);
                                            @endphp
                                            <tr id="item_row_{{ $item->id }}" class="{{ $isStockDeficient ? 'table-danger' : '' }}" data-item-id="{{ $item->id }}" data-source-stock="{{ (float)($item->source_current_stock ?? 0) }}" data-unit="{{ $item->product->unit->name ?? 'pcs' }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($imgSrc)
                                                            <img src="{{ $imgSrc }}" onerror="this.onerror=null; this.src='{{ asset('uploads/no-image.svg') }}';" class="rounded mr-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded mr-2 border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 34px; height: 34px; min-width: 34px; font-size: 8px; flex-direction: column;">
                                                                <i class="fas fa-image text-secondary" style="font-size: 11px;"></i>
                                                                <span>No img</span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span class="font-weight-bold text-dark">{{ $product->name ?? 'Product #' . $item->product_id }}</span>
                                                            @if($item->item_note)
                                                                <br><small class="text-muted"><i class="fas fa-info-circle mr-1"></i>{{ $item->item_note }}</small>
                                                            @endif
                                                        </div>
                                                        @if($isStockDeficient)
                                                            <span class="badge badge-danger text-white font-weight-bold out-of-stock-badge ml-2" style="font-size: 10px;">
                                                                Insufficient ({{ number_format((float)$item->source_current_stock, 2) }} in Stock)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                @if($stockTransfer->status === 'draft')
                                                    <td class="text-center">
                                                        <span class="badge {{ $isStockDeficient ? 'badge-danger' : 'badge-success' }} font-weight-bold source-stock-badge">
                                                            {{ number_format((float)($item->source_current_stock ?? 0), 2) }} {{ $item->product->unit->name ?? 'pcs' }}
                                                        </span>
                                                    </td>
                                                @endif
                                                <td class="text-center">
                                                    @if($stockTransfer->status === 'draft')
                                                        <div class="d-inline-flex align-items-center">
                                                            <span class="badge badge-primary font-weight-bold transfer-qty-display mr-1" style="font-size: 12px;">
                                                                {{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}
                                                            </span>
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-item-qty" data-id="{{ $item->id }}" data-qty="{{ (float)$item->qty }}" title="Adjust Transfer Quantity" style="padding: 1px 5px; font-size: 10px;">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="badge badge-primary font-weight-bold">{{ number_format((float)$item->qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($stockTransfer->status === 'received')
                                                        <span class="badge badge-success font-weight-bold">{{ number_format((float)$item->received_qty, 2) }} {{ $item->product->unit->name ?? 'pcs' }}</span>
                                                    @else
                                                        <span class="text-muted small">Pending</span>
                                                    @endif
                                                </td>
                                                @if($stockTransfer->status === 'draft')
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" data-id="{{ $item->id }}" title="Remove this item" style="padding: 1px 6px; font-size: 11px;">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn_dispatch_transfer').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Dispatch this Transfer?",
                text: "This will deduct the items from the source warehouse inventory and mark the transfer as in-transit.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#6777ef",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Dispatch Now!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#dispatch_transfer_form').submit();
                }
            });
        });

        $('#btn_cancel_transfer').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Cancel Transfer?",
                text: "Are you sure you want to cancel this draft stock transfer?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#fc544b",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Cancel Transfer!",
                cancelButtonText: "Keep Draft"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#cancel_transfer_form').submit();
                }
            });
        });

        // Edit Transfer Item Qty
        $(document).on('click', '.btn-edit-item-qty', function(e) {
            e.preventDefault();
            const itemId = $(this).data('id');
            const currentQty = $(this).data('qty');
            const $row = $('#item_row_' + itemId);
            const sourceStock = parseFloat($row.data('source-stock')) || 0;
            const unit = $row.data('unit') || 'pcs';

            Swal.fire({
                title: 'Adjust Transfer Quantity',
                html: `<div class="text-left mb-2">
                        <small class="text-muted font-weight-bold">Available in Source Warehouse:</small>
                        <span class="badge ${sourceStock > 0 ? 'badge-success' : 'badge-danger'} font-weight-bold ml-1">${sourceStock.toFixed(2)} ${unit}</span>
                       </div>`,
                input: 'number',
                inputValue: currentQty,
                inputAttributes: {
                    min: '0.01',
                    step: 'any',
                    placeholder: 'Enter transfer quantity'
                },
                showCancelButton: true,
                confirmButtonColor: '#6777ef',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Update Quantity',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value || parseFloat(value) <= 0) {
                        return 'Please enter a valid positive quantity!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const newQty = parseFloat(result.value);
                    $.ajax({
                        url: "{{ url('admin/stock-transfers/' . $stockTransfer->id . '/items') }}/" + itemId,
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { qty: newQty },
                        success: function(res) {
                            if (res.success) {
                                if (window.toastr) toastr.success(res.message);
                                $row.find('.transfer-qty-display').text(parseFloat(res.qty).toFixed(2) + ' ' + unit);
                                $row.find('.btn-edit-item-qty').data('qty', res.qty);

                                if (res.is_item_sufficient) {
                                    $row.removeClass('table-danger');
                                    $row.find('.out-of-stock-badge').hide();
                                    $row.find('.source-stock-badge').removeClass('badge-danger').addClass('badge-success');
                                } else {
                                    $row.addClass('table-danger');
                                    $row.find('.out-of-stock-badge').show();
                                    $row.find('.source-stock-badge').removeClass('badge-success').addClass('badge-danger');
                                }

                                if (!res.has_insufficient_stock) {
                                    $('#insufficient_stock_alert').slideUp();
                                } else {
                                    $('#insufficient_stock_alert').slideDown();
                                }
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating quantity';
                            if (window.toastr) toastr.error(msg);
                        }
                    });
                }
            });
        });

        // Remove Item from Draft Transfer
        $(document).on('click', '.btn-remove-item', function(e) {
            e.preventDefault();
            const itemId = $(this).data('id');
            const $row = $('#item_row_' + itemId);

            Swal.fire({
                title: 'Remove this item?',
                text: 'Are you sure you want to remove this product from this draft transfer?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fc544b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Remove It!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/stock-transfers/' . $stockTransfer->id . '/items') }}/" + itemId,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                if (window.toastr) toastr.success(res.message);
                                $row.fadeOut(300, function() {
                                    $(this).remove();
                                    $('#transfer_items_count').text(res.remaining_count);

                                    // Re-index rows
                                    $('#transfer_items_tbody tr').each(function(idx) {
                                        $(this).find('.row-index').text(idx + 1);
                                    });

                                    if (!res.has_insufficient_stock) {
                                        $('#insufficient_stock_alert').slideUp();
                                    }

                                    if (res.remaining_count === 0) {
                                        location.reload();
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error removing item';
                            if (window.toastr) toastr.error(msg);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
