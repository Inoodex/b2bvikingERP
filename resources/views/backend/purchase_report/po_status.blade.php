@extends('backend.layouts.master')

@section('title', 'Items Purchased & PO Issued Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-signature text-primary mr-2"></i> Items Purchased & PO Issued List</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">PO Issued</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Issued Purchase Orders Registry</h4>
            </div>
            <div class="card-body">
                <form id="report-filter-form" action="javascript:void(0);" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-2 mb-2">
                            <label class="font-weight-bold">Start Date:</label>
                            <input type="date" name="start_date" class="form-control filter-input">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="font-weight-bold">End Date:</label>
                            <input type="date" name="end_date" class="form-control filter-input">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold">Supplier / Vendor:</label>
                            <select name="vendor_id" class="form-control filter-input select2">
                                <option value="">All Suppliers</option>
                                @foreach($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->shop_name ?? $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="font-weight-bold">Type:</label>
                            <select name="purchase_type" class="form-control filter-input">
                                <option value="">All Types</option>
                                <option value="local">Local</option>
                                <option value="foreign">Foreign</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
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
                        <div class="col-md-1 mb-2">
                            <button type="button" id="btn-reset-filter" class="btn btn-outline-danger btn-block shadow-sm" title="Reset Filters">
                                <i class="fas fa-undo-alt"></i>
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
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
    $(document).ready(function() {
        $(document).on('change', '.filter-input', function() {
            window.LaravelDataTables['po-status-table'].draw();
        });

        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#report-filter-form')[0].reset();
            window.LaravelDataTables['po-status-table'].draw();
        });
    });
    </script>
@endpush
