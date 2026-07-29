@extends('backend.layouts.master')
@section('title', 'Letter of Credit (LC) Register')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Letter of Credit (LC) Register</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Procurement</a></div>
                <div class="breadcrumb-item">LC Register</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary border shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark font-weight-bold">
                                <i class="fas fa-university text-primary mr-2"></i> All Registered Import Letters of Credit
                            </h5>
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
    {{ $dataTable->scripts() }}
@endpush
