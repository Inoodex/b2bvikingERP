@extends('backend.layouts.master')

@section('title', 'Customer AR Aging Receivables Report')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clock text-primary mr-2"></i> Accounts Receivable (AR) Aging Report</h1>
        <a href="{{ route('admin.reports.ar-aging.pdf', ['customer_id' => $customerId]) }}" target="_blank" class="btn btn-danger font-weight-bold shadow-sm">
            <i class="fas fa-file-pdf mr-1"></i> Export PDF Report
        </a>
    </div>

    <style>
        .ar-card {
            border-radius: 12px;
            transition: all 0.2s ease-in-out;
            background: #ffffff;
        }
        .ar-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
        }
    </style>

    <div class="section-body">
        <!-- Executive KPI Metric Cards (Spacious 3-Column Grid) -->
        <div class="row mb-3">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100 p-3 ar-card" style="border-left: 5px solid #4f46e5 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">Total Outstanding Dues</span>
                            <h3 class="font-weight-bold text-primary mb-0 mt-2" style="font-size: 22px;">kr. {{ number_format((float)$totals['total_due'], 2) }}</h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background: rgba(79, 70, 229, 0.12); min-width: 50px; min-height: 50px;">
                            <i class="fas fa-coins" style="font-size: 22px; color: #4f46e5 !important;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100 p-3 ar-card" style="border-left: 5px solid #10b981 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">Current (0-30 Days Dues)</span>
                            <h3 class="font-weight-bold text-success mb-0 mt-2" style="font-size: 22px;">kr. {{ number_format((float)$totals['current_0_30'], 2) }}</h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background: rgba(16, 185, 129, 0.12); min-width: 50px; min-height: 50px;">
                            <i class="fas fa-calendar-check" style="font-size: 22px; color: #10b981 !important;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100 p-3 ar-card" style="border-left: 5px solid #f59e0b !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">31 - 60 Days Dues</span>
                            <h3 class="font-weight-bold text-warning mb-0 mt-2" style="font-size: 22px;">kr. {{ number_format((float)$totals['days_31_60'], 2) }}</h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background: rgba(245, 158, 11, 0.12); min-width: 50px; min-height: 50px;">
                            <i class="fas fa-exclamation-circle" style="font-size: 22px; color: #f59e0b !important;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100 p-3 ar-card" style="border-left: 5px solid #06b6d4 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">61 - 90 Days Dues</span>
                            <h3 class="font-weight-bold text-info mb-0 mt-2" style="font-size: 22px;">kr. {{ number_format((float)$totals['days_61_90'], 2) }}</h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background: rgba(6, 182, 212, 0.12); min-width: 50px; min-height: 50px;">
                            <i class="fas fa-clock" style="font-size: 22px; color: #06b6d4 !important;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100 p-3 ar-card" style="border-left: 5px solid #ef4444 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">90+ Days (Critical High Risk)</span>
                            <h3 class="font-weight-bold text-danger mb-0 mt-2" style="font-size: 22px;">kr. {{ number_format((float)$totals['over_90'], 2) }}</h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background: rgba(239, 68, 68, 0.12); min-width: 50px; min-height: 50px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 22px; color: #ef4444 !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
            <div class="card-body">
                <form action="{{ route('admin.reports.ar-aging') }}" method="GET" class="row align-items-end">
                    <div class="col-md-8">
                        <label class="font-weight-bold text-dark">Filter by B2B Customer</label>
                        <select name="customer_id" class="form-control select2">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                                    {{ $c->outlet_name ? $c->outlet_name . ' (' . $c->name . ')' : $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary font-weight-bold px-4">
                            <i class="fas fa-filter mr-1"></i> Apply Filter
                        </button>
                        <a href="{{ route('admin.reports.ar-aging') }}" class="btn btn-light ml-2">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aging Table Card -->
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white">
                <h4 class="text-primary font-weight-bold mb-0">Customer Aging Receivables Matrix</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="ar-aging-table">
                        <thead class="thead-light">
                            <tr>
                                <th>B2B Customer</th>
                                <th>Phone</th>
                                <th class="text-center">Open Invoices</th>
                                <th class="text-right">0 - 30 Days</th>
                                <th class="text-right">31 - 60 Days</th>
                                <th class="text-right">61 - 90 Days</th>
                                <th class="text-right">90+ Days (Critical)</th>
                                <th class="text-right font-weight-bold">Total Dues</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agingData as $row)
                                <tr>
                                    <td class="font-weight-bold text-dark">{{ $row['customer_name'] }}</td>
                                    <td>{{ $row['phone'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $row['invoice_count'] }}</span>
                                    </td>
                                    <td class="text-right text-success font-weight-bold">
                                        kr. {{ number_format((float)$row['current_0_30'], 2) }}
                                    </td>
                                    <td class="text-right text-warning font-weight-bold">
                                        kr. {{ number_format((float)$row['days_31_60'], 2) }}
                                    </td>
                                    <td class="text-right text-info font-weight-bold">
                                        kr. {{ number_format((float)$row['days_61_90'], 2) }}
                                    </td>
                                    <td class="text-right text-danger font-weight-bold">
                                        @if($row['over_90'] > 0)
                                            <span class="badge badge-danger">kr. {{ number_format((float)$row['over_90'], 2) }}</span>
                                        @else
                                            kr. 0.00
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold text-primary h6">
                                        kr. {{ number_format((float)$row['total_due'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success mr-1"></i> No outstanding AR aging dues found! All receivables are fully collected.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($agingData) > 0)
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="3" class="text-right h6 text-dark font-weight-bold">Total Portfolio Dues:</td>
                                    <td class="text-right text-success h6 font-weight-bold">kr. {{ number_format((float)$totals['current_0_30'], 2) }}</td>
                                    <td class="text-right text-warning h6 font-weight-bold">kr. {{ number_format((float)$totals['days_31_60'], 2) }}</td>
                                    <td class="text-right text-info h6 font-weight-bold">kr. {{ number_format((float)$totals['days_61_90'], 2) }}</td>
                                    <td class="text-right text-danger h6 font-weight-bold">kr. {{ number_format((float)$totals['over_90'], 2) }}</td>
                                    <td class="text-right text-primary h5 font-weight-bold">kr. {{ number_format((float)$totals['total_due'], 2) }}</td>
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
        if ($('#ar-aging-table tbody tr').length > 0 && !$('#ar-aging-table tbody td').hasClass('dataTables_empty')) {
            $('#ar-aging-table').DataTable({
                "pageLength": 25,
                "order": [[7, "desc"]],
                "language": {
                    "search": "Filter Customer Aging:"
                }
            });
        }
    });
</script>
@endpush
