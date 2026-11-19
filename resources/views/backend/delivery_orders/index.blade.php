@extends('backend.layouts.master')
@section('title', 'Delivery Orders (Challans)')

@push('css')
    <style>
        #delivery-order-table {
            min-width: 900px !important;
        }

        #delivery-order-table th,
        #delivery-order-table td {
            white-space: nowrap;
        }

        #delivery-order-table th:last-child,
        #delivery-order-table td:last-child {
            min-width: 150px;
        }

        #delivery-order-table_wrapper > .row:nth-child(2) {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            margin-bottom: 8px !important;
            scrollbar-width: auto;
            scrollbar-color: #2563eb #e2e8f0;
        }

        #delivery-order-table_wrapper > .row:nth-child(2)::-webkit-scrollbar {
            display: block;
            height: 12px;
        }

        #delivery-order-table_wrapper > .row:nth-child(2)::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 6px;
        }

        #delivery-order-table_wrapper > .row:nth-child(2)::-webkit-scrollbar-thumb {
            background: #2563eb;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
        }
    </style>
@endpush

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
