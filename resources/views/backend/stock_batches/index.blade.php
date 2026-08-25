@extends('backend.layouts.master')

@section('title', 'Stock Batches')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-boxes mr-2 text-primary"></i>
                    Inventory Stock Batches
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Stock Batches</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    {{-- Filter Card --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                    <i class="fas fa-filter"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Filter Batches</h5>
                                    <small class="text-muted">Search and filter inventory FIFO batches</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-box text-primary mr-1"></i>
                                            Product
                                        </label>
                                        <select id="filter-product" class="form-control form-control-sm select2" data-placeholder="Select Product">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">
                                            <i class="fas fa-thermometer-half text-warning mr-1"></i>
                                            Status
                                        </label>
                                        <select id="filter-status" class="form-control form-control-sm">
                                            <option value="active" selected>Active Batches (In Stock)</option>
                                            <option value="depleted">Depleted Batches (Empty)</option>
                                            <option value="">All Batches</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-danger btn-sm px-4 shadow-sm" id="reset-filters">
                                                <i class="fas fa-undo mr-1"></i> Reset 
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table Card --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                {{ $dataTable->table() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* Styling matching stock ledger */
    .form-group label { font-size: 0.8rem; margin-bottom: 0.4rem; letter-spacing: 0.3px; }
    .form-control { border-radius: 8px !important; border: 1.5px solid #e2e8f0; font-size: 0.85rem; }
    .form-control:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15); }
    .card { border-radius: 12px !important; overflow: hidden !important; }
    .table th { border-top: none !important; font-size: 0.75rem; text-transform: uppercase; padding: 0.75rem 1rem !important; }
    .table td { vertical-align: middle !important; font-size: 0.85rem; padding: 0.75rem 1rem !important; }
</style>
@endpush

@push('scripts')
<script>
    $('.select2').select2({ width: '100%' });
</script>
{{ $dataTable->scripts() }}
<script>
    $('#filter-product, #filter-status').on('change', function () {
        window.LaravelDataTables["stock-batches-table"].ajax.url(
            "{{ route('admin.stock-batches.index') }}?product_id=" + $('#filter-product').val() + "&status=" + $('#filter-status').val()
        ).load();
    });

    $('#reset-filters').on('click', function () {
        $('#filter-product').val('').trigger('change');
        $('#filter-status').val('active').trigger('change');
    });
</script>
@endpush
