@extends('backend.layouts.master')
@section('title', 'Receive Customer Payment — Multi-Invoice Smart Matrix')

@push('css')
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 44px !important;
        border: 1px solid #dcdfe8 !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        font-weight: 600 !important;
        color: #2d3748 !important;
        padding-left: 14px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 10px !important;
    }
    .form-control, .custom-select {
        border-radius: 8px !important;
        border: 1px solid #dcdfe8 !important;
    }
    .form-control:focus, .custom-select:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15) !important;
    }
</style>
@endpush

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-hand-holding-usd text-primary mr-2"></i> Receive Customer Payment</h1>
            <p class="text-muted mb-0 small">SAP / Odoo Multi-Invoice FIFO Allocation & Real-time AR Knockdown</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.customer-payments.index') }}">Customer Payments</a></div>
            <div class="breadcrumb-item active">Receive Payment</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.customer-payments.store') }}" method="POST" id="payment-form">
            @csrf
            <input type="hidden" name="allocations_json" id="allocations_json" value="">
            <input type="hidden" name="order_id" id="order_id" value="{{ $selectedOrderId ?? '' }}">
            <input type="hidden" name="sales_invoice_id" id="sales_invoice_id" value="{{ $selectedInvoiceId ?? '' }}">

            <div class="row">
                <!-- Main Form Column -->
                <div class="col-12 col-lg-8">
                    <!-- Step 1: Payment Voucher Header -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-receipt text-primary mr-2"></i> Payment Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Row 1: Customer & Received Amount -->
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-store text-primary mr-1"></i> B2B Customer / Outlet <span class="text-danger">*</span></label>
                                    <select name="user_id" id="customer_select" class="form-control select2" required style="width: 100%;">
                                        <option value="">-- Select B2B Customer --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                {{ (isset($preloadedInvoice) && $preloadedInvoice->user_id == $customer->id) || (isset($preloadedOrder) && $preloadedOrder->user_id == $customer->id) || (old('user_id') == $customer->id) ? 'selected' : '' }}>
                                                {{ $customer->outlet_name ? $customer->outlet_name . ' (' . $customer->name . ')' : $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-coins text-warning mr-1"></i> Total Amount Received (DKK) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text font-weight-bold bg-light text-muted" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">kr.</span>
                                        </div>
                                        <input type="number" step="0.01" min="0.01" name="amount" id="total_amount_input" class="form-control font-weight-bold text-dark" required 
                                            value="{{ old('amount', isset($preloadedInvoice) ? $preloadedInvoice->due_amount : (isset($preloadedOrder) ? $preloadedOrder->due_amount : '')) }}" placeholder="0.00" style="font-size: 16px; height: 44px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                                    </div>
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="allow_advance_check" name="allow_advance" value="1" checked>
                                        <label class="custom-control-label small font-weight-bold text-muted" for="allow_advance_check">
                                            Auto-record excess as Customer Advance Deposit (GL 2040)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Payment Method & Deposit Account (2 columns) -->
                            <div class="row pt-2 border-top">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1"><i class="fas fa-wallet text-info mr-1"></i> Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-control font-weight-bold" style="height: 44px;" required>
                                        <option value="bank" {{ old('payment_method', 'bank') == 'bank' ? 'selected' : '' }}>🏦 Bank Transfer (Wire/SEPA)</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 Cash in Hand (Petty Cash)</option>
                                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>📜 Bank Cheque / Draft</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>💳 POS Terminal / Card</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1"><i class="fas fa-university text-primary mr-1"></i> Deposit To Account</label>
                                    <select name="account_id" id="account_id" class="form-control font-weight-bold" style="height: 44px;">
                                        @foreach($accounts->whereIn('account_code', ['1010', '1020']) as $acc)
                                            <option value="{{ $acc->id }}" {{ $acc->account_code == '1020' ? 'selected' : '' }}>
                                                {{ $acc->account_code }} — {{ $acc->account_name }} ({{ strtoupper($acc->account_type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3: Payment Date & Reference (2 columns) -->
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1"><i class="fas fa-calendar-alt text-warning mr-1"></i> Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control font-weight-bold" style="height: 44px;" value="{{ old('payment_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1"><i class="fas fa-barcode text-secondary mr-1"></i> Transaction / Ref #</label>
                                    <input type="text" name="reference_no" id="reference_no" class="form-control" style="height: 44px;" placeholder="e.g. TRF-98214 or CHQ-001" value="{{ old('reference_no') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Smart Multi-Invoice FIFO Allocation Matrix -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="font-weight-bold text-dark mb-0">
                                <i class="fas fa-tasks text-success mr-2"></i> Invoices to Settle & Allocation Matrix
                            </h5>
                            <div class="mt-2 mt-md-0" id="matrix-actions-bar">
                                <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm mr-1" id="btn-auto-allocate">
                                    <i class="fas fa-bolt mr-1"></i> ⚡ Auto-Allocate (FIFO)
                                </button>
                                <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btn-clear-allocations">
                                    <i class="fas fa-undo mr-1"></i> Clear
                                </button>
                            </div>
                        </div>

                        @if(isset($preloadedInvoice))
                        <div id="single-invoice-banner" class="alert alert-light border border-primary mx-4 my-3 p-3 d-flex flex-wrap justify-content-between align-items-center" style="border-radius: 8px;">
                            <div class="mb-2 mb-md-0">
                                <span class="badge badge-primary px-2 py-1 mr-2 font-weight-bold" id="mode-badge"><i class="fas fa-bullseye mr-1"></i> Single Invoice Mode</span>
                                <span class="text-dark font-weight-bold">Recording payment exclusively for Invoice #{{ $preloadedInvoice->invoice_no }}</span>
                                <span class="text-muted ml-1 small">({{ $preloadedInvoice->order?->order_no ? 'Order: ' . $preloadedInvoice->order->order_no . ' | ' : '' }}Due: kr. {{ number_format($preloadedInvoice->due_amount, 2) }})</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold shadow-sm" id="btn-toggle-multi-invoices">
                                <i class="fas fa-layer-group mr-1"></i> View All Customer Invoices
                            </button>
                        </div>
                        @endif

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle" id="invoices-matrix-table">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">#</th>
                                            <th style="width: 20%;">Invoice No</th>
                                            <th style="width: 15%;">Issue Date</th>
                                            <th style="width: 15%;">Due Date</th>
                                            <th style="width: 15%;" class="text-right">Total (kr.)</th>
                                            <th style="width: 15%;" class="text-right">Due (kr.)</th>
                                            <th style="width: 15%;" class="text-right">Pay Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoices-tbody">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-user-check text-muted fa-2x mb-2 d-block"></i>
                                                Please select a B2B Customer above to view open unpaid invoices.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Live Real-Time Allocation Summary Footer -->
                        <div class="card-footer bg-light border-top p-3">
                            <div class="row align-items-center text-center text-md-left">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Total Received</small>
                                    <h6 class="font-weight-bold text-dark mb-0" id="summary-received">kr. 0.00</h6>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Total Allocated to Invoices</small>
                                    <h6 class="font-weight-bold text-success mb-0" id="summary-allocated">kr. 0.00</h6>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Unallocated Advance Deposit</small>
                                    <h6 class="font-weight-bold text-info mb-0" id="summary-unallocated">kr. 0.00</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Remarks -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Notes / Internal Remarks</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Optional audit memo or bank reference notes...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column: Customer Profile & Action -->
                <div class="col-12 col-lg-4">
                    <!-- Customer Credit Profile Card -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-user-circle text-primary mr-2"></i> Customer Credit Profile</h6>
                        </div>
                        <div class="card-body p-4" id="customer-profile-box">
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-address-card fa-3x text-muted mb-2"></i>
                                <p class="small mb-0">Select a customer to load real-time credit limit, total exposure and contact details.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Card -->
                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold shadow-sm py-3 mb-2" style="border-radius: 8px;" id="btn-submit-payment">
                                <i class="fas fa-check-circle mr-2"></i> Post & Clear Payment
                            </button>
                            <a href="{{ route('admin.customer-payments.index') }}" class="btn btn-light btn-block font-weight-bold text-muted border py-2" style="border-radius: 8px;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div><!-- /.section-body -->
</section>

<!-- Customer Credit Profile Modal -->
<div class="modal fade" id="customerCreditDrawerModal" tabindex="-1" role="dialog" aria-labelledby="creditDrawerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white py-3 px-4 d-flex justify-content-between align-items-center" style="background: #0f172a;">
                <h5 class="modal-title font-weight-bold text-white mb-0" id="creditDrawerLabel">
                    <i class="fas fa-id-card text-warning mr-2"></i> B2B Customer Credit Profile & Risk Analysis
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light text-center">
                            <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Current Receivables</small>
                            <h4 class="font-weight-bold text-danger mb-0 mt-1" id="drawer-total-due">kr. 0.00</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light text-center">
                            <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Open Invoices</small>
                            <h4 class="font-weight-bold text-primary mb-0 mt-1" id="drawer-open-count">0</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light text-center">
                            <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Credit Standing</small>
                            <h4 class="font-weight-bold text-success mb-0 mt-1" id="drawer-credit-status">Standard Tier</h4>
                        </div>
                    </div>
                </div>
                <div class="card border mb-0 shadow-none">
                    <div class="card-header bg-light font-weight-bold text-dark py-2">
                        <i class="fas fa-info-circle text-primary mr-1"></i> Customer Financial Credentials
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th style="width: 35%;">Customer Name:</th>
                                <td id="drawer-name" class="font-weight-bold text-dark">-</td>
                            </tr>
                            <tr>
                                <th>Contact Phone:</th>
                                <td id="drawer-phone">-</td>
                            </tr>
                            <tr>
                                <th>Billing Email:</th>
                                <td id="drawer-email">-</td>
                            </tr>
                            <tr>
                                <th>Unallocated Advance Balance:</th>
                                <td><span class="badge badge-success px-2 py-1 font-weight-bold">Credited to GL 2040</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 6px;">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let customerInvoices = [];
    let currentInvoiceFilter = "{{ $selectedInvoiceId ?? '' }}";
    const originalInvoiceId = "{{ $selectedInvoiceId ?? '' }}";
    const originalUserId = "{{ isset($preloadedInvoice) ? $preloadedInvoice->user_id : (isset($preloadedOrder) ? $preloadedOrder->user_id : '') }}";

    function formatMoney(amount) {
        return 'kr. ' + parseFloat(amount || 0).toLocaleString('da-DK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalculateSummary() {
        const totalReceived = parseFloat($('#total_amount_input').val()) || 0;
        let totalAllocated = 0;
        const allocations = [];

        $('.alloc-input').each(function() {
            const rowVal = parseFloat($(this).val()) || 0;
            const invoiceId = $(this).data('invoice-id');
            if (rowVal > 0) {
                totalAllocated += rowVal;
                allocations.push({ sales_invoice_id: invoiceId, amount: rowVal });
            }
        });

        const unallocated = Math.max(0, totalReceived - totalAllocated);

        $('#summary-received').text(formatMoney(totalReceived));
        $('#summary-allocated').text(formatMoney(totalAllocated));
        $('#summary-unallocated').text(formatMoney(unallocated));

        $('#allocations_json').val(JSON.stringify(allocations));
    }

    function loadCustomerData(userId, overrideInvoiceFilter) {
        if (!userId) {
            $('#invoices-tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Please select a B2B Customer above.</td></tr>');
            $('#customer-profile-box').html('<div class="text-center text-muted py-3"><i class="fas fa-address-card fa-3x text-muted mb-2"></i><p class="small mb-0">Select a customer to load profile.</p></div>');
            customerInvoices = [];
            recalculateSummary();
            return;
        }

        const activeFilter = (overrideInvoiceFilter !== undefined) ? overrideInvoiceFilter : currentInvoiceFilter;

        $('#invoices-tbody').html('<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Loading invoices...</td></tr>');

        $.ajax({
            url: "{{ route('admin.customer-payments.get-customer-invoices') }}",
            type: "GET",
            data: { 
                user_id: userId,
                sales_invoice_id: activeFilter || ''
            },
            success: function(res) {
                if (res.success) {
                    // Update Single Invoice Banner UI
                    if (res.is_single_invoice_mode) {
                        $('#single-invoice-banner').slideDown();
                        $('#mode-badge').removeClass('badge-secondary').addClass('badge-primary')
                            .html('<i class="fas fa-bullseye mr-1"></i> Single Invoice Mode');
                        $('#btn-toggle-multi-invoices').html('<i class="fas fa-layer-group mr-1"></i> View All Customer Invoices');
                    } else if (originalInvoiceId) {
                        $('#single-invoice-banner').slideDown();
                        $('#mode-badge').removeClass('badge-primary').addClass('badge-secondary')
                            .html('<i class="fas fa-layer-group mr-1"></i> Multi-Invoice Mode (All Open)');
                        $('#btn-toggle-multi-invoices').html('<i class="fas fa-bullseye mr-1"></i> Switch back to Single Invoice');
                    }

                    // Populate Customer Credit Profile Drawer
                    $('#drawer-name').text(res.customer_name);
                    $('#drawer-phone').text(res.customer_phone || 'N/A');
                    $('#drawer-email').text(res.customer_email || 'N/A');
                    $('#drawer-total-due').text(formatMoney(res.total_customer_due));
                    $('#drawer-open-count').text(res.invoices.length);

                    const modeNotice = res.is_single_invoice_mode 
                        ? `<div class="badge badge-primary px-2 py-1 mb-2 font-weight-bold d-block text-left" style="border-radius: 6px;"><i class="fas fa-bullseye mr-1"></i> Dedicated Single-Invoice Mode</div>` 
                        : '';

                    const profileHtml = `
                        ${modeNotice}
                        <h6 class="font-weight-bold text-dark mb-1">${res.customer_name}</h6>
                        <small class="text-muted d-block mb-3"><i class="fas fa-envelope mr-1"></i> ${res.customer_email || 'N/A'}</small>
                        <div class="border rounded p-3 bg-light mb-3" style="border-radius: 8px;">
                            ${res.is_single_invoice_mode && res.target_invoice_due ? `
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="small text-muted font-weight-bold">Invoice Due:</span>
                                <strong class="text-danger font-weight-bold">${formatMoney(res.target_invoice_due)}</strong>
                            </div>
                            ` : ''}
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted font-weight-bold">Total Customer Due:</span>
                                <strong class="text-dark">${formatMoney(res.total_customer_due)}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="small text-muted font-weight-bold">${res.is_single_invoice_mode ? 'Invoices in View:' : 'Total Open Invoices:'}</span>
                                <span class="badge ${res.is_single_invoice_mode ? 'badge-primary' : 'badge-warning text-dark'} font-weight-bold">${res.invoices.length} ${res.invoices.length === 1 ? 'Invoice' : 'Invoices'}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-block font-weight-bold shadow-sm" data-toggle="modal" data-target="#customerCreditDrawerModal" style="border-radius: 6px;">
                                <i class="fas fa-id-card-alt mr-1"></i> View Full Credit Profile
                            </button>
                        </div>
                    `;
                    $('#customer-profile-box').html(profileHtml);

                    // 2. Render Invoices Matrix Table
                    customerInvoices = res.invoices;
                    if (customerInvoices.length === 0) {
                        $('#invoices-tbody').html('<tr><td colspan="7" class="text-center text-success py-4"><i class="fas fa-check-circle mr-1"></i> No outstanding due balance found for this invoice or customer.</td></tr>');
                    } else {
                        let rows = '';
                        customerInvoices.forEach(function(inv, idx) {
                            rows += `
                                <tr class="${res.is_single_invoice_mode ? 'table-light' : ''}">
                                    <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
                                    <td>
                                        <strong class="text-dark">${inv.invoice_no}</strong>
                                        <div class="small text-muted font-weight-bold"><i class="fas fa-shopping-bag mr-1"></i> ${inv.order_no}</div>
                                        <div class="small text-primary mt-1" style="font-size: 11px;"><i class="fas fa-boxes mr-1"></i> ${inv.items_summary}</div>
                                    </td>
                                    <td>${inv.date || 'N/A'}</td>
                                    <td><span class="badge badge-light border text-danger font-weight-bold">${inv.due_date || 'N/A'}</span></td>
                                    <td class="text-right">${formatMoney(inv.total_amount)}</td>
                                    <td class="text-right font-weight-bold text-danger">${formatMoney(inv.due_amount)}</td>
                                    <td class="text-right">
                                        <input type="number" step="0.01" min="0" max="${inv.due_amount}" 
                                            class="form-control form-control-sm text-right font-weight-bold alloc-input" 
                                            data-invoice-id="${inv.id}" data-due="${inv.due_amount}" value="0.00" style="border-radius: 6px;">
                                    </td>
                                </tr>
                            `;
                        });
                        $('#invoices-tbody').html(rows);

                        // Auto-populate row amount
                        if (activeFilter) {
                            const targetInput = $(`.alloc-input[data-invoice-id="${activeFilter}"]`);
                            if (targetInput.length) {
                                const due = parseFloat(targetInput.data('due')) || 0;
                                const initialAmt = parseFloat($('#total_amount_input').val()) || due;
                                if (!$('#total_amount_input').val() || parseFloat($('#total_amount_input').val()) <= 0) {
                                    $('#total_amount_input').val(due.toFixed(2));
                                }
                                targetInput.val(Math.min(initialAmt, due).toFixed(2));
                            } else {
                                runAutoAllocate();
                            }
                        } else if ($('#total_amount_input').val() > 0) {
                            runAutoAllocate();
                        }
                    }
                    recalculateSummary();
                }
            }
        });
    }

    function runAutoAllocate() {
        let remaining = parseFloat($('#total_amount_input').val()) || 0;
        $('.alloc-input').each(function() {
            const due = parseFloat($(this).data('due')) || 0;
            if (remaining > 0) {
                const allocate = Math.min(remaining, due);
                $(this).val(allocate.toFixed(2));
                remaining -= allocate;
            } else {
                $(this).val('0.00');
            }
        });
        recalculateSummary();
    }

    // Toggle Multi-Invoice vs Single-Invoice view
    $(document).on('click', '#btn-toggle-multi-invoices', function(e) {
        e.preventDefault();
        if (currentInvoiceFilter) {
            currentInvoiceFilter = '';
            loadCustomerData($('#customer_select').val(), '');
        } else {
            currentInvoiceFilter = originalInvoiceId;
            loadCustomerData($('#customer_select').val(), currentInvoiceFilter);
        }
    });

    // Trigger on customer change
    $('#customer_select').on('change', function() {
        const selectedUserId = $(this).val();
        if (originalUserId && selectedUserId != originalUserId) {
            currentInvoiceFilter = '';
            $('#single-invoice-banner').slideUp();
        } else if (originalInvoiceId) {
            currentInvoiceFilter = originalInvoiceId;
        }
        loadCustomerData(selectedUserId, currentInvoiceFilter);
    });

    // Auto allocate button
    $('#btn-auto-allocate').on('click', function() {
        runAutoAllocate();
    });

    // Clear allocations button
    $('#btn-clear-allocations').on('click', function() {
        $('.alloc-input').val('0.00');
        recalculateSummary();
    });

    // Recompute on amount change or row change
    $('#total_amount_input').on('keyup change', function() {
        recalculateSummary();
    });

    $(document).on('keyup change', '.alloc-input', function() {
        recalculateSummary();
    });

    // Auto-link Payment Method with Deposit GL Account
    $('#payment_method').on('change', function() {
        const method = $(this).val();
        if (method === 'cash') {
            $('#account_id option').each(function() {
                if ($(this).text().indexOf('1010') !== -1) $(this).prop('selected', true);
            });
        } else {
            $('#account_id option').each(function() {
                if ($(this).text().indexOf('1020') !== -1) $(this).prop('selected', true);
            });
        }
    });

    // Initial load if customer already selected
    if ($('#customer_select').val()) {
        loadCustomerData($('#customer_select').val(), currentInvoiceFilter);
    }
});
</script>
@endpush
