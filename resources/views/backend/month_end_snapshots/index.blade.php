@extends('backend.layouts.master')

@section('title', 'Month-End Snapshots')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-calendar-check mr-2 text-primary"></i>
                    Month-End Stock Valuation
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Snapshots</div>
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
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">Period</label>
                                        <select id="filter-period" class="form-control form-control-sm">
                                            <option value="">All Periods</option>
                                            @foreach ($periods as $period)
                                                <option value="{{ $period }}">{{ $period }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-dark">Product</label>
                                        <select id="filter-product" class="form-control form-control-sm select2">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
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
    .form-group label { font-size: 0.8rem; margin-bottom: 0.4rem; letter-spacing: 0.3px; }
    .form-control { border-radius: 8px !important; border: 1.5px solid #e2e8f0; font-size: 0.85rem; }
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
    $('#filter-period, #filter-product').on('change', function () {
        window.LaravelDataTables["month-end-snapshots-table"].ajax.url(
            "{{ route('admin.month-end-snapshots.index') }}?period=" + $('#filter-period').val() + "&product_id=" + $('#filter-product').val()
        ).load();
    });
</script>
@endpush
