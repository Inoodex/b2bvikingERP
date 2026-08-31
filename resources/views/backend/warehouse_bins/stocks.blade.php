@extends('backend.layouts.master')
@section('title', 'Bin Inventory Inspector — ' . $warehouseBin->name)

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.warehouse-bins.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Bin Inventory Inspector</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.warehouse-bins.index') }}">Warehouse Bins</a></div>
            <div class="breadcrumb-item">Inspector</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Bin Master Header Info -->
        <div class="card card-primary shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-light rounded text-primary mr-3">
                                <i class="fas fa-boxes fa-3x"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 text-dark font-weight-bold">{{ $warehouseBin->name }}</h3>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-layer-group text-info mr-1"></i> Zone: <strong>{{ $warehouseBin->zone?->name ?? 'N/A' }}</strong> 
                                    <span class="mx-2">|</span> 
                                    <i class="fas fa-warehouse text-warning mr-1"></i> Outlet: <strong>{{ $warehouseBin->zone?->outlet?->name ?? 'N/A' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-right">
                        <span class="badge badge-light border text-dark font-weight-bold px-3 py-2 mr-2" style="font-size: 14px;">
                            <i class="fas fa-barcode text-primary mr-1"></i> {{ $warehouseBin->barcode }}
                        </span>
                        <a href="{{ route('admin.warehouse-bins.show', $warehouseBin->id) }}" target="_blank" class="btn btn-info font-weight-bold">
                            <i class="fas fa-print mr-1"></i> Print Barcode
                        </a>
                        <a href="{{ route('admin.bin-transfers.create') }}" class="btn btn-warning font-weight-bold ml-1">
                            <i class="fas fa-exchange-alt mr-1"></i> Relocate Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Stored Products</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($totalStoredProducts, 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-success">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Stock Qty</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($totalStockQty, 0) }} Pcs
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Active Batches</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($activeBatchesCount, 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-info">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Bin Valuation</h4>
                        </div>
                        <div class="card-body">
                            kr. {{ number_format($totalValuation, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Stocks & Batches Detailed Server-Side DataTables -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <ul class="nav nav-pills card-header-pills" id="binTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="stocks-tab" data-toggle="tab" href="#stocks-content" role="tab">
                            <i class="fas fa-boxes mr-1"></i> Stored Products Inventory ({{ number_format($totalStoredProducts, 0) }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="batches-tab" data-toggle="tab" href="#batches-content" role="tab">
                            <i class="fas fa-tags mr-1"></i> Active FIFO Batches ({{ number_format($activeBatchesCount, 0) }})
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="binTabsContent">
                    <!-- Tab 1: Inventory Stocks Server-Side DataTable -->
                    <div class="tab-pane fade show active" id="stocks-content" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table-bin-stocks" style="width: 100%;">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="8%" class="text-center">Image</th>
                                        <th width="27%">Product Name</th>
                                        <th width="25%">Variant / Attributes</th>
                                        <th width="15%">SKU / Code</th>
                                        <th width="10%" class="text-right">Unit Price</th>
                                        <th width="10%" class="text-right">On-Hand Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Stock Batches Server-Side DataTable -->
                    <div class="tab-pane fade" id="batches-content" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table-bin-batches" style="width: 100%;">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="15%">Batch Code</th>
                                        <th width="25%">Product Name</th>
                                        <th width="20%">Variant / Attributes</th>
                                        <th width="12%">Received Date</th>
                                        <th width="11%" class="text-right">Landed Cost</th>
                                        <th width="12%" class="text-right">Remaining Qty</th>
                                        <th width="15%" class="text-right">Total Batch Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
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
    $(document).ready(function() {
        // Initialize Server-Side Yajra DataTable for Bin Stored Inventory Table
        var stocksTable = $('#table-bin-stocks').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.warehouse-bins.stocks', $warehouseBin->id) }}",
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold'},
                {data: 'image', name: 'image', orderable: false, searchable: false, className: 'text-center'},
                {data: 'product_name', name: 'product_name'},
                {data: 'variant_details', name: 'variant_details'},
                {data: 'sku', name: 'sku'},
                {data: 'unit_price', name: 'unit_price', className: 'text-right font-weight-bold'},
                {data: 'quantity', name: 'quantity', className: 'text-right'}
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[6, "desc"]]
        });

        // Initialize Server-Side Yajra DataTable for Active FIFO Batches Table
        var batchesTable = $('#table-bin-batches').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.warehouse-bins.stocks', $warehouseBin->id) }}",
                data: function(d) {
                    d.type = 'batches';
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold'},
                {data: 'batch_code', name: 'batch_no'},
                {data: 'product_name', name: 'product_name'},
                {data: 'variant_details', name: 'variant_details'},
                {data: 'received_date', name: 'received_date'},
                {data: 'unit_cost', name: 'unit_cost', className: 'text-right font-weight-bold'},
                {data: 'qty_remaining', name: 'qty_remaining', className: 'text-right'},
                {data: 'total_value', name: 'total_value', className: 'text-right'}
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[4, "desc"]]
        });

        // Auto-adjust DataTable layout on tab switch
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });
    });
</script>
@endpush
