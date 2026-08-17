@extends('backend.layouts.master')
@section('title', 'Delivery Orders (Challans)')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Delivery Orders (Challans)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Delivery Orders</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-truck mr-2"></i>Outbound Delivery Orders & Packing Slips</h4>
                    <a href="{{ route('admin.delivery-orders.create') }}" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Create Delivery Order
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
