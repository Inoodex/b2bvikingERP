@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Purchase History Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Purchase History</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Purchase Invoices</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.reports.purchase') }}" method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Vendor</label>
                                        <select name="vendor_id" class="form-control">
                                            <option value="">All Vendors</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>&nbsp;</label>
                                        <div class="d-flex">
                                            <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                            <a href="{{ route('admin.reports.purchase') }}" class="btn btn-secondary">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Vendor</th>
                                            <th>Created By</th>
                                            <th>Items Count</th>
                                             <th>Base Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->date }}</td>
                                                <td>{{ $purchase->invoice_no }}</td>
                                                <td>{{ $purchase->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>{{ $purchase->user->name ?? 'System' }}</td>
                                                <td>{{ $purchase->details->count() }}</td>
                                                <td>{!! formatConverted($purchase->total_amount) !!}</td>
                                                <td>
                                                    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted mb-3 text-center" style="font-size: 14px; font-weight: 500;">
                                Showing 
                                <span class="text-dark font-weight-bold">
                                    {{ $purchases->firstItem() ?? 0 }} - {{ $purchases->lastItem() ?? 0 }}
                                </span> 
                                of 
                                 <span class="text-dark font-weight-bold">
                                    {{ $purchases->total() }}
                                </span> Purchases
                            </p>
                            <div class="d-flex justify-content-center flex-wrap custom-pagination">
                                {{ $purchases->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $("#table-1").dataTable({
            "order": [[0, "desc"]],
            paging: false,
            info: false,
            searching: false
        });
    </script>
@endpush
