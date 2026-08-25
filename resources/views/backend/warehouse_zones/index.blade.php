@extends('backend.layouts.master')
@section('title', 'Warehouse Zones')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Warehouse Zones</h4>
        <a href="{{ route('admin.warehouse-zones.create') }}" class="btn btn-primary">Create Zone</a>
    </div>
    <div class="card-body border-bottom py-3">
        {{ $dataTable->table() }}
    </div>
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
