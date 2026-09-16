@extends('backend.layouts.master')

@section('title', 'Product Purchase History & Tracking')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-boxes text-primary mr-2"></i> Product Purchase Tracking</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Product Purchase Tracking</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Track Product Purchases by Vendor & Date</h4>
                        </div>
                        <div class="card-body">
                            <form id="report-filter-form" action="javascript:void(0);" class="mb-4">
                                <div class="row align-items-end">
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Filter by Product:</label>
                                        <select name="product_id" class="form-control select2 filter-input">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }} ({{ $product->product_number ?? $product->sku ?? ('PROD-' . $product->id) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Filter by Supplier / Vendor:</label>
                                        <select name="vendor_id" class="form-control select2 filter-input">
                                            <option value="">All Vendors</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">
                                                    {{ $vendor->shop_name ?? $vendor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="font-weight-bold">Start Date:</label>
                                        <input type="date" name="start_date" class="form-control filter-input">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="font-weight-bold">End Date:</label>
                                        <input type="date" name="end_date" class="form-control filter-input">
                                    </div>
                                    <div class="col-md-2 col-sm-12 mb-2">
                                        <button type="button" id="btn-reset-filter" class="btn btn-outline-danger btn-block shadow-sm">
                                            <i class="fas fa-undo-alt mr-1"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => 'product-purchase-history-table']) !!}
                            </div>
                        </div>
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
            if (window.LaravelDataTables && window.LaravelDataTables['product-purchase-history-table']) {
                window.LaravelDataTables['product-purchase-history-table'].draw();
            }
        });

        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#report-filter-form')[0].reset();
            if ($.fn.select2) {
                $('.select2').val('').trigger('change');
            }
            if (window.LaravelDataTables && window.LaravelDataTables['product-purchase-history-table']) {
                window.LaravelDataTables['product-purchase-history-table'].draw();
            }
        });
    });
    </script>
@endpush
