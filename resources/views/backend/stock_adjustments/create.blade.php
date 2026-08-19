@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>New Stock Adjustment</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-adjustments.index') }}">Stock Adjustments</a></div>
            <div class="breadcrumb-item">Create</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.stock-adjustments.store') }}" method="POST" id="adjustment_form">
            @csrf
            <div class="row">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i> Adjustment Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Warehouse / Outlet <span class="text-danger">*</span></label>
                                <select name="outlet_id" id="outlet_select" class="form-control select2" required>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->outlet_name ?? $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Adjustment Type <span class="text-danger">*</span></label>
                                <select name="adjustment_type" id="adjustment_type_select" class="form-control select2" required>
                                    <option value="decrease">Stock Decrease (Damage / Lost / Sample)</option>
                                    <option value="increase">Stock Increase (Found / Overage)</option>
                                    <option value="reconciliation">Physical Inventory Reconciliation</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Reason Code <span class="text-danger">*</span></label>
                                <select name="reason_code" class="form-control select2" required>
                                    <option value="damage">Damage Write-off</option>
                                    <option value="physical_count" selected>Physical Count Discrepancy</option>
                                    <option value="expired">Expired / Outdated</option>
                                    <option value="sample_marketing">Sample / Marketing</option>
                                    <option value="theft_loss">Theft / Loss</option>
                                    <option value="internal_use">Internal Consumption</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Notes / Justification</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Explain the reason for this adjustment..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> Adjustment Items</h4>
                            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="add_row_btn">
                                <i class="fas fa-plus mr-1"></i> Add Product Line
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0" id="items_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="min-width: 220px;">Product</th>
                                            <th style="width: 100px;">Current Stock</th>
                                            <th style="width: 110px;">Adjusted Qty <span class="text-danger">*</span></th>
                                            <th style="width: 110px;">Unit Cost</th>
                                            <th style="width: 110px;">Total Cost</th>
                                            <th style="width: 140px;">Note</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="items_body">
                                        {{-- Dynamic Rows --}}
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="4" class="text-right">Total Adjusted Value:</td>
                                            <td id="grand_total_cost" class="text-primary font-weight-bold">0.00</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top text-right py-3">
                            <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Save Adjustment (Draft)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Hidden Product Options Template --}}
<select id="product_template_select" style="display: none;">
    <option value="" disabled selected>Select Product...</option>
    @foreach($products as $p)
        @if($p->variants->isNotEmpty())
            @foreach($p->variants as $v)
                <option value="{{ $p->id }}" data-variant-id="{{ $v->id }}" data-price="{{ $p->purchase_price ?? $p->price }}">
                    {{ $p->name }} - {{ $v->color->name ?? '' }} {{ $v->size->name ?? '' }}
                </option>
            @endforeach
        @else
            <option value="{{ $p->id }}" data-variant-id="" data-price="{{ $p->purchase_price ?? $p->price }}">
                {{ $p->name }}
            </option>
        @endif
    @endforeach
</select>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        let rowIndex = 0;

        function addRow() {
            let productOptions = $('#product_template_select').html();
            let rowHtml = `
                <tr id="row_${rowIndex}">
                    <td>
                        <select name="items[${rowIndex}][product_id]" class="form-control select2-row product-select" required>
                            ${productOptions}
                        </select>
                        <input type="hidden" name="items[${rowIndex}][variant_id]" class="variant-id-input" value="">
                    </td>
                    <td>
                        <input type="text" class="form-control system-qty-input text-center" readonly value="0.00" style="background: #f8fafc;">
                    </td>
                    <td>
                        <input type="number" step="any" min="0.01" name="items[${rowIndex}][adjusted_qty]" class="form-control adjusted-qty-input text-center font-weight-bold" required placeholder="Qty">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_cost]" class="form-control unit-cost-input text-center" value="0.00">
                    </td>
                    <td>
                        <input type="text" class="form-control total-cost-input text-center font-weight-bold" readonly value="0.00" style="background: #f8fafc;">
                    </td>
                    <td>
                        <input type="text" name="items[${rowIndex}][item_note]" class="form-control" placeholder="e.g. Broken box">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#items_body').append(rowHtml);
            $('#row_' + rowIndex + ' .select2-row').select2({ width: '100%' });
            rowIndex++;
        }

        // Add initial row
        addRow();

        $('#add_row_btn').on('click', function () {
            addRow();
        });

        $(document).on('click', '.remove-row-btn', function () {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        $(document).on('change', '.product-select', function () {
            let row = $(this).closest('tr');
            let selectedOption = $(this).find('option:selected');
            let productId = selectedOption.val();
            let variantId = selectedOption.data('variant-id') || '';
            let outletId = $('#outlet_select').val();

            row.find('.variant-id-input').val(variantId);

            if (productId) {
                $.ajax({
                    url: "{{ route('admin.stock-adjustments.get-item-stock') }}",
                    method: "GET",
                    data: {
                        product_id: productId,
                        variant_id: variantId,
                        outlet_id: outletId
                    },
                    success: function (res) {
                        if (res.success) {
                            row.find('.system-qty-input').val(parseFloat(res.system_qty).toFixed(2));
                            row.find('.unit-cost-input').val(parseFloat(res.unit_cost).toFixed(2));
                            recalculateRow(row);
                        }
                    }
                });
            }
        });

        $(document).on('input', '.adjusted-qty-input, .unit-cost-input', function () {
            let row = $(this).closest('tr');
            recalculateRow(row);
        });

        function recalculateRow(row) {
            let qty = parseFloat(row.find('.adjusted-qty-input').val()) || 0;
            let cost = parseFloat(row.find('.unit-cost-input').val()) || 0;
            let total = qty * cost;
            row.find('.total-cost-input').val(total.toFixed(2));
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            $('.total-cost-input').each(function () {
                grandTotal += parseFloat($(this).val()) || 0;
            });
            $('#grand_total_cost').text(grandTotal.toFixed(2));
        }
    });
</script>
@endpush
