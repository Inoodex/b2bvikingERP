@extends('backend.layouts.master')

@section('title', 'Customer Payments & Receipts')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>💳 Customer Payments & Receipt Vouchers</h1>
        <a href="{{ route('admin.customer-payments.create') }}" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius: 6px;">
            <i class="fas fa-plus mr-1"></i> Record Customer Payment
        </a>
    </div>

    <div class="section-body">
        <div class="card card-primary shadow-sm">
            <div class="card-header">
                <h4>Customer Payment Receipts Registry</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-striped table-hover w-100']) }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
