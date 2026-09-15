@extends('backend.layouts.master')
@section('title', 'AP Aging Analysis — Accounts Payable Overdue')

@section('content')
<section class="section">
    <!-- Header Section -->
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-clock text-danger mr-2"></i> Accounts Payable (AP) Aging Analysis</h1>
            <p class="text-muted mb-0 small">GAAP / IFRS Due-Date Overdue Classification & Liquidity Risk Analysis</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-ledger.index') }}">Supplier Ledger</a></div>
            <div class="breadcrumb-item active">AP Aging</div>
        </div>
    </div>

    @php
        $totalDue = $agingData->sum('total_due') ?: 1;
        $currSum = $agingData->sum('current');
        $d30Sum = $agingData->sum('days_31_60');
        $d60Sum = $agingData->sum('days_61_90');
        $d90Sum = $agingData->sum('days_90_plus');

        $pCurr = round(($currSum / $totalDue) * 100);
        $p30 = round(($d30Sum / $totalDue) * 100);
        $p60 = round(($d60Sum / $totalDue) * 100);
        $p90 = round(($d90Sum / $totalDue) * 100);
    @endphp

    <!-- Visual Aging Risk Proportions Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-chart-pie text-primary mr-2"></i> Overdue Distribution Profile (Total Debt: kr. {{ number_format($agingData->sum('total_due'), 2) }})</h6>
                    <div class="progress" style="height: 18px; border-radius: 9px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pCurr }}%;" title="Current (0-30d): {{ $pCurr }}%">Current: {{ $pCurr }}%</div>
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $p30 }}%;" title="31-60 Days: {{ $p30 }}%">31-60d: {{ $p30 }}%</div>
                        <div class="progress-bar bg-warning text-dark" role="progressbar" style="width: {{ $p60 }}%;" title="61-90 Days: {{ $p60 }}%">61-90d: {{ $p60 }}%</div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $p90 }}%;" title="90+ Days: {{ $p90 }}%">90+d: {{ $p90 }}%</div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small font-weight-bold">
                        <span class="text-success"><i class="fas fa-circle mr-1"></i> Current (0-30 Days): kr. {{ number_format($currSum, 2) }}</span>
                        <span class="text-info"><i class="fas fa-circle mr-1"></i> 31-60 Days: kr. {{ number_format($d30Sum, 2) }}</span>
                        <span class="text-warning"><i class="fas fa-circle mr-1"></i> 61-90 Days: kr. {{ number_format($d60Sum, 2) }}</span>
                        <span class="text-danger"><i class="fas fa-circle mr-1"></i> 90+ Days Overdue: kr. {{ number_format($d90Sum, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master AP Aging Table -->
    <div class="section-body">
        <div class="card shadow-sm border-0" style="border-radius: 12px; border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-alt text-primary mr-2"></i> Supplier Payables Aging Matrix</h5>
                <div class="d-flex align-items-center">
                    @if(!empty($latestPdf))
                        <a href="{{ $latestPdf['url'] }}" id="btn-download-pdf" class="btn btn-success btn-sm font-weight-bold mr-2" title="{{ $latestPdf['filename'] }}">
                            <i class="fas fa-file-download mr-1"></i> Download PDF (Ready: {{ $latestPdf['time'] }})
                        </a>
                    @else
                        <a href="#" id="btn-download-pdf" class="btn btn-success btn-sm font-weight-bold mr-2" style="display: none;">
                            <i class="fas fa-file-download mr-1"></i> Download PDF (Ready)
                        </a>
                    @endif

                    <button type="button" id="btn-generate-pdf" class="btn btn-danger btn-sm font-weight-bold" data-url="{{ route('admin.vendor-ledger.aging.pdf') }}" data-type="ap_aging">
                        <i class="fas fa-file-pdf mr-1"></i> Export Aging PDF
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Modern Executive Filter Toolbar -->
                <div class="p-3 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);">
                    <div class="row align-items-end">
                        <div class="col-lg-5 col-md-5 mb-md-0 mb-3">
                            <label class="font-weight-bold text-dark mb-1 d-flex align-items-center" style="font-size: 12px;">
                                <i class="fas fa-building text-primary mr-1.5"></i> Filter by Supplier
                            </label>
                            <select id="filter_vendor" class="form-control select2" style="width: 100%;">
                                <option value="">All Suppliers in Portfolio</option>
                                @foreach($agingData->sortBy('vendor_name') as $row)
                                    <option value="{{ $row['vendor_name'] }}">{{ $row['vendor_name'] }} ({{ $row['vendor_code'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-5 col-md-5 mb-md-0 mb-3">
                            <label class="font-weight-bold text-dark mb-1 d-flex align-items-center" style="font-size: 12px;">
                                <i class="fas fa-exclamation-triangle text-warning mr-1.5"></i> Risk Severity Bucket
                            </label>
                            <select id="filter_risk" class="form-control select2" style="width: 100%;">
                                <option value="">All Risk Levels</option>
                                <option value="critical">Critical Overdue (90+ Days)</option>
                                <option value="warning">Moderate Overdue (31 - 90 Days)</option>
                                <option value="current">Current &amp; On-Time Only (0 - 30 Days)</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2">
                            <label class="font-weight-bold text-dark mb-1 d-none d-md-block" style="font-size: 12px; visibility: hidden;">Reset</label>
                            <button type="button" id="btn-reset" class="btn btn-danger font-weight-bold w-100 d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; font-size: 13px;" title="Reset all filters">
                                <i class="fas fa-undo mr-1.5"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="ap-aging-table" class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th style="width: 14%;">Supplier Code</th>
                                <th style="width: 25%;">Supplier / Vendor Name</th>
                                <th class="text-right text-success" style="width: 13%;">Current (0-30d)</th>
                                <th class="text-right text-info" style="width: 12%;">31 - 60 Days</th>
                                <th class="text-right text-warning" style="width: 12%;">61 - 90 Days</th>
                                <th class="text-right text-danger" style="width: 12%;">90+ Days Overdue</th>
                                <th class="text-right" style="width: 12%;">Total Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agingData as $row)
                                <tr data-vendor="{{ $row['vendor_name'] }}" data-vendor-code="{{ $row['vendor_code'] }}" data-current="{{ $row['current'] }}" data-d30="{{ $row['days_31_60'] }}" data-d60="{{ $row['days_61_90'] }}" data-d90="{{ $row['days_90_plus'] }}">
                                    <td><span class="badge badge-dark font-monospace">{{ $row['vendor_code'] }}</span></td>
                                    <td>
                                        @if(!empty($row['vendor_id']))
                                            <a href="{{ route('admin.vendor-ledger.show', $row['vendor_id']) }}" class="font-weight-bold text-primary">
                                                {{ $row['vendor_name'] }} <i class="fas fa-external-link-alt ml-1 small"></i>
                                            </a>
                                        @else
                                            <strong>{{ $row['vendor_name'] }}</strong>
                                        @endif
                                        @if(!empty($row['phone']) && $row['phone'] !== 'N/A')
                                            <br><small class="text-muted"><i class="fas fa-phone mr-1"></i>{{ $row['phone'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold text-success">kr. {{ number_format($row['current'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-info">kr. {{ number_format($row['days_31_60'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-warning">kr. {{ number_format($row['days_61_90'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-danger">kr. {{ number_format($row['days_90_plus'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-dark h6 mb-0">kr. {{ number_format($row['total_due'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-success py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                        All supplier bills are fully settled! Zero outstanding liabilities.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($agingData->count() > 0)
                        <tfoot>
                            <tr class="table-active font-weight-bold">
                                <td colspan="2" class="text-right">TOTAL AP OBLIGATIONS:</td>
                                <td class="text-right text-success">kr. {{ number_format($agingData->sum('current'), 2) }}</td>
                                <td class="text-right text-info">kr. {{ number_format($agingData->sum('days_31_60'), 2) }}</td>
                                <td class="text-right text-warning">kr. {{ number_format($agingData->sum('days_61_90'), 2) }}</td>
                                <td class="text-right text-danger">kr. {{ number_format($agingData->sum('days_90_plus'), 2) }}</td>
                                <td class="text-right text-primary h5 mb-0">kr. {{ number_format($agingData->sum('total_due'), 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($('#filter_vendor').length) {
            $('#filter_vendor').select2({
                placeholder: "All Suppliers in Portfolio",
                allowClear: true,
                width: '100%'
            });
        }

        if ($('#filter_risk').length) {
            $('#filter_risk').select2({
                placeholder: "All Risk Levels",
                allowClear: true,
                width: '100%'
            });
        }

        var table = null;
        if ($('#ap-aging-table tbody tr').length > 0 && !$('#ap-aging-table tbody td').hasClass('dataTables_empty')) {
            table = $('#ap-aging-table').DataTable({
                "pageLength": 25,
                "order": [[6, "desc"]],
                "language": {
                    "search": "Quick Search Aging:"
                }
            });

            // Custom risk bucket & vendor filter function
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'ap-aging-table') {
                        return true;
                    }

                    var selectedVendor = $('#filter_vendor').val();
                    var selectedRisk = $('#filter_risk').val();

                    var $row = $(table.row(dataIndex).node());
                    var vendorText = data[1] || '';
                    var d30 = parseFloat($row.data('d30')) || 0;
                    var d60 = parseFloat($row.data('d60')) || 0;
                    var d90 = parseFloat($row.data('d90')) || 0;

                    if (selectedVendor) {
                        var rowVendor = ($row.data('vendor') || '').toString().toLowerCase();
                        var searchVendor = selectedVendor.toString().toLowerCase();
                        if (rowVendor !== searchVendor && vendorText.toLowerCase().indexOf(searchVendor) === -1) {
                            return false;
                        }
                    }

                    if (selectedRisk === 'critical') {
                        return d90 > 0;
                    } else if (selectedRisk === 'warning') {
                        return (d30 > 0 || d60 > 0);
                    } else if (selectedRisk === 'current') {
                        return (d30 === 0 && d60 === 0 && d90 === 0);
                    }

                    return true;
                }
            );

            // Re-draw table when either Supplier or Risk Severity changes
            $('#filter_vendor, #filter_risk').on('change', function() {
                table.draw();
            });

            // Danger Reset button clears both dropdowns and redraws table
            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                $('#filter_vendor').val('').trigger('change');
                $('#filter_risk').val('').trigger('change');
                table.draw();
            });
        }
    });
</script>
@include('backend.reports.partials.async_payables_receivables_pdf_js')
@endpush
