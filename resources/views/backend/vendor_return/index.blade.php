@extends('backend.layouts.master')

@section('title', 'Vendor Returns & Debit Notes')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Vendor Returns & Debit Notes</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Procurement</div>
            <div class="breadcrumb-item">Vendor Returns</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-undo text-primary mr-2"></i> All Vendor Returns & Debit Notes</h4>
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
