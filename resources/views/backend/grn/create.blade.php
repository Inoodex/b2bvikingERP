@extends('backend.layouts.master')

@section('title', 'Receive Goods & Quality Control (GRN)')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.goods-receipts.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Receive Goods & Quality Control (GRN)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.goods-receipts.index') }}">GRN</a></div>
            <div class="breadcrumb-item">Receive Goods</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Step 1: Select Purchase Order Card -->
        <div class="card card-primary shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-search text-primary mr-2"></i> Step 1: Select Purchase Order to Receive</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.goods-receipts.create') }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-9 mb-2 mb-md-0">
                            <label for="purchase_id" class="font-weight-bold text-dark mb-1">Pending / Partial Purchase Orders (PO)</label>
                            <select name="purchase_id" id="purchase_id" class="form-control select2" style="width: 100%;" onchange="this.form.submit()">
                                <option value="">-- Select Approved PO to Receive Goods --</option>
                                @foreach($purchases as $po)
                                    <option value="{{ $po->id }}" {{ isset($purchase) && $purchase->id == $po->id ? 'selected' : '' }}>
                                        {{ $po->po_no ?? 'PO #'.$po->id }} — {{ $po->vendor?->name ?? $po->vendor?->shop_name }} (Status: {{ ucfirst(str_replace('_', ' ', $po->milestone_status)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 text-md-right mt-3 mt-md-4">
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold"><i class="fas fa-boxes mr-1"></i> Load Line Items</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($purchase)
        <form action="{{ route('admin.goods-receipts.store') }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

            <!-- PO Overview Summary -->
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 text-dark font-weight-bold"><i class="fas fa-file-invoice text-info mr-2"></i> PO Reference: {{ $purchase->po_no ?? 'PO #'.$purchase->id }}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center text-md-left">
                        <div class="col-md-3 mb-2 mb-md-0 border-right">
                            <small class="text-muted font-weight-bold text-uppercase d-block">Supplier / Vendor</small>
                            <strong class="text-dark font-weight-bold">{{ $purchase->vendor?->name ?? $purchase->vendor?->shop_name ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0 border-right">
                            <small class="text-muted font-weight-bold text-uppercase d-block">Purchase Type</small>
                            <span class="badge badge-info font-weight-bold mt-1"><i class="fas fa-globe mr-1"></i> {{ strtoupper($purchase->purchase_type ?? 'LOCAL') }}</span>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0 border-right">
                            <small class="text-muted font-weight-bold text-uppercase d-block">PO Milestone</small>
                            <span class="badge badge-warning font-weight-bold mt-1"><i class="fas fa-clock mr-1"></i> {{ ucfirst(str_replace('_', ' ', $purchase->milestone_status)) }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted font-weight-bold text-uppercase d-block">PO Total Amount</small>
                            <strong class="text-success font-weight-bold" style="font-size: 16px;">
                                {{ $purchase->vendor?->currency?->symbol ?? $purchase->currency?->symbol ?? 'kr.' }} {{ number_format($purchase->foreign_amount ?? $purchase->total_amount, 2) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Quality Control & Receiving Form -->
            <div class="card card-success shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-clipboard-check text-success mr-2"></i> Step 2: Quality Control & Warehouse Receiving</h4>
                    <span class="badge badge-success font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> QC Inspection Active</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="receipt_date" class="font-weight-bold text-dark">Receiving Gate Date <span class="text-danger">*</span></label>
                            <input type="date" name="receipt_date" id="receipt_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="qc_status" class="font-weight-bold text-dark">Quality Control (QC) Decision <span class="text-danger">*</span></label>
                            <select name="qc_status" id="qc_status" class="form-control select2" required>
                                <option value="passed" selected>🟢 Passed (100% Quality Verified)</option>
                                <option value="conditionally_accepted">🟡 Conditionally Accepted (Partial Damage)</option>
                                <option value="failed">🔴 Failed / Rejected (Lot Damaged)</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="outlet_id" class="font-weight-bold text-dark">Receiving Outlet / Warehouse <span class="text-danger">*</span></label>
                            <select name="outlet_id" id="outlet_id" class="form-control select2" required>
                                <option value="">-- Select Destination Outlet --</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="remarks" class="font-weight-bold text-dark">Receiving Notes / Truck Gate Pass Info</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Delivered via Cargo Truck #DHAKA-METRO-11-2094">
                        </div>
                    </div>

                    <!-- Quality Control (QC) Inspection Verification Checklist -->
                    <div class="card bg-light border-primary mt-2 mb-3">
                        <div class="card-body py-3">
                            <h6 class="font-weight-bold text-primary mb-2">
                                <i class="fas fa-microscope mr-1"></i> Quality Control (QC) Inspection Checklist (Verify before receiving)
                            </h6>
                            <div class="row text-dark">
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="qc_physical_check" value="1" class="custom-control-input" id="qc_chk_physical" checked>
                                        <label class="custom-control-label font-weight-600" for="qc_chk_physical">
                                            <i class="fas fa-shield-alt text-success mr-1"></i> Physical Damage Check Passed
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="qc_spec_check" value="1" class="custom-control-input" id="qc_chk_spec" checked>
                                        <label class="custom-control-label font-weight-600" for="qc_chk_spec">
                                            <i class="fas fa-tasks text-success mr-1"></i> Specification & Brand Verified
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="qc_doc_check" value="1" class="custom-control-input" id="qc_chk_doc" checked>
                                        <label class="custom-control-label font-weight-600" for="qc_chk_doc">
                                            <i class="fas fa-file-invoice text-success mr-1"></i> Invoice & Waybill Verified
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover" id="grn-items-table">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th class="text-center" width="3%">#</th>
                                    <th width="24%">Product Description</th>
                                    <th width="10%">Variant</th>
                                    <th class="text-right" width="10%">PO Qty</th>
                                    <th class="text-right" width="10%">Remaining</th>
                                    <th class="text-right" width="11%">Total Delivered <span class="text-danger">*</span></th>
                                    <th class="text-right" width="11%">Accepted Qty <span class="text-danger">*</span></th>
                                    <th class="text-right" width="11%">Rejected Qty</th>
                                    <th width="10%">Rejection Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $index => $item)
                                    @php
                                        $remaining = $remainingQtyMap[$item->id] ?? (float)$item->qty;
                                    @endphp
                                    <tr class="{{ $remaining <= 0 ? 'bg-light text-muted' : '' }}">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <strong class="text-dark">{{ $item->product?->name }}</strong>
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="items[{{ $index }}][variant_id]" value="{{ $item->variant_id }}">
                                        </td>
                                        <td>{{ $item->variant?->name ?? 'N/A' }}</td>
                                        <td class="text-right font-weight-bold text-muted">{{ number_format($item->qty, 2) }}</td>
                                        <td class="text-right font-weight-bold {{ $remaining > 0 ? 'text-primary' : 'text-success' }}">
                                            @if($remaining > 0)
                                                {{ number_format($remaining, 2) }}
                                            @else
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Fully Received</span>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" 
                                                name="items[{{ $index }}][received_qty]" 
                                                value="{{ $remaining > 0 ? $remaining : 0 }}" 
                                                class="form-control form-control-sm text-right font-weight-bold text-dark received-input" 
                                                {{ $remaining <= 0 ? 'disabled' : 'required' }}>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="{{ $remaining }}" 
                                                name="items[{{ $index }}][accepted_qty]" 
                                                value="{{ $remaining > 0 ? $remaining : 0 }}" 
                                                class="form-control form-control-sm text-right font-weight-bold text-success accepted-input" 
                                                {{ $remaining <= 0 ? 'disabled' : 'required' }}>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="{{ $remaining }}" 
                                                name="items[{{ $index }}][rejected_qty]" 
                                                value="0" 
                                                class="form-control form-control-sm text-right font-weight-bold text-danger rejected-input" 
                                                {{ $remaining <= 0 ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][rejection_reason]" 
                                                class="form-control form-control-sm" placeholder="Reason if rejected">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                        <a href="{{ route('admin.goods-receipts.index') }}" class="btn btn-secondary font-weight-bold mr-2">Cancel</a>
                        <button type="submit" class="btn btn-success font-weight-bold px-4"><i class="fas fa-check-circle mr-1"></i> Submit GRN & Update Inventory</button>
                    </div>
                </div>
            </div>
        </form>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Line Item Quantity Calculation
        $('#grn-items-table').on('input', '.accepted-input, .rejected-input', function() {
            var $row = $(this).closest('tr');
            var accepted = parseFloat($row.find('.accepted-input').val()) || 0;
            var rejected = parseFloat($row.find('.rejected-input').val()) || 0;
            var total = accepted + rejected;
            $row.find('.received-input').val(total.toFixed(2));
        });

        // 2. Interactive QC Checkboxes <-> QC Decision Sync
        $('#qc_chk_physical, #qc_chk_spec, #qc_chk_doc').on('change', function() {
            var physical = $('#qc_chk_physical').is(':checked');
            var spec = $('#qc_chk_spec').is(':checked');
            var doc = $('#qc_chk_doc').is(':checked');

            if (physical && spec && doc) {
                $('#qc_status').val('passed').trigger('change');
            } else if (!physical && !spec && !doc) {
                $('#qc_status').val('failed').trigger('change');
            } else {
                $('#qc_status').val('conditionally_accepted').trigger('change');
            }
        });

        $('#qc_status').on('change', function() {
            var val = $(this).val();
            if (val === 'passed') {
                $('#qc_chk_physical, #qc_chk_spec, #qc_chk_doc').prop('checked', true);
            } else if (val === 'failed') {
                $('#qc_chk_physical, #qc_chk_spec, #qc_chk_doc').prop('checked', false);
            }
        });
    });
</script>
@endpush
