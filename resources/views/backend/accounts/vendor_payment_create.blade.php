@extends('backend.layouts.master')

@section('title', 'Pay Vendor Invoice')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-money-bill-wave mr-2 text-primary"></i>
                    Pay Vendor Invoice
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.accounts.vendor-payments.index') }}">Vendor Payments</a>
                        </div>
                        <div class="breadcrumb-item active">Pay Invoice</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-12">
                    <div class="card shadow-sm border-0">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Search Purchase Invoice</h5>
                                    <small class="text-muted">Find invoice and process vendor payment</small>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-4">
                            {{-- Search Section --}}
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">
                                    <i class="fas fa-file-invoice text-primary mr-1"></i>
                                    Purchase Invoice No
                                </label>
                                <div class="input-group">
                                    <input type="text" id="invoice_no_input" class="form-control form-control-lg" 
                                           placeholder="Enter Purchase Invoice No (e.g. INV-123456)">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary px-4" id="search_btn" type="button">
                                            <i class="fas fa-search mr-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Enter the purchase invoice number to process payment</small>
                            </div>

                            {{-- Purchase Details Section --}}
                            <div id="purchase_details_section" style="display: none;">
                                <hr class="my-4">

                                {{-- Vendor Info --}}
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <div class="bg-light p-3 rounded">
                                            <div class="text-muted small text-uppercase mb-1">Vendor Name</div>
                                            <p id="vendor_name" class="font-weight-bold h6 text-dark mb-2"></p>
                                            <div class="text-muted small text-uppercase mb-1">Purchase Date</div>
                                            <p id="purchase_date" class="font-weight-bold h6 text-dark mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="bg-light p-3 rounded">
                                            <div class="text-muted small text-uppercase mb-1">Payment Status</div>
                                            <span id="payment_status" class="badge badge-secondary"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Amount Summary --}}
                                <div class="row text-center mb-4">
                                    <div class="col-4 border-right">
                                        <div class="text-muted small text-uppercase">Total</div>
                                        <div class="h5 font-weight-bold text-primary" id="total_amount"></div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="text-muted small text-uppercase">Paid</div>
                                        <div class="h5 font-weight-bold text-success" id="paid_amount"></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small text-uppercase">Due</div>
                                        <div class="h5 font-weight-bold text-danger" id="due_amount"></div>
                                    </div>
                                </div>

                                {{-- Payment Form --}}
                                <div id="payment_form_wrapper">
                                    <form id="payment_form" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="source" value="central_entry">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold text-dark">
                                                        <i class="fas fa-money-bill text-success mr-1"></i>
                                                        Amount to Pay
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" name="amount" id="pay_amount_input" 
                                                           class="form-control form-control-lg" step="0.01" required>
                                                    <small class="text-muted">Enter the amount you want to pay</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold text-dark">
                                                        <i class="fas fa-credit-card text-info mr-1"></i>
                                                        Payment Method
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="payment_method" class="form-control form-control-lg" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="bank">Bank Transfer</option>
                                                        <option value="mobile_banking">Mobile Pay</option>
                                                        <option value="cheque">Cheque</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark">
                                                <i class="fas fa-hashtag text-warning mr-1"></i>
                                                Transaction ID / Reference
                                            </label>
                                            <input type="text" name="transaction_id" class="form-control form-control-lg" 
                                                   placeholder="Optional reference number">
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark">
                                                <i class="fas fa-sticky-note text-secondary mr-1"></i>
                                                Note
                                            </label>
                                            <textarea name="note" class="form-control" rows="3" 
                                                      placeholder="Optional note about this payment"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark">
                                                <i class="fas fa-paperclip text-primary mr-1"></i>
                                                Receipt(s)
                                            </label>
                                            <div class="custom-file">
                                                <input type="file" name="receipts[]" class="custom-file-input" 
                                                       multiple accept=".jpg,.jpeg,.png,.webp,.pdf" id="receiptInput">
                                                <label class="custom-file-label" for="receiptInput">
                                                    <i class="fas fa-cloud-upload-alt mr-1"></i>
                                                    Choose files (multiple allowed)
                                                </label>
                                            </div>
                                            <small class="text-muted">You can upload multiple files (JPG/PNG/WEBP/PDF).</small>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm mt-3">
                                            <i class="fas fa-check-circle mr-2"></i> Confirm Vendor Payment
                                        </button>
                                    </form>
                                </div>

                                {{-- Full Paid Message --}}
                                <div id="full_paid_msg" style="display: none;" class="alert alert-success text-center">
                                    <i class="fas fa-check-circle fa-2x d-block mb-2"></i>
                                    <h5>This purchase invoice is already fully paid.</h5>
                                    <p class="mb-0">No payment required for this invoice.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* =============================================
       PAY VENDOR INVOICE - CUSTOM STYLES
       ============================================= */

    .form-group label {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        letter-spacing: 0.3px;
    }

    .form-control {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: #fafbfc;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff;
    }

    .form-control-lg {
        height: 50px !important;
        font-size: 0.95rem !important;
    }

    .form-control.is-invalid:focus {
        border-color: #e74a3b;
        box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.15);
    }

    /* Input Group */
    .input-group .input-group-append .btn {
        border-radius: 0 10px 10px 0 !important;
        border: 2px solid #4e73df;
        border-left: none;
        background: #4e73df !important;
        color: #fff !important;
        padding: 0 1.5rem !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
    }

    .input-group .form-control {
        border-radius: 10px 0 0 10px !important;
        border-right: none;
    }

    .input-group .form-control:focus + .input-group-append .btn {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    /* Custom File Input */
    .custom-file-label {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        background: #fafbfc;
        cursor: pointer;
        transition: all 0.3s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        height: 50px;
        display: flex;
        align-items: center;
    }

    .custom-file-label:hover {
        border-color: #4e73df;
        background: #f8f9fc;
    }

    .custom-file-input:focus ~ .custom-file-label {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    /* Badge */
    .badge {
        font-weight: 600 !important;
        padding: 0.5rem 1rem !important;
        border-radius: 50rem !important;
        font-size: 0.85rem !important;
    }

    .badge-secondary {
        background: #858796 !important;
        color: #fff !important;
    }

    .badge-warning {
        background: #f6c23e !important;
        color: #1a1a2e !important;
    }

    .badge-success {
        background: #1cc88a !important;
        color: #fff !important;
    }

    /* Buttons */
    .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 1.5rem !important;
        font-size: 0.9rem !important;
        letter-spacing: 0.3px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: scale(0.97);
    }

    .btn-primary {
        background: #4e73df !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #224abe !important;
        box-shadow: 0 6px 20px rgba(78, 115, 223, 0.35) !important;
    }

    .btn-success {
        background: #1cc88a !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-success:hover {
        background: #13855c !important;
        box-shadow: 0 6px 20px rgba(28, 200, 138, 0.35) !important;
    }

    .btn-block {
        border-radius: 10px !important;
    }

    /* Card */
    .card {
        border-radius: 16px !important;
        overflow: hidden !important;
    }

    .card-header:first-child {
        border-radius: 16px 16px 0 0 !important;
    }

    .card-footer:last-child {
        border-radius: 0 0 16px 16px !important;
    }

    /* Amount Summary */
    .border-right {
        border-right: 2px solid #e2e8f0 !important;
    }

    .col-4:last-child {
        border-right: none !important;
    }

    /* Alert */
    .alert {
        border-radius: 12px !important;
        border: none !important;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb) !important;
        color: #155724 !important;
    }

    /* Mobile Responsive */
    @media (max-width: 575.98px) {
        .section-header {
            padding: 12px 15px !important;
        }
        .section-header h1 {
            font-size: 1rem !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .card-header {
            padding: 10px 15px !important;
        }
        .form-control-lg {
            height: 44px !important;
            font-size: 0.85rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 38px !important;
            padding: 0 1rem !important;
        }
        .input-group .input-group-append .btn {
            padding: 0 1rem !important;
            font-size: 0.8rem !important;
        }
        .custom-file-label {
            height: 44px !important;
            font-size: 0.85rem !important;
        }
        .badge {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.7rem !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .section-header .breadcrumb {
            font-size: 0.7rem !important;
        }
        .text-sm-right {
            text-align: left !important;
        }
        .col-sm-6 {
            margin-bottom: 15px !important;
        }
        .border-right {
            border-right: none !important;
        }
        .row.text-center .col-4 {
            border-right: 1px solid #e2e8f0 !important;
        }
        .row.text-center .col-4:last-child {
            border-right: none !important;
        }
        .bg-light.p-3 {
            padding: 12px !important;
        }
        .btn-block {
            font-size: 0.85rem !important;
            min-height: 44px !important;
        }
        .h5 {
            font-size: 1.1rem !important;
        }
        .h6 {
            font-size: 0.9rem !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        .form-control-lg {
            height: 46px !important;
        }
        .btn {
            min-height: 40px !important;
        }
        .custom-file-label {
            height: 46px !important;
        }
        .border-right {
            border-right: 1px solid #e2e8f0 !important;
        }
        .col-sm-6 {
            margin-bottom: 10px !important;
        }
    }

    @media (max-width: 767.98px) {
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    #purchase_details_section {
        animation: fadeInUp 0.3s ease-out;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #224abe;
    }

    /* Loading State */
    .btn-progress {
        pointer-events: none !important;
        opacity: 0.7 !important;
    }

    /* Validation */
    .invalid-feedback {
        font-size: 0.8rem;
        font-weight: 500;
        margin-top: 0.3rem;
    }

    .text-danger {
        font-weight: 700 !important;
        font-size: 1.1rem;
    }

    /* Form Control Plain Text */
    .form-control-plaintext {
        font-size: 1rem;
        font-weight: 700;
        padding: 0.3rem 0;
    }

    /* Hover effects */
    .form-control:hover {
        border-color: #4e73df;
        background: #ffffff;
    }

    .custom-file-label:hover {
        border-color: #4e73df;
        background: #ffffff;
    }

    /* Focus glow */
    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15) !important;
    }

    select.form-control {
        appearance: auto !important;
        -webkit-appearance: auto !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const statusClasses = {
            pending: 'badge-secondary',
            partial: 'badge-warning',
            paid: 'badge-success'
        };

        const urlParams = new URLSearchParams(window.location.search);
        const invoiceNoParam = urlParams.get('invoice_no');
        if (invoiceNoParam) {
            $('#invoice_no_input').val(invoiceNoParam);
            setTimeout(() => {
                $('#search_btn').click();
            }, 500);
        }

        // File input label update
        $('#receiptInput').on('change', function() {
            let files = $(this).prop('files');
            let label = $(this).siblings('.custom-file-label');
            if (files.length === 0) {
                label.html('<i class="fas fa-cloud-upload-alt mr-1"></i> Choose files (multiple allowed)');
            } else if (files.length === 1) {
                label.text(files[0].name);
            } else {
                label.text(files.length + ' files selected');
            }
        });

        $('#search_btn').on('click', function() {
            const invoiceNo = $('#invoice_no_input').val();
            if (!invoiceNo) {
                toastr.warning('Please enter a purchase invoice number');
                return;
            }

            $.ajax({
                url: "{{ route('admin.accounts.vendor-payments.search-purchase') }}",
                method: "GET",
                data: { invoice_no: invoiceNo },
                beforeSend: function() {
                    $('#search_btn').addClass('btn-progress disabled');
                    $('#search_btn').html('<i class="fas fa-spinner fa-spin mr-1"></i> Searching...');
                },
                success: function(response) {
                    $('#search_btn').removeClass('btn-progress disabled');
                    $('#search_btn').html('<i class="fas fa-search mr-1"></i> Search');

                    if (response.success) {
                        const purchase = response.purchase;
                        const normalizedStatus = (purchase.payment_status || 'Pending').toLowerCase();

                        $('#purchase_details_section').show();
                        $('#vendor_name').text(purchase.vendor_name);
                        $('#purchase_date').text(purchase.purchase_date);
                        $('#total_amount').text(purchase.total_amount);
                        $('#paid_amount').text(purchase.paid_amount);
                        $('#due_amount').text(purchase.due_amount);
                        $('#payment_status')
                            .text(purchase.payment_status)
                            .removeClass('badge-secondary badge-warning badge-success')
                            .addClass(statusClasses[normalizedStatus] || 'badge-secondary');

                        $('#payment_form').attr('action', `/admin/accounts/purchases/${purchase.id}/payment`);
                        $('#pay_amount_input').val(purchase.due_raw).attr('max', purchase.due_raw);

                        if (parseFloat(purchase.due_raw) <= 0) {
                            $('#payment_form_wrapper').hide();
                            $('#full_paid_msg').show();
                        } else {
                            $('#payment_form_wrapper').show();
                            $('#full_paid_msg').hide();
                        }
                    }
                },
                error: function(xhr) {
                    $('#search_btn').removeClass('btn-progress disabled');
                    $('#search_btn').html('<i class="fas fa-search mr-1"></i> Search');
                    $('#purchase_details_section').hide();
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error occurred');
                }
            });
        });

        $('#invoice_no_input').on('keypress', function(e) {
            if (e.which === 13) {
                $('#search_btn').click();
            }
        });
    });
</script>
@endpush