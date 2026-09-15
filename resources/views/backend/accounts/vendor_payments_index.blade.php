@extends('backend.layouts.master')
@section('title', 'Vendor Payment Ledger — Accounts Payable')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-money-check-alt text-primary mr-2"></i> Vendor Payment Ledger</h1>
            <p class="text-muted mb-0 small">Accounts Payable — All Vendor Payment Vouchers & Receipt Records</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.chart-of-accounts.index') }}">Accounts</a></div>
            <div class="breadcrumb-item active">Vendor Payments</div>
        </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #6777ef !important;">
                <div class="card-icon bg-primary text-white">
                    <i class="fas fa-receipt fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Payments</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">{{ number_format($summary['count']) }}</div>
                    <small class="text-primary font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Vouchers Issued</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                <div class="card-icon bg-success text-white">
                    <i class="fas fa-hand-holding-usd fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Total Amount Paid</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">{!! formatConverted($summary['total_amount']) !!}</div>
                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Settled to Vendors</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-3">
            <div class="card card-statistic-1 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                <div class="card-icon bg-info text-white">
                    <i class="fas fa-calculator fa-2x"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4 class="text-muted small text-uppercase font-weight-bold">Avg per Payment</h4></div>
                    <div class="card-body font-weight-bold text-dark h4 mb-0">
                        {!! formatConverted($summary['count'] > 0 ? round($summary['total_amount'] / $summary['count'], 2) : 0) !!}
                    </div>
                    <small class="text-info font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Average Voucher Value</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-2"></i> Vendor Payment History</h5>
                <div class="mt-2 mt-md-0 d-flex align-items-center flex-wrap">
                    @if(!empty($latestPdf))
                        <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn btn-success btn-sm font-weight-bold mr-2" style="border-radius:6px;" title="{{ $latestPdf['filename'] }}">
                            <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                        </a>
                    @else
                        <a href="#" id="btn-download-pdf" class="btn btn-success btn-sm font-weight-bold mr-2" style="border-radius:6px; display: none;">
                            <i class="fas fa-file-download mr-1"></i> Download PDF (Ready)
                        </a>
                    @endif

                    <button type="button" id="btn-generate-pdf" class="btn btn-primary btn-sm font-weight-bold mr-2" style="border-radius:6px;" data-url="{{ route('admin.accounts.vendor-payments.pdf') }}" data-type="vendor_payments">
                        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
                    </button>
                    <a href="{{ route('admin.accounts.vendor-payments.pdf.view') }}"
                       class="btn btn-outline-secondary btn-sm font-weight-bold mr-2" target="_blank" style="border-radius:6px;">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                    <a href="{{ route('admin.accounts.vendor-payments.record-payment') }}"
                       class="btn btn-dark btn-sm font-weight-bold" style="border-radius:6px;">
                        <i class="fas fa-plus-circle mr-1"></i> Pay Vendor Invoice
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Modern Executive Filter Toolbar (Zero-Reload) -->
                <div class="p-3 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);">
                    <form id="filter-form" class="row align-items-end mb-0">
                        <div class="col-lg-3 col-md-6 col-12 form-group mb-lg-0 mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">Vendor / Supplier</label>
                            <select id="vendor_id" name="vendor_id" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">All Registered Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->shop_name ?? $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 col-6 form-group mb-lg-0 mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">From Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-lg-2 col-md-3 col-6 form-group mb-lg-0 mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">To Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-lg-2 col-md-6 col-6 form-group mb-lg-0 mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px;">Payment Method</label>
                            <select id="method" name="method" class="form-control form-control-sm" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="mobile_banking">Mobile Pay</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 form-group mb-lg-0 mb-3 d-flex align-items-center">
                            <button type="button" id="btn-reset" class="btn btn-sm d-inline-flex align-items-center font-weight-bold mr-2" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; border-radius: 8px; height: 35px; padding: 0 16px; transition: all 0.2s;" title="Reset Filters">
                                <i class="fas fa-undo mr-1.5"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Yajra Server-Side DataTable -->
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover align-middle mb-0']) }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
<script>
    $(document).ready(function() {
        const table = window.LaravelDataTables["vendor-payment-table"];

        if ($('.select2').length) {
            $('.select2').select2({
                placeholder: "All Registered Vendors",
                allowClear: true
            });
        }

        $('#vendor_id, #start_date, #end_date, #method').on('change', function() {
            if (table) {
                table.draw();
            }
        });

        $('#btn-reset').on('click', function(e) {
            e.preventDefault();
            $('#filter-form')[0].reset();
            if ($('#vendor_id').hasClass('select2-hidden-accessible')) {
                $('#vendor_id').val('').trigger('change.select2');
            }
            if (table) {
                table.draw();
            }
        });

        if (table) {
            table.on('preXhr.dt', function(e, settings, data) {
                data.vendor_id  = $('#vendor_id').val();
                data.start_date = $('#start_date').val();
                data.end_date   = $('#end_date').val();
                data.method     = $('#method').val();
            });
        }
    });
</script>
@include('backend.reports.partials.async_payables_receivables_pdf_js')
@endpush
