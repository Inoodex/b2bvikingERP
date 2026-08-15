@extends('backend.layouts.master')

@section('title', 'Sales Orders (SO) Management')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-store-alt text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Sales Orders (SO)</h4>
                        <p class="text-muted mb-0 small">Manage commercial sales orders, credit limit holds, and fulfillment workflows</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.sales-orders.create') }}" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-plus mr-1"></i> Create Sales Order
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="section-body">
            <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center flex-wrap">
                    <h6 class="text-dark font-weight-bold mb-0 mr-4"><i class="fas fa-filter mr-2 text-primary"></i> Order Status Filters:</h6>
                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                        <a href="{{ route('admin.sales-orders.index') }}" class="btn {{ request('status_filter') == '' ? 'btn-primary' : 'btn-outline-primary' }} font-weight-bold">All Orders</a>
                        <a href="{{ route('admin.sales-orders.index', ['status_filter' => 'credit_hold']) }}" class="btn {{ request('status_filter') == 'credit_hold' ? 'btn-danger' : 'btn-outline-danger' }} font-weight-bold">
                            <i class="fas fa-lock mr-1"></i> Credit Hold
                        </a>
                        <a href="{{ route('admin.sales-orders.index', ['status_filter' => 'pending_approval']) }}" class="btn {{ request('status_filter') == 'pending_approval' ? 'btn-warning' : 'btn-outline-warning' }} font-weight-bold">Pending Approval</a>
                        <a href="{{ route('admin.sales-orders.index', ['status_filter' => 'approved']) }}" class="btn {{ request('status_filter') == 'approved' ? 'btn-info' : 'btn-outline-info' }} font-weight-bold">Approved</a>
                        <a href="{{ route('admin.sales-orders.index', ['status_filter' => 'delivered']) }}" class="btn {{ request('status_filter') == 'delivered' ? 'btn-success' : 'btn-outline-success' }} font-weight-bold">Delivered</a>
                    </div>
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
