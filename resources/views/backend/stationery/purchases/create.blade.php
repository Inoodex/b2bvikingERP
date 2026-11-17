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
                <h1 class="font-weight-bold text-dark mb-1"
                    style="font-size: 24px; letter-spacing: -0.025em; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;">
                    Record Supplies Purchase (Stock In)
                </h1>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Sequential Inward PO <span
                        class="badge badge-light border text-dark font-weight-bold ml-1">{{ $nextPurchaseNo }}</span> with
                    automated Weighted Average Cost (WAC) recalculation.
                </p>
            </div>
            <a href="{{ route('admin.stationery-supplies.purchases') }}"
                class="btn font-weight-bold d-inline-flex align-items-center shadow-sm"
                style="background: #0f172a; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #0f172a;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Purchases
            </a>
        </div>

        <div class="section-body">
            <form method="POST" action="{{ route('admin.stationery-supplies.purchases.store') }}" id="purchaseForm">
                @csrf
                {{-- Header Details Card --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 14px;"><i
                                class="fas fa-file-invoice mr-2 text-primary"></i>Purchase Inward Voucher</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold text-dark">Purchase Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}"
                                    required style="border-radius: 8px;">
                            </div>

                            <div class="form-group col-md-5">
                                <label class="font-weight-bold text-dark">Purchased From / Packaging Shop <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px;">
                                            <i class="fas fa-store text-muted"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="vendor_name_custom" list="recent_shops_list" class="form-control border-left-0"
                                        placeholder="e.g. Local Packaging Market, Dansk Tape & Emballage, Box Supplier"
                                        value="{{ old('vendor_name_custom', 'Local Packaging Market') }}" required style="border-radius: 0 8px 8px 0;">
                                    <datalist id="recent_shops_list">
                                        @foreach($recentShops as $shop)
                                            <option value="{{ $shop }}">
                                        @endforeach
                                        <option value="Local Packaging Market">
                                        <option value="Dansk Tape & Emballage">
                                        <option value="Direct Cash Purchase">
                                    </datalist>
                                </div>
                                <small class="form-text text-muted">Enter local shop, packaging supplier, or wholesale vendor name.</small>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="font-weight-bold text-dark">Invoice / Cash Memo #</label>
                                <input type="text" name="invoice_no" class="form-control" placeholder="e.g. MEMO-8801, INV-0982"
                                    style="border-radius: 8px;">
                                <small class="form-text text-muted">Supplier bill, receipt, or cash memo reference (optional).</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4 mb-0">
                                <label class="font-weight-bold text-dark">Payment Status <span class="text-danger">*</span></label>
                                <select name="payment_status" id="payment_status_select" class="form-control font-weight-bold" style="border-radius: 8px;">
                                    <option value="paid" selected>&#9679; Paid in Full</option>
                                    <option value="unpaid">&#9679; Unpaid / On Credit</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4 mb-0" id="paid_account_wrapper">
                                <label class="font-weight-bold text-dark">Paid From Account <span class="text-danger">*</span></label>
                                <select name="payment_account_code" id="payment_account_select" class="form-control" style="border-radius: 8px;">
                                    @foreach($paymentAccounts as $pAcc)
                                        <option value="{{ $pAcc->account_code }}" {{ $pAcc->account_code === '1010' ? 'selected' : '' }}>
                                            {{ $pAcc->account_code }} — {{ $pAcc->account_name }} ({{ ucfirst($pAcc->account_type) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted" id="paid_account_help">Select company cash or bank account used for payment.</small>
                            </div>

                            <div class="form-group col-md-4 mb-0" id="credit_purchase_notice" style="display: none;">
                                <label class="font-weight-bold text-danger">Liability Note</label>
                                <div class="p-2 rounded bg-light border border-danger text-danger small">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Credit purchase. Automatically recorded under <strong>[2010: Accounts Payable]</strong> liability without cash/bank deduction.
                                </div>
                            </div>

                            <div class="form-group col-md-4 mb-0">
                                <label class="font-weight-bold text-dark">Purchase Notes / Reference</label>
                                <input type="text" name="notes" class="form-control"
                                    placeholder="Optional notes, memo reference, etc." style="border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Multi-item Purchase Lines Card --}}
                <div class="card border-0 shadow-sm mb-4"
                    style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 14px;">
                            <i class="fas fa-boxes mr-2 text-success"></i>Purchased Supply Items
                        </h6>
                        <button type="button" class="btn btn-sm font-weight-bold shadow-sm" id="add-row-btn"
                            style="background: #059669; color: #fff; border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> Add Item Line
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle" id="itemsTable">
                                <thead class="bg-light text-uppercase"
                                    style="font-size: 10.5px; letter-spacing: 0.5px; color: #64748b;">
                                    <tr>
                                        <th style="min-width: 280px;">Supply Item <span class="text-danger">*</span></th>
                                        <th class="text-center" style="width: 140px;">Current Stock</th>
                                        <th class="text-center" style="width: 150px;">Inward Quantity <span
                                                class="text-danger">*</span></th>
                                        <th class="text-right" style="width: 170px;">Unit Price
                                            ({{ $settings->currency_icon ?? 'DKK' }}) <span class="text-danger">*</span>
                                        </th>
                                        <th class="text-right" style="width: 160px;">Line Total</th>
                                        <th class="text-center" style="width: 60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row" data-index="0">
                                        <td>
                                            <select name="items[0][stationery_item_id]" class="form-control item-select"
                                                required style="border-radius: 8px;">
                                                <option value="">-- Choose Stationery Item --</option>
                                                @foreach ($items as $sItem)
                                                    <option value="{{ $sItem->id }}"
                                                        data-stock="{{ $sItem->current_stock }}"
                                                        data-cost="{{ $sItem->unit_cost }}"
                                                        data-unit="{{ $sItem->unit_name }}">
                                                        {{ $sItem->name }} ({{ $sItem->item_code }})
                                                        [{{ $sItem->unit_name }}]
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="stock-badge font-weight-bold text-muted">-</span>
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0.01"
                                                name="items[0][quantity]" class="form-control text-center qty-input"
                                                placeholder="Qty" required style="border-radius: 8px;">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text py-0"
                                                        style="font-size: 11px;">{{ $settings->currency_icon ?? 'DKK' }}</span>
                                                </div>
                                                <input type="number" step="0.01" min="0"
                                                    name="items[0][unit_price]"
                                                    class="form-control text-right price-input" placeholder="0.00"
                                                    required style="border-radius: 8px;">
                                            </div>
                                        </td>
                                        <td class="text-right align-middle font-weight-bold text-success line-total">
                                            0.00
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"
                                                disabled style="border-radius: 6px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="4" class="text-right font-weight-bold text-dark text-uppercase"
                                            style="font-size: 12.5px;">
                                            Total Purchase Amount ({{ $settings->currency_icon ?? 'DKK' }}):
                                        </th>
                                        <th class="text-right font-weight-bold text-success" style="font-size: 16px;"
                                            id="grandTotal">
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
                            <i class="fas fa-info-circle mr-1 text-primary"></i>
                            Submitting this voucher automatically updates physical stock and recalculates Weighted Average
                            Cost.
                        </span>
                        <div>
                            <a href="{{ route('admin.stationery-supplies.purchases') }}"
                                class="btn btn-light border mr-2 font-weight-bold" style="border-radius: 8px;">Cancel</a>
                            <button type="submit" class="btn font-weight-bold shadow-sm"
                                style="background: #059669; color: #fff; border-radius: 8px; padding: 7px 20px;">
                                <i class="fas fa-check-circle mr-1"></i> Save & Inward Stock
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
            <option value="">-- Choose Stationery Item --</option>
            @foreach ($items as $sItem)
                <option value="{{ $sItem->id }}" 
                    data-stock="{{ $sItem->current_stock }}" 
                    data-cost="{{ $sItem->unit_cost }}" 
                    data-unit="{{ $sItem->unit_name }}">
                    {{ $sItem->name }} ({{ $sItem->item_code }}) [{{ $sItem->unit_name }}]
                </option>
            @endforeach
        `;

            function recalculate() {
                var grandTotal = 0;
                $('#itemsBody tr').each(function() {
                    var $row = $(this);
                    var qty = parseFloat($row.find('.qty-input').val()) || 0;
                    var price = parseFloat($row.find('.price-input').val()) || 0;
                    var subtotal = qty * price;
                    $row.find('.line-total').text(subtotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    grandTotal += subtotal;
                });

                $('#grandTotal').text(grandTotal.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
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
                        <span class="stock-badge font-weight-bold text-muted">-</span>
                    </td>
                    <td>
                        <input type="number" step="any" min="0.01" name="items[${rowIndex}][quantity]" class="form-control text-center qty-input" placeholder="Qty" required style="border-radius: 8px;">
                    </td>
                    <td>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text py-0" style="font-size: 11px;">{{ $settings->currency_icon ?? 'DKK' }}</span></div>
                            <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" class="form-control text-right price-input" placeholder="0.00" required style="border-radius: 8px;">
                        </div>
                    </td>
                    <td class="text-right align-middle font-weight-bold text-success line-total">
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
                    if (!$row.find('.price-input').val() && cost > 0) {
                        $row.find('.price-input').val(cost);
                    }
                } else {
                    $row.find('.stock-badge').text('-');
                }
                recalculate();
            });

            $('#payment_status_select').on('change', function() {
                if ($(this).val() === 'unpaid') {
                    $('#paid_account_wrapper').hide();
                    $('#credit_purchase_notice').show();
                } else {
                    $('#paid_account_wrapper').show();
                    $('#credit_purchase_notice').hide();
                }
            });

            $(document).on('input change', '.qty-input, .price-input', function() {
                recalculate();
            });
        });
    </script>
@endpush
