@extends('backend.layouts.master')

@push('css')
<style>
    .executive-header-bar {
        border-bottom: 1px solid #e2e8f0;
        padding-top: 14px;
        padding-bottom: 18px;
        margin-bottom: 22px;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="executive-header-bar d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
        <div>
            <h1 class="font-weight-bold text-dark mb-1" style="font-size: 24px; letter-spacing: -0.025em; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;">
                Issue Supplies to Department (Stock Out)
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Sequential Issue Voucher <span class="badge badge-light border text-dark font-weight-bold ml-1">{{ $nextIssueNo }}</span> with real-time negative-stock protection.
            </p>
        </div>
        <a href="{{ route('admin.stationery-supplies.consumptions') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #0f172a; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #0f172a;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Issues
        </a>
    </div>

    <div class="section-body">
        <form method="POST" action="{{ route('admin.stationery-supplies.consumptions.store') }}" id="consumptionForm">
            @csrf
            {{-- Header Details Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 14px;"><i class="fas fa-clipboard-check mr-2 text-primary"></i>Department Issue Voucher</h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold text-dark">Issue Date <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                        </div>

                        <div class="form-group col-md-4">
                            <label class="font-weight-bold text-dark">Department / Cost Center <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_select" class="form-control" style="border-radius: 8px;">
                                <option value="">-- Choose Consuming Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ str_contains(strtolower($dept->name), 'warehouse') ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label class="font-weight-bold text-dark">Other Department / Custom</label>
                            <input type="text" name="department_name" class="form-control" placeholder="e.g. Packing Table 2, Sample Room" style="border-radius: 8px;">
                        </div>

                        <div class="form-group col-md-2">
                            <label class="font-weight-bold text-dark">Issued To / Receiver</label>
                            <input type="text" name="issued_to" class="form-control" placeholder="Staff / Operator Name" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Purpose / Batch Work Order</label>
                        <input type="text" name="purpose_notes" class="form-control" placeholder="e.g. Packing cartons for Danish buyer shipment #8921" style="border-radius: 8px;">
                    </div>
                </div>
            </div>

            {{-- Multi-item Issue Lines Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 14px;">
                        <i class="fas fa-boxes mr-2 text-warning"></i>Supplies to Issue
                    </h6>
                    <button type="button" class="btn btn-sm font-weight-bold shadow-sm" id="add-row-btn" style="background: #d97706; color: #fff; border-radius: 6px;">
                        <i class="fas fa-plus mr-1"></i> Add Supply Line
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle" id="itemsTable">
                            <thead class="bg-light text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px; color: #64748b;">
                                <tr>
                                    <th style="min-width: 300px;">Supply Item <span class="text-danger">*</span></th>
                                    <th class="text-center" style="width: 160px;">Available Stock</th>
                                    <th class="text-center" style="width: 160px;">Quantity to Issue <span class="text-danger">*</span></th>
                                    <th class="text-right" style="width: 160px;">Unit Cost (WAC)</th>
                                    <th class="text-right" style="width: 160px;">Expense Total</th>
                                    <th class="text-center" style="width: 60px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row" data-index="0">
                                    <td>
                                        <select name="items[0][stationery_item_id]" class="form-control item-select" required style="border-radius: 8px;">
                                            <option value="">-- Choose In-Stock Supply --</option>
                                            @foreach($items as $sItem)
                                                <option value="{{ $sItem->id }}" 
                                                    data-stock="{{ $sItem->current_stock }}" 
                                                    data-cost="{{ $sItem->unit_cost }}" 
                                                    data-unit="{{ $sItem->unit_name }}">
                                                    {{ $sItem->name }} ({{ $sItem->item_code }}) [Available: {{ number_format($sItem->current_stock) }} {{ $sItem->unit_name }}]
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="stock-badge badge badge-success px-2 py-1 font-weight-bold">-</span>
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0.01" name="items[0][quantity]" class="form-control text-center qty-input" placeholder="Qty" required style="border-radius: 8px;">
                                        <small class="error-msg text-danger font-weight-bold d-none" style="font-size: 10.5px;"></small>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-dark unit-cost-display">
                                        0.00
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-danger line-total">
                                        0.00
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" disabled style="border-radius: 6px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="4" class="text-right font-weight-bold text-dark text-uppercase" style="font-size: 12.5px;">
                                        Total Consumption Expense ({{ $settings->currency_icon ?? 'DKK' }}):
                                    </th>
                                    <th class="text-right font-weight-bold text-danger" style="font-size: 16px;" id="grandTotal">
                                        0.00
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 12px;">
                        <i class="fas fa-shield-alt mr-1 text-success"></i> 
                        Strict Safety Guard: The system prevents issues that exceed available physical stock.
                    </span>
                    <div>
                        <a href="{{ route('admin.stationery-supplies.consumptions') }}" class="btn btn-light border mr-2 font-weight-bold" style="border-radius: 8px;">Cancel</a>
                        <button type="submit" class="btn font-weight-bold shadow-sm" id="submitBtn" style="background: #d97706; color: #fff; border-radius: 8px; padding: 7px 20px;">
                            <i class="fas fa-check-circle mr-1"></i> Issue & Deduct Stock
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var rowIndex = 1;

        var itemOptionsHtml = `
            <option value="">-- Choose In-Stock Supply --</option>
            @foreach($items as $sItem)
                <option value="{{ $sItem->id }}" 
                    data-stock="{{ $sItem->current_stock }}" 
                    data-cost="{{ $sItem->unit_cost }}" 
                    data-unit="{{ $sItem->unit_name }}">
                    {{ $sItem->name }} ({{ $sItem->item_code }}) [Available: {{ number_format($sItem->current_stock) }} {{ $sItem->unit_name }}]
                </option>
            @endforeach
        `;

        function recalculate() {
            var grandTotal = 0;
            var hasStockError = false;

            $('#itemsBody tr').each(function() {
                var $row = $(this);
                var $selected = $row.find('.item-select option:selected');
                var availableStock = parseFloat($selected.data('stock')) || 0;
                var cost = parseFloat($selected.data('cost')) || 0;
                var qty = parseFloat($row.find('.qty-input').val()) || 0;

                var subtotal = qty * cost;
                $row.find('.line-total').text(subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                grandTotal += subtotal;

                // Validate stock limit
                if (qty > availableStock && availableStock > 0) {
                    $row.find('.error-msg').text('Max: ' + availableStock).removeClass('d-none');
                    $row.find('.qty-input').addClass('is-invalid');
                    hasStockError = true;
                } else {
                    $row.find('.error-msg').addClass('d-none');
                    $row.find('.qty-input').removeClass('is-invalid');
                }
            });

            $('#grandTotal').text(grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            if (hasStockError) {
                $('#submitBtn').prop('disabled', true).css('opacity', '0.6');
            } else {
                $('#submitBtn').prop('disabled', false).css('opacity', '1');
            }
        }

        $('#add-row-btn').on('click', function() {
            var newRow = `
                <tr class="item-row" data-index="${rowIndex}">
                    <td>
                        <select name="items[${rowIndex}][stationery_item_id]" class="form-control item-select" required style="border-radius: 8px;">
                            ${itemOptionsHtml}
                        </select>
                    </td>
                    <td class="text-center align-middle">
                        <span class="stock-badge badge badge-success px-2 py-1 font-weight-bold">-</span>
                    </td>
                    <td>
                        <input type="number" step="any" min="0.01" name="items[${rowIndex}][quantity]" class="form-control text-center qty-input" placeholder="Qty" required style="border-radius: 8px;">
                        <small class="error-msg text-danger font-weight-bold d-none" style="font-size: 10.5px;"></small>
                    </td>
                    <td class="text-right align-middle font-weight-bold text-dark unit-cost-display">
                        0.00
                    </td>
                    <td class="text-right align-middle font-weight-bold text-danger line-total">
                        0.00
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" style="border-radius: 6px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#itemsBody').append(newRow);
            rowIndex++;
            updateRemoveButtons();
        });

        $(document).on('click', '.remove-row-btn', function() {
            if ($('#itemsBody tr').length > 1) {
                $(this).closest('tr').remove();
                recalculate();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            var rows = $('#itemsBody tr');
            if (rows.length === 1) {
                rows.find('.remove-row-btn').prop('disabled', true);
            } else {
                rows.find('.remove-row-btn').prop('disabled', false);
            }
        }

        $(document).on('change', '.item-select', function() {
            var $row = $(this).closest('tr');
            var $selected = $(this).find('option:selected');
            var stock = $selected.data('stock');
            var unit = $selected.data('unit') || 'pcs';
            var cost = $selected.data('cost');

            if (stock !== undefined) {
                $row.find('.stock-badge').text(Number(stock).toLocaleString() + ' ' + unit);
                $row.find('.unit-cost-display').text((parseFloat(cost) || 0).toFixed(2));
                $row.find('.qty-input').attr('max', stock);
            } else {
                $row.find('.stock-badge').text('-');
                $row.find('.unit-cost-display').text('0.00');
            }
            recalculate();
        });

        $(document).on('input change', '.qty-input', function() {
            recalculate();
        });
    });
</script>
@endpush
