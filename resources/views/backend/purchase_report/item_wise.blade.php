@extends('backend.layouts.master')

@section('title', 'Item-wise Purchase Value Report')

@push('styles')
<style>
    /* Step 5: Executive PC Breakdown Modal Design */
    #modalPcBreakdown .modal-dialog {
        max-width: 1120px !important;
        width: 95% !important;
        margin: 1.75rem auto;
    }
    #modalPcBreakdown .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }
    #modalPcBreakdown .modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    #modalPcBreakdown .btn-modal-close {
        background: rgba(255, 255, 255, 0.12);
        border: none;
        color: #ffffff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }
    #modalPcBreakdown .btn-modal-close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg);
        color: #ffffff;
    }
    #modalPcBreakdown .kpi-card {
        border-radius: 12px;
        padding: 16px 18px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        position: relative;
        overflow: hidden;
    }
    #modalPcBreakdown .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
    }
    #modalPcBreakdown .kpi-card.kpi-blue {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
    }
    #modalPcBreakdown .kpi-card.kpi-green {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }
    #modalPcBreakdown .kpi-card.kpi-indigo {
        background: #eef2ff;
        border: 2px solid #6366f1;
    }
    #modalPcBreakdown .kpi-card.kpi-amber {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }
    #modalPcBreakdown .kpi-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        white-space: nowrap;
    }
    #modalPcBreakdown .kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.2;
        margin-top: 6px;
        letter-spacing: -0.3px;
    }
    #modalPcBreakdown .kpi-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    #modalPcBreakdown .table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 14px;
        white-space: nowrap;
    }
    #modalPcBreakdown .table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        font-size: 0.88rem;
    }
    #modalPcBreakdown .table tfoot td {
        background-color: #f8fafc;
        border-top: 2px solid #cbd5e1;
        padding: 12px 14px;
        font-size: 0.88rem;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-boxes text-primary mr-2"></i> Item-wise Purchase Value Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">Item Purchase</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Item-wise Purchase Metrics</h4>
            </div>
            <div class="card-body">
                <form id="report-filter-form" action="javascript:void(0);" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="font-weight-bold">Start Date:</label>
                            <input type="date" name="start_date" class="form-control filter-input">
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">End Date:</label>
                            <input type="date" name="end_date" class="form-control filter-input">
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold">Supplier:</label>
                            <select name="vendor_id" class="form-control select2 filter-input">
                                <option value="">-- All Suppliers --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold">Product:</label>
                            <select name="product_id" class="form-control select2 filter-input">
                                <option value="">-- All Products --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->product_number ?? $p->sku ?? 'PROD-'.$p->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-reset-filter" class="btn btn-outline-danger btn-block shadow-sm">
                                <i class="fas fa-undo-alt mr-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100']) !!}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PC Breakdown Modal (Step 5) -->
