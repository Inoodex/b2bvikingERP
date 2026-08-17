@extends('backend.layouts.master')

@section('title', 'Commercial Sales Invoices')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Commercial Sales Invoices</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Sales Invoices</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-list mr-2"></i>Sales Invoice List</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.sales-invoices.create') }}" class="btn btn-primary font-weight-bold">
                                <i class="fas fa-plus mr-1"></i> Create Sales Invoice
                            </a>
                        </div>
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
