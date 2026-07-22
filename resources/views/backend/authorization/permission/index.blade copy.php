@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Permission')
@section('content')
    <!-- Main Content -->

    <section class="section">
        <div class="section-header">
            <h1>Permission</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Permission</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.permission.create') }}" class="btn btn-primary"><i
                                        class="fas fa-plus"></i> Create New</a>
                            </div>
                        </div>
                        <div class="table-responsive card-body">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'permission-table']) }}
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
