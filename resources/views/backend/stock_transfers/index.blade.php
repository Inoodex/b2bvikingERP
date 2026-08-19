@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-truck-moving mr-2 text-primary"></i> Stock Transfers</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Stock Transfers</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="text-dark font-weight-bold mb-0">Inter-Warehouse / Outlet Stock Transfers</h4>
                        <a href="{{ route('admin.stock-transfers.create') }}" class="btn btn-primary shadow-sm font-weight-bold px-4">
                            <i class="fas fa-plus mr-1"></i> New Transfer
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{ $dataTable->table(['class' => 'table table-hover table-striped w-100']) }}
                        </div>
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