<div class="modal fade" id="modalPcBreakdown" tabindex="-1" role="dialog" aria-labelledby="modalPcBreakdownLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header d-flex align-items-center justify-content-between py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(99, 102, 241, 0.18);">
                        <i class="fas fa-cubes fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-white mb-0" id="modal-product-title">PC Purchase Breakdown</h5>
                        <small class="text-white-50">Detailed PO-wise quantity & landed cost ledger</small>
                    </div>
                </div>
                <button type="button" class="btn-modal-close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Loading Spinner -->
                <div id="pc-breakdown-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-3 font-weight-bold">Calculating Weighted Averages & Loading PO History...</p>
                </div>

                <!-- Empty State -->
                <div id="pc-breakdown-empty" class="d-none text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted font-weight-bold">No purchase records found for this product.</h6>
                    <p class="text-secondary small mb-0">Check if purchase orders are marked completed or adjust the date filter.</p>
                </div>

                <!-- Content State -->
                <div id="pc-breakdown-content" class="d-none">
                    <!-- Summary KPI Cards -->
                    <div class="row row-deck mb-4">
                        <!-- 1. Total Ordered -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="kpi-card kpi-blue d-flex flex-column justify-content-between h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="kpi-label text-info"><i class="fas fa-boxes mr-1"></i> Total Volume</span>
                                    <div class="kpi-icon-badge bg-white shadow-xs text-info"><i class="fas fa-cubes"></i></div>
                                </div>
                                <div>
                                    <div class="kpi-value text-dark" id="stat-pc-qty">0</div>
                                    <small class="text-muted" id="stat-unit-label">Total purchased quantity</small>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Total Spend -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="kpi-card kpi-green d-flex flex-column justify-content-between h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="kpi-label text-success"><i class="fas fa-wallet mr-1"></i> Total PO Spend</span>
                                    <div class="kpi-icon-badge bg-white shadow-xs text-success"><i class="fas fa-receipt"></i></div>
                                </div>
                                <div>
                                    <div class="kpi-value text-success" id="stat-pc-spend">0.00</div>
                                    <small class="text-muted">Combined purchase total</small>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Weighted Avg Cost -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="kpi-card kpi-indigo d-flex flex-column justify-content-between h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="kpi-label text-primary"><i class="fas fa-calculator mr-1"></i> Weighted Avg Cost</span>
                                    <div class="kpi-icon-badge bg-white shadow-xs text-primary"><i class="fas fa-balance-scale"></i></div>
                                </div>
                                <div>
                                    <div class="kpi-value text-primary" id="stat-pc-avg-cost">0.00</div>
                                    <small class="text-muted" id="stat-cost-unit-label">Cost per unit (volume-weighted)</small>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Weighted Landed Cost -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="kpi-card kpi-amber d-flex flex-column justify-content-between h-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="kpi-label text-warning"><i class="fas fa-truck mr-1"></i> Weighted Landed</span>
                                    <div class="kpi-icon-badge bg-white shadow-xs text-warning"><i class="fas fa-shipping-fast"></i></div>
                                </div>
                                <div>
                                    <div class="kpi-value text-dark" id="stat-pc-landed-cost">0.00</div>
                                    <small class="text-muted" id="stat-landed-unit-label">Landed cost per unit</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Breakdown Table -->
                    <div class="table-responsive rounded border bg-white mb-0 shadow-sm">
                        <table class="table table-hover table-striped mb-0 text-nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>PO Date</th>
                                    <th>PO & Invoice</th>
                                    <th>Supplier</th>
                                    <th class="text-right">Quantity (<span class="modal-unit-label">Unit</span>)</th>
                                    <th class="text-right">Unit Cost</th>
                                    <th class="text-right">Landed Cost</th>
                                    <th class="text-right pr-4">Total Line Cost</th>
                                </tr>
                            </thead>
                            <tbody id="pc-breakdown-tbody">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right font-weight-bold text-uppercase text-muted">
                                        <i class="fas fa-calculator mr-1"></i> Weighted Average & Totals:
                                    </td>
                                    <td class="text-right font-weight-bold" id="pc-breakdown-tfoot-qty">0</td>
                                    <td class="text-right font-weight-bold text-primary" id="pc-breakdown-tfoot-avg-cost">0.00</td>
                                    <td class="text-right font-weight-bold" id="pc-breakdown-tfoot-landed-cost">0.00</td>
                                    <td class="text-right font-weight-bold text-success pr-4" id="pc-breakdown-tfoot-total">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    <i class="fas fa-info-circle text-primary mr-1"></i>
                    <span>Weighted average cost is computed proportionally by purchased volume to eliminate price batch skewing.</span>
                </div>
                <button type="button" class="btn btn-secondary px-4 shadow-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
    $(document).ready(function() {
        $(document).on('change', '.filter-input', function() {
            window.LaravelDataTables['item-wise-table'].draw();
        });

        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#report-filter-form')[0].reset();
            if ($.fn.select2) {
                $('.select2').val('').trigger('change.select2');
            }
            window.LaravelDataTables['item-wise-table'].draw();
        });

        // PC Breakdown Modal handler
        $(document).on('click', '.btn-view-pc-breakdown', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var productName = $(this).data('product-name');

            $('#modal-product-title').html('<i class="fas fa-boxes mr-2"></i>' + $('<div>').text(productName).html());
            $('#pc-breakdown-loading').removeClass('d-none');
            $('#pc-breakdown-content').addClass('d-none');
            $('#pc-breakdown-empty').addClass('d-none');
            $('#modalPcBreakdown').modal('show');

            var startDate = $('input[name="start_date"]').val();
            var endDate = $('input[name="end_date"]').val();
            var vendorId = $('select[name="vendor_id"]').val();

            var url = '{{ route("admin.purchase-reports.item-wise.breakdown", ":id") }}'.replace(':id', productId);

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    vendor_id: vendorId
                },
                dataType: 'json',
                success: function(res) {
                    $('#pc-breakdown-loading').addClass('d-none');
                    if (!res || !res.orders || res.orders.length === 0) {
                        $('#pc-breakdown-empty').removeClass('d-none');
                        return;
                    }

                    var unitName = (res.product && res.product.unit_name) ? res.product.unit_name : 'Units';

                    $('#modal-product-title').html(
                        $('<div>').text(res.product.name).html() + 
                        ' <span class="badge badge-light text-primary ml-2 font-weight-normal">' + $('<div>').text(res.product.sku).html() + '</span>'
                    );

                    $('#stat-pc-qty').text(res.summary.formatted_total_qty);
                    $('#stat-pc-spend').text(res.summary.formatted_total_value);
                    $('#stat-pc-avg-cost').text(res.summary.formatted_weighted_avg_unit_cost);
                    $('#stat-pc-landed-cost').text(res.summary.formatted_weighted_avg_landed_cost);

                    $('#stat-unit-label').text('Total ' + unitName.toLowerCase());
                    $('#stat-cost-unit-label').text('Cost per ' + unitName.toLowerCase());
                    $('#stat-landed-unit-label').text('Landed per ' + unitName.toLowerCase());
                    $('.modal-unit-label').text(unitName);

                    var tbody = $('#pc-breakdown-tbody');
                    tbody.empty();

                    $.each(res.orders, function(idx, item) {
                        var invHtml = '';
                        if (item.invoice_no && item.invoice_no !== '-') {
                            invHtml = '<br><small class="text-muted"><i class="fas fa-file-invoice mr-1 text-secondary"></i>' + $('<div>').text(item.invoice_no).html() + '</small>';
                        }

                        var row = '<tr>' +
                            '<td class="text-center text-muted font-weight-bold">' + (idx + 1) + '</td>' +
                            '<td><i class="far fa-calendar-alt text-muted mr-1"></i> ' + $('<div>').text(item.date).html() + '</td>' +
                            '<td><span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-receipt text-primary mr-1"></i>' + $('<div>').text(item.po_no).html() + '</span>' + invHtml + '</td>' +
                            '<td><strong class="text-dark">' + $('<div>').text(item.supplier_name).html() + '</strong></td>' +
                            '<td class="text-right"><span class="badge badge-primary px-2 py-1 font-weight-bold">' + $('<div>').text(item.formatted_qty).html() + '</span></td>' +
                            '<td class="text-right font-weight-600">' + $('<div>').text(item.formatted_unit_cost).html() + '</td>' +
                            '<td class="text-right font-weight-600 text-muted">' + $('<div>').text(item.formatted_landed_cost).html() + '</td>' +
                            '<td class="text-right font-weight-bold text-dark pr-4">' + $('<div>').text(item.formatted_total).html() + '</td>' +
                        '</tr>';
                        tbody.append(row);
                    });

                    $('#pc-breakdown-tfoot-qty').text(res.summary.formatted_total_qty);
                    $('#pc-breakdown-tfoot-avg-cost').text(res.summary.formatted_weighted_avg_unit_cost);
                    $('#pc-breakdown-tfoot-landed-cost').text(res.summary.formatted_weighted_avg_landed_cost);
                    $('#pc-breakdown-tfoot-total').text(res.summary.formatted_total_value);

                    $('#pc-breakdown-content').removeClass('d-none');
                },
                error: function(xhr) {
                    $('#pc-breakdown-loading').addClass('d-none');
                    $('#pc-breakdown-empty').removeClass('d-none').html(
                        '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Failed to load order history details. Please try again.</div>'
                    );
                }
            });
        });
    });
    </script>
@endpush

