@extends('backend.layouts.master')

@section('title', 'Payment Vouchers')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-receipt text-primary mr-2"></i> Purchase Payment Vouchers</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Accounts Payable</div>
            <div class="breadcrumb-item">Payment Vouchers</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Supplier Payment Voucher Registry</h4>
                        <a href="{{ route('admin.purchase-payments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Record Payment Voucher
                        </a>
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
