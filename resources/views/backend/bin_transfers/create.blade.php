@extends('backend.layouts.master')
@section('title', 'Inter-Bin Relocation (Internal Stock Movement)')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Inter-Bin Relocation (Internal Stock Movement)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.warehouse-bins.index') }}">Warehouse Bins</a></div>
            <div class="breadcrumb-item">Relocate Stock</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card card-primary shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="text-dark font-weight-bold mb-0">
                            <i class="fas fa-exchange-alt text-primary mr-2"></i> Relocate Stock Between Warehouse Bins
                        </h4>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-primary btn-sm active" id="btn-mode-single">
                                <input type="radio" name="mode_toggle" id="mode_single" checked> Single Product Item
                            </label>
                            <label class="btn btn-outline-warning btn-sm" id="btn-mode-full">
                                <input type="radio" name="mode_toggle" id="mode_full"> Move Entire Bin (100% All Stock)
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.bin-transfers.store') }}" method="POST" id="bin-transfer-form">
                            @csrf
                            <input type="hidden" name="transfer_mode" id="transfer_mode" value="single">

                            <!-- Mode Alert Banner -->
                            <div class="alert alert-info py-2 mb-4 d-flex align-items-center" id="mode-alert-banner">
                                <i class="fas fa-info-circle fa-lg mr-2"></i>
                                <span id="mode-alert-text"><strong>Single Item Mode:</strong> Transfer specific product item or partial quantity between bins.</span>
                            </div>

                            <!-- Step 1: Select Outlet & Source Location -->
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="outlet_id" class="font-weight-bold text-dark">
                                        Warehouse / Outlet <span class="text-danger">*</span>
                                    </label>
                                    <select name="outlet_id" id="outlet_id" class="form-control select2" required>
                                        <option value="">-- Select Warehouse Outlet --</option>
                                        @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="source_bin_id" class="font-weight-bold text-dark">
                                        <i class="fas fa-upload text-warning mr-1"></i> Source Bin / Origin Location <span class="text-danger">*</span>
                                    </label>
                                    <select name="source_bin_id" id="source_bin_id" class="form-control select2">
                                        <option value="">-- Select Origin Bin (or Unassigned Stock) --</option>
                                        @foreach($bins as $bin)
                                            <option value="{{ $bin->id }}" data-outlet="{{ $bin->zone?->outlet_id }}">
                                                {{ $bin->name }} ({{ $bin->barcode }} — {{ $bin->zone?->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- Step 2: Product & Destination Selection -->
                            <div class="row">
                                <div class="col-md-6 form-group" id="product-select-group">
                                    <label for="product_select" class="font-weight-bold text-dark">
                                        Select Product to Move <span class="text-danger">*</span>
                                    </label>
                                    <select name="product_id" id="product_select" class="form-control select2" required disabled>
                                        <option value="">-- First Select Outlet/Source Bin --</option>
                                    </select>
                                    <input type="hidden" name="variant_id" id="variant_id" value="">
                                </div>

                                <div class="col-md-6 form-group" id="dest-bin-group">
                                    <label for="destination_bin_id" class="font-weight-bold text-dark">
                                        <i class="fas fa-download text-success mr-1"></i> Destination Target Bin <span class="text-danger">*</span>
                                    </label>
                                    <select name="destination_bin_id" id="destination_bin_id" class="form-control select2" required>
                                        <option value="">-- Select Target Bin --</option>
                                        @foreach($bins as $bin)
                                            <option value="{{ $bin->id }}" data-outlet="{{ $bin->zone?->outlet_id }}">
                                                {{ $bin->name }} ({{ $bin->barcode }} — {{ $bin->zone?->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Step 3: Quantity & Stock Info Alert -->
                            <div class="row" id="quantity-group">
                                <div class="col-md-6 form-group">
                                    <label for="quantity" class="font-weight-bold text-dark">
                                        Quantity to Relocate <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="quantity" id="quantity" step="1" min="1" class="form-control font-weight-bold text-dark" placeholder="e.g. 10" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info font-weight-bold" id="btn-select-all-qty" title="Move 100% of this product">
                                                <i class="fas fa-check-double mr-1"></i> Select All Qty
                                            </button>
                                        </div>
                                    </div>
                                    <small id="available-stock-msg" class="form-text text-muted"></small>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="notes" class="font-weight-bold text-dark">Relocation Notes / Reason</label>
                                    <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Moved for rack optimization / Full bin relocation">
                                </div>
                            </div>

                            <!-- Live Bin Preview Card for Full Bin Transfer -->
                            <div class="card border-warning bg-light d-none" id="full-bin-preview-card">
                                <div class="card-body py-3">
                                    <h6 class="font-weight-bold text-warning mb-2">
                                        <i class="fas fa-cubes mr-1"></i> Bin Contents Preview (100% Move)
                                    </h6>
                                    <div id="full-bin-contents-list">
                                        <p class="text-muted mb-0">Select Source Bin to preview all items being transferred.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light border mt-3">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center text-dark">
                                        <i class="fas fa-shield-alt text-success fa-2x mr-3"></i>
                                        <div>
                                            <strong class="d-block">Enterprise Audit Guarantee</strong>
                                            <small class="text-muted">Moving items between Bins automatically updates real-time stock balances, batch locations, and immutable Stock Ledger relocation logs.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-secondary font-weight-bold mr-2">Cancel</a>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btn-submit-relocation">
                                    <i class="fas fa-check-circle mr-1"></i> Confirm & Relocate Stock
                                </button>
                            </div>
                        </form>
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
        var currentMode = 'single';

        // Toggle between Single Item Mode and Move Entire Bin Mode
        $('#btn-mode-single').on('click', function() {
            currentMode = 'single';
            $('#transfer_mode').val('single');
            $('#mode-alert-text').html('<strong>Single Item Mode:</strong> Transfer specific product item or partial quantity between bins.');
            $('#product-select-group').removeClass('d-none');
            $('#quantity-group').removeClass('d-none');
            $('#full-bin-preview-card').addClass('d-none');
            $('#product_select').prop('required', true);
            $('#quantity').prop('required', true);
            $('#btn-submit-relocation').html('<i class="fas fa-check-circle mr-1"></i> Confirm & Relocate Item');
        });

        $('#btn-mode-full').on('click', function() {
            currentMode = 'full';
            $('#transfer_mode').val('full');
            $('#mode-alert-text').html('<strong>Full Bin Transfer Mode:</strong> Move 100% of ALL products & batches currently in the Source Bin into Destination Bin in 1-click.');
            $('#product-select-group').addClass('d-none');
            $('#quantity-group').addClass('d-none');
            $('#full-bin-preview-card').removeClass('d-none');
            $('#product_select').prop('required', false);
            $('#quantity').prop('required', false);
            $('#btn-submit-relocation').html('<i class="fas fa-boxes mr-1"></i> Move Entire Bin Contents (100%)');
            loadBinContentsPreview();
        });

        // Load products based on selected Outlet and Source Bin
        $('#outlet_id, #source_bin_id').on('change', function() {
            var outletId = $('#outlet_id').val();
            var binId = $('#source_bin_id').val();
            var $productSelect = $('#product_select');

            $productSelect.empty().append('<option value="">Loading available stock...</option>').prop('disabled', true);
            $('#available-stock-msg').text('');

            if (!outletId) {
                $productSelect.empty().append('<option value="">-- First Select Outlet/Source Bin --</option>');
                return;
            }

            $.ajax({
                url: "{{ route('admin.bin-transfers.get-bin-products') }}",
                type: "GET",
                data: {
                    outlet_id: outletId,
                    bin_id: binId
                },
                success: function(stocks) {
                    $productSelect.empty().append('<option value="">-- Select Product from Available Stock --</option>');
                    if (stocks.length === 0) {
                        $productSelect.append('<option value="" disabled>No available stock in this location</option>');
                        $('#full-bin-contents-list').html('<p class="text-danger mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Source Bin is currently empty.</p>');
                    } else {
                        var previewHtml = '<ul class="list-group list-group-flush small">';
                        stocks.forEach(function(stock) {
                            var pName = stock.product ? stock.product.name : 'Unknown Product';
                            var vName = stock.variant ? (' - ' + stock.variant.name) : '';
                            var label = pName + vName + ' (Available: ' + parseFloat(stock.quantity).toFixed(0) + ' Pcs)';
                            
                            $productSelect.append(
                                $('<option></option>')
                                    .attr('value', stock.product_id)
                                    .attr('data-variant', stock.variant_id || '')
                                    .attr('data-max', stock.quantity)
                                    .text(label)
                            );

                            previewHtml += '<li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-1 px-0">' +
                                '<span><strong>' + pName + '</strong>' + vName + '</span>' +
                                '<span class="badge badge-warning font-weight-bold">' + parseFloat(stock.quantity).toFixed(0) + ' Pcs</span>' +
                                '</li>';
                        });
                        previewHtml += '</ul>';
                        $('#full-bin-contents-list').html(previewHtml);
                        $productSelect.prop('disabled', false);
                    }
                },
                error: function() {
                    $productSelect.empty().append('<option value="">Error loading products</option>');
                }
            });
        });

        $('#product_select').on('change', function() {
            var $selected = $(this).find(':selected');
            var variantId = $selected.data('variant') || '';
            var maxQty = parseFloat($selected.data('max')) || 0;

            $('#variant_id').val(variantId);
            if (maxQty > 0) {
                $('#quantity').attr('max', maxQty);
                $('#available-stock-msg').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Maximum available: ' + maxQty + ' Pcs</span>');
            } else {
                $('#available-stock-msg').text('');
            }
        });

        // 1-Click "Select All Qty" button handler
        $('#btn-select-all-qty').on('click', function() {
            var $selected = $('#product_select').find(':selected');
            var maxQty = parseFloat($selected.data('max')) || 0;
            if (maxQty > 0) {
                $('#quantity').val(maxQty);
            }
        });

        function loadBinContentsPreview() {
            $('#source_bin_id').trigger('change');
        }
    });
</script>
@endpush
