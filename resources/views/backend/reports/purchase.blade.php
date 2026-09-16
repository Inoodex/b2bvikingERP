@extends('backend.layouts.master')

@section('title', 'Purchase & Procurement History Report')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Purchase History & Procurement Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Purchase History</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>All Procurement Orders & Purchases</h4>
                            <div class="card-header-action d-flex align-items-center">
                                @if(!empty($latestPdf))
                                    <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn btn-success btn-download-pdf mr-2" title="{{ $latestPdf['filename'] }}">
                                        <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                                    </a>
                                @else
                                    <a href="javascript:void(0);" id="btn-download-pdf" class="btn btn-success btn-download-pdf mr-2" style="display: none;">
                                        <i class="fas fa-file-download mr-1"></i> Download PDF
                                    </a>
                                @endif

                                <button type="button" id="btn-generate-pdf" class="btn btn-outline-primary btn-generate-pdf"
                                    data-url="{{ route('admin.reports.procurement.pdf.async') }}"
                                    data-type="procurement_report"
                                    data-check-url="{{ route('admin.reports.procurement.check-status') }}"
                                    title="Export high-resolution PDF report">
                                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form id="report-filter-form" action="javascript:void(0);" class="mb-4">
                                <div class="row align-items-end">
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Start Date:</label>
                                        <input type="date" name="start_date" class="form-control filter-input" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="font-weight-bold">End Date:</label>
                                        <input type="date" name="end_date" class="form-control filter-input" value="{{ request('end_date') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Supplier / Vendor:</label>
                                        <select name="vendor_id" class="form-control select2 filter-input">
                                            <option value="">All Vendors</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                    {{ $vendor->shop_name ?? $vendor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Type:</label>
                                        <select name="purchase_type" class="form-control filter-input">
                                            <option value="">All Types</option>
                                            <option value="local">Local</option>
                                            <option value="foreign">Foreign / Import</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Milestone:</label>
                                        <select name="milestone_status" class="form-control filter-input">
                                            <option value="">All Statuses</option>
                                            <option value="draft">Draft</option>
                                            <option value="approved">Approved</option>
                                            <option value="po_sent">PO Sent</option>
                                            <option value="lc_opened">LC Opened</option>
                                            <option value="shipped">Shipped</option>
                                            <option value="goods_received">Goods Received</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 col-sm-12 mb-2">
                                        <button type="button" id="btn-reset-filter" class="btn btn-outline-danger btn-block shadow-sm" title="Reset Filters">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => 'purchase-history-table']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
    $(document).ready(function() {
        $(document).on('change', '.filter-input', function() {
            if (window.LaravelDataTables && window.LaravelDataTables['purchase-history-table']) {
                window.LaravelDataTables['purchase-history-table'].draw();
            }
        });

        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#report-filter-form')[0].reset();
            if ($.fn.select2) {
                $('.select2').val('').trigger('change');
            }
            if (window.LaravelDataTables && window.LaravelDataTables['purchase-history-table']) {
                window.LaravelDataTables['purchase-history-table'].draw();
            }
        });
    });
    </script>
    @include('backend.reports.partials.async_analytics_report_pdf_js')
@endpush
