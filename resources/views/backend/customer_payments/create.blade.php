@extends('backend.layouts.master')

@section('title', 'Record Customer Payment Receipt')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.customer-payments.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>💳 Record Customer Payment Receipt</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.customer-payments.index') }}">Customer Payments</a></div>
            <div class="breadcrumb-item">Record Payment</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.customer-payments.store') }}" method="POST" id="payment-form">
            @csrf
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card card-primary shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                            <h4 class="text-primary font-weight-bold mb-0">
                                <i class="fas fa-receipt mr-2"></i> Payment Receipt Voucher Details
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-4">
                                <label for="user_id" class="font-weight-bold text-dark">
                                    B2B Customer / Account <span class="text-danger">*</span>
                                </label>
                                <select name="user_id" id="user_id" class="form-control select2" required>
                                    <option value="">Select B2B Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ (isset($preloadedInvoice) && $preloadedInvoice->user_id == $customer->id) || (old('user_id') == $customer->id) ? 'selected' : '' }}>
                                            {{ $customer->outlet_name ? $customer->outlet_name . ' (' . $customer->name . ')' : $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Selecting a customer will automatically load their open unpaid invoices.</small>
                            </div>

                            <div class="form-group mb-4 p-3 bg-light rounded-lg border">
                                <label for="sales_invoice_id" class="font-weight-bold text-dark">
                                    <i class="fas fa-link text-info mr-1"></i> Link Unpaid Sales Invoice (Optional)
                                </label>
                                <select name="sales_invoice_id" id="sales_invoice_id" class="form-control select2">
                                    <option value="">-- Direct Account Payment / Unallocated Deposit (No Invoice) --</option>
                                    @foreach($unpaidInvoices as $inv)
                                        <option value="{{ $inv->id }}" {{ (isset($selectedInvoiceId) && $selectedInvoiceId == $inv->id) || (old('sales_invoice_id') == $inv->id) ? 'selected' : '' }}>
                                            {{ $inv->invoice_no }} — Due: kr. {{ number_format((float)$inv->due_amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted" id="invoice-help-text">Direct payments will remain on customer balance as unallocated credit deposit.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="amount" class="font-weight-bold text-dark">
                                            Amount Received <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold bg-light text-muted">kr.</span>
                                            </div>
                                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control font-weight-bold text-dark" required value="{{ old('amount', isset($preloadedInvoice) ? $preloadedInvoice->due_amount : '') }}" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="payment_method" class="font-weight-bold text-dark">
                                            Payment Method <span class="text-danger">*</span>
                                        </label>
                                        <select name="payment_method" id="payment_method" class="form-control selectric" required>
                                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 Bank Transfer (Wire / IBAN / SEPA)</option>
                                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>💳 Credit / Debit Card (Visa / Dankort)</option>
                                            <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>📜 Bank Cheque / Commercial Draft</option>
                                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 Cash in Hand / Branch Counter</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="reference_no" class="font-weight-bold text-dark">
                                            Transaction / Wire / Cheque Reference
                                        </label>
                                        <input type="text" name="reference_no" id="reference_no" class="form-control" placeholder="e.g. TRF-981245 or CHQ-00431" value="{{ old('reference_no') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="payment_date" class="font-weight-bold text-dark">
                                            Posting Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="payment_date" id="payment_date" class="form-control" required value="{{ old('payment_date', date('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="account_id" class="font-weight-bold text-dark">
                                    Deposit GL Bank/Cash Account
                                </label>
                                <select name="account_id" id="account_id" class="form-control select2">
                                    <option value="">Auto-Detect Primary Bank/Cash Account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }} ({{ ucfirst($acc->account_type) }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label for="notes" class="font-weight-bold text-dark">Internal Audit & Accounting Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter bank transaction details, clearance notes, or payment advice references...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card card-info shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h4 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-calculator text-info mr-2"></i> Financial Settlement
                            </h4>
                        </div>
                        <div class="card-body p-4" id="invoice-summary-card">
                            <div class="p-3 bg-white border rounded-lg shadow-sm mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Invoice Total Amount:</span>
                                    <strong id="sum-total" class="text-dark">kr. {{ isset($preloadedInvoice) ? number_format((float)$preloadedInvoice->total_amount, 2) : '0.00' }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Previously Paid:</span>
                                    <strong class="text-success" id="sum-paid">kr. {{ isset($preloadedInvoice) ? number_format((float)$preloadedInvoice->paid_amount, 2) : '0.00' }}</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold text-dark">Current Balance Due:</span>
                                    <strong class="text-danger h5 mb-0 font-weight-bold" id="sum-due">kr. {{ isset($preloadedInvoice) ? number_format((float)$preloadedInvoice->due_amount, 2) : '0.00' }}</strong>
                                </div>
                            </div>

                            <button type="button" id="btn-fill-due" class="btn btn-outline-info btn-block btn-sm mb-3 font-weight-bold" style="border-radius: 6px; display: none;">
                                <i class="fas fa-magic mr-1"></i> Auto-fill Full Due Amount
                            </button>

                            <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold shadow-sm py-3" style="border-radius: 8px;">
                                <i class="fas fa-check-circle mr-2"></i> Post Payment Receipt
                            </button>
                            <a href="{{ route('admin.customer-payments.index') }}" class="btn btn-light btn-block mt-3 text-muted">
                                Cancel & Back
                            </a>
                        </div>
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
        var isManualInvoiceSelection = false;

        // 1. Dynamic Unpaid Invoice Filter when Customer is Selected
        $('#user_id').on('change', function(e, isAutoTrigger) {
            if (isManualInvoiceSelection) return;

            var customerId = $(this).val();
            var invoiceSelect = $('#sales_invoice_id');
            var currentInvoiceVal = invoiceSelect.val();
            
            invoiceSelect.empty().append('<option value="">-- Direct Account Payment / Unallocated Deposit (No Invoice) --</option>');
            resetSummary();

            if (customerId) {
                $.ajax({
                    url: "{{ route('admin.customer-payments.get-invoice-details') }}",
                    type: "GET",
                    data: { user_id: customerId },
                    success: function(res) {
                        if (res.success && res.invoices && res.invoices.length > 0) {
                            $.each(res.invoices, function(index, inv) {
                                var dueFormatted = parseFloat(inv.due_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                var isSelected = (currentInvoiceVal == inv.id) ? 'selected' : '';
                                invoiceSelect.append(
                                    $('<option value="' + inv.id + '" ' + isSelected + '>' + inv.invoice_no + ' — Due: kr. ' + dueFormatted + '</option>')
                                );
                            });

                            // If an invoice was already selected, trigger its summary update
                            if (invoiceSelect.val()) {
                                invoiceSelect.trigger('change');
                            } else if (res.invoices.length === 1) {
                                // Auto-select if only 1 unpaid invoice exists
                                invoiceSelect.val(res.invoices[0].id).trigger('change');
                            }
                        }
                    }
                });
            }
        });

        // 2. Dynamic Summary & Amount Fill when Invoice is Selected
        $('#sales_invoice_id').on('change', function() {
            var invoiceId = $(this).val();
            if (invoiceId) {
                $.ajax({
                    url: "{{ route('admin.customer-payments.get-invoice-details') }}",
                    type: "GET",
                    data: { sales_invoice_id: invoiceId },
                    success: function(res) {
                        if (res.success) {
                            if (res.user_id && $('#user_id').val() != res.user_id) {
                                isManualInvoiceSelection = true;
                                $('#user_id').val(res.user_id).trigger('change.select2');
                                isManualInvoiceSelection = false;
                            }
                            $('#amount').val(parseFloat(res.due_amount).toFixed(2));
                            $('#sum-total').text('kr. ' + parseFloat(res.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));
                            $('#sum-paid').text('kr. ' + parseFloat(res.paid_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));
                            $('#sum-due').text('kr. ' + parseFloat(res.due_amount).toLocaleString('en-US', {minimumFractionDigits: 2}));
                            $('#btn-fill-due').data('due', res.due_amount).show();
                        }
                    }
                });
            } else {
                resetSummary();
            }
        });

        // 3. Auto-fill Full Due Amount Button Handler
        $('#btn-fill-due').on('click', function() {
            var due = $(this).data('due');
            if (due) {
                $('#amount').val(parseFloat(due).toFixed(2));
            }
        });

        function resetSummary() {
            $('#sum-total').text('kr. 0.00');
            $('#sum-paid').text('kr. 0.00');
            $('#sum-due').text('kr. 0.00');
            $('#btn-fill-due').hide().removeData('due');
        }
    });
</script>
@endpush
