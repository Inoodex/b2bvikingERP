@extends('backend.layouts.master')
@section('title', 'Warehouse Bins')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Warehouse Bins (Micro-Locations)</h4>
        <a href="{{ route('admin.warehouse-bins.create') }}" class="btn btn-primary">Create Bin</a>
    </div>
    <div class="card-body border-bottom py-3">
        {{ $dataTable->table() }}
    </div>
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
