@extends('backend.layouts.master')
@section('title', 'Month-End Snapshots')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Month-End Stock Valuation</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.inventory-reports.index') }}">Inventory</a></div>
            <div class="breadcrumb-item">Snapshots</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                {{-- Filter Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-filter text-primary mr-2"></i> Filter Snapshots</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5 col-sm-6 form-group">
                                <label for="filter-period" class="font-weight-bold">Valuation Period (YYYY-MM)</label>
                                <select id="filter-period" class="form-control select2">
                                    <option value="">All Periods</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period }}">{{ $period }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5 col-sm-6 form-group">
                                <label for="filter-product" class="font-weight-bold">Product</label>
                                <select id="filter-product" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-12 form-group d-flex align-items-end">
                                <button type="button" class="btn btn-warning px-4 w-100" id="reset-filters" style="height: 42px;">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Month-End Valuation Snapshots</h4>
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
        $('#filter-period, #filter-product').on('change', function () {
            window.LaravelDataTables["month-end-snapshots-table"].ajax.url(
                "{{ route('admin.month-end-snapshots.index') }}?period=" + $('#filter-period').val() + "&product_id=" + $('#filter-product').val()
            ).load();
        });

        $('#reset-filters').on('click', function () {
            $('#filter-period').val('').trigger('change');
            $('#filter-product').val('').trigger('change');
        });
    });
</script>
@endpush
