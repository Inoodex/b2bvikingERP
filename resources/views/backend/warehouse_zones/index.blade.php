@extends('backend.layouts.master')
@section('title', 'Warehouse Zones')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Warehouse Zones</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Warehouse Zones</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>All Warehouse Zones</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.warehouse-zones.create') }}" class="btn btn-primary">
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
