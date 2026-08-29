@extends('backend.layouts.master')
@section('title', 'Stock Batches')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Inventory Stock Batches</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.inventory-reports.index') }}">Inventory</a></div>
            <div class="breadcrumb-item">Stock Batches</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter text-primary mr-2"></i> Filter Batches</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5 col-sm-6 form-group">
                                <label for="filter-product" class="font-weight-bold">Product</label>
                                <select id="filter-product" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->product_number ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6 form-group">
                                <label for="filter-status" class="font-weight-bold">Batch Status</label>
                                <select id="filter-status" class="form-control select2">
                                    <option value="active" selected>Active Batches (In Stock)</option>
                                    <option value="depleted">Depleted Batches (0 Qty)</option>
                                    <option value="">All Batches</option>
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-12 form-group d-flex align-items-end">
                                <button type="button" class="btn btn-warning px-4 w-100" id="reset-filters" style="height: 42px;">
                                    <i class="fas fa-undo mr-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4>All FIFO Batches</h4>
                    </div>
                    <div class="card-body table-responsive">
                        {{ $dataTable->table() }}
                    </div>
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
        $('#filter-product, #filter-status').on('change', function () {
            window.LaravelDataTables["stock-batches-table"].ajax.url(
                "{{ route('admin.stock-batches.index') }}?product_id=" + $('#filter-product').val() + "&status=" + $('#filter-status').val()
            ).load();
        });

        $('#reset-filters').on('click', function () {
            $('#filter-product').val('').trigger('change');
            $('#filter-status').val('active').trigger('change');
        });
    });
</script>
@endpush
