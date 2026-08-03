@extends('backend.layouts.master')

@section('title', 'Supplier-wise Purchase Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-bar text-primary mr-2"></i> Supplier-wise Purchase Report (Client Req 2.23)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">Supplier Purchase</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Report Filters & Export</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.supplier-wise') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>End Date:</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>Supplier:</label>
                            <select name="vendor_id" class="form-control select2">
                                <option value="">-- All Suppliers --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ (isset($filters['vendor_id']) && $filters['vendor_id'] == $v->id) ? 'selected' : '' }}>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('admin.purchase-reports.supplier-wise') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="reportTable">
                        <thead>
                            <tr class="bg-light">
                                <th>Supplier Name</th>
                                <th>Supplier Code</th>
                                <th class="text-center">Total POs Issued</th>
                                <th class="text-right">Total Purchase Value</th>
                                <th class="text-right text-success">Total Paid</th>
                                <th class="text-right text-danger">Total Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData as $row)
                                <tr>
                                    <td><strong>{{ $row['vendor_name'] }}</strong></td>
                                    <td><code>{{ $row['vendor_code'] }}</code></td>
                                    <td class="text-center">{{ $row['po_count'] }}</td>
                                    <td class="text-right font-weight-bold">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['total_base_amount'], 2) }}</td>
                                    <td class="text-right text-success">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['total_paid'], 2) }}</td>
                                    <td class="text-right text-danger font-weight-bold">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['total_due'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No purchase records found for the selected filter criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($reportData->count() > 0)
                        <tfoot>
                            <tr class="table-active font-weight-bold">
                                <td colspan="2" class="text-right">GRAND TOTAL:</td>
                                <td class="text-center">{{ $reportData->sum('po_count') }}</td>
                                <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($reportData->sum('total_base_amount'), 2) }}</td>
                                <td class="text-right text-success">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($reportData->sum('total_paid'), 2) }}</td>
                                <td class="text-right text-danger">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($reportData->sum('total_due'), 2) }}</td>
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
