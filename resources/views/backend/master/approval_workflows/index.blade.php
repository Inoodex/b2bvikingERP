@extends('backend.layouts.master')

@section('title', 'Approval Workflows')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Approval Workflows</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>All Approval Workflows</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.approval-workflows.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        {{ $dataTable->table() }}
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
