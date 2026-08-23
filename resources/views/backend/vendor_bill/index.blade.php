@extends('backend.layouts.master')

@section('title', 'Vendor Bills (Invoices)')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Vendor Bills (3-Way Matching Invoices)</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.vendor-bills.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create Vendor Bill</a>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Procurement</div>
            <div class="breadcrumb-item">Vendor Bills</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>All Supplier Invoices & Debit Adjustments</h4>
                        <a href="{{ route('admin.vendor-bills.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> New Vendor Bill</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100']) }}
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
