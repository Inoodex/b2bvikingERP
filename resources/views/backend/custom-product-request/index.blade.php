@extends('backend.layouts.master')
@section('title', 'Custom Product Requests')

@push('css')
{{-- <style>
    .request-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #6777ef;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
</style> --}}
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Custom Product Requests</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Custom Product Requests</h4>
                            {{-- <div class="card-header-action">
                                @can('Create Custom Product Requests')
                                    <a href="{{ route('admin.custom-product-requests.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create New
                                    </a>
                                @endcan
                            </div> --}}
                        </div>
                        <div class="table-responsive card-body">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'custom-product-request-table']) }}
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
