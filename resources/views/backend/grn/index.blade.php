@extends('backend.layouts.master')

@section('title', 'Goods Received Notes (GRN)')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Goods Received Notes (GRN) & Quality Control</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Store & Warehouse</div>
            <div class="breadcrumb-item">GRN Register</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-dolly text-primary mr-2"></i> All Goods Receipt Records</h4>
                        <a href="{{ route('admin.goods-receipts.create') }}" class="btn btn-primary btn-round">
                            <i class="fas fa-plus-circle mr-1"></i> Receive Goods (Create GRN)
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{ $dataTable->table(['class' => 'table table-striped table-hover w-100']) }}
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
