@extends('backend.layouts.master')

@section('title', 'Item-wise Purchase Value Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-boxes text-primary mr-2"></i> Item-wise Purchase Value Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">Item Purchase</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Item-wise Purchase Metrics</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.purchase-reports.item-wise') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>End Date:</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>Supplier:</label>
                            <select name="vendor_id" class="form-control select2">
                                <option value="">-- All Suppliers --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ (isset($filters['vendor_id']) && $filters['vendor_id'] == $v->id) ? 'selected' : '' }}>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('admin.purchase-reports.item-wise') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100']) !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
@endpush
