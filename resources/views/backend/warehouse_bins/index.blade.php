@extends('backend.layouts.master')
@section('title', 'Warehouse Bins')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Warehouse Bins</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Warehouse Bins</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>All Warehouse Bins</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.bin-transfers.create') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-exchange-alt mr-1"></i> Relocate Stock
                            </a>
                            <a href="{{ route('admin.warehouse-bins.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Create New
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive card-body">
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
@endpush
