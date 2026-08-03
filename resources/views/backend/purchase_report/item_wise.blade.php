@extends('backend.layouts.master')

@section('title', 'Item-wise Purchase Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-boxes text-primary mr-2"></i> Item-wise Purchase Value Report (Client Req 2.24 & 2.26)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">Item Purchase</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Filter Purchased Items</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.item-wise') }}" method="GET" class="mb-4">
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
                            <label>Product:</label>
                            <select name="product_id" class="form-control select2">
                                <option value="">-- All Products --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ (isset($filters['product_id']) && $filters['product_id'] == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('admin.purchase-reports.item-wise') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="bg-light">
                                <th>Product Name</th>
                                <th class="text-center">Total Quantity Purchased</th>
                                <th class="text-right">Average Unit Price</th>
                                <th class="text-right">Average Landed Cost</th>
                                <th class="text-right font-weight-bold">Total Purchase Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData as $row)
                                <tr>
                                    <td><strong>{{ $row['product_name'] }}</strong></td>
                                    <td class="text-center font-weight-bold">{{ number_format($row['total_qty'], 2) }}</td>
                                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['avg_unit_price'], 2) }}</td>
                                    <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['avg_landed_cost'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-primary">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($row['total_value'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No item purchase records found for selected filter criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($reportData->count() > 0)
                        <tfoot>
                            <tr class="table-active font-weight-bold">
                                <td class="text-right">TOTAL CUMULATIVE VALUE:</td>
                                <td class="text-center">{{ number_format($reportData->sum('total_qty'), 2) }}</td>
                                <td colspan="2"></td>
                                <td class="text-right text-primary h5">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($reportData->sum('total_value'), 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                @if($reportData->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $reportData->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
