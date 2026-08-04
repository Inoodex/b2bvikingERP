@extends('backend.layouts.master')

@section('title', 'PR Pending Items Status Report')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-tasks text-primary mr-2"></i> PR Pending Items Status Report</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Reports</div>
            <div class="breadcrumb-item">PR Pending Status</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Pending Purchase Requisitions Overview</h4>
            </div>
            <div class="card-body">
                <form id="report-filter-form" action="javascript:void(0);" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <label class="font-weight-bold">Start Date:</label>
                            <input type="date" name="start_date" class="form-control filter-input">
                        </div>
                        <div class="col-md-5">
                            <label class="font-weight-bold">End Date:</label>
                            <input type="date" name="end_date" class="form-control filter-input">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-reset-filter" class="btn btn-outline-danger btn-block shadow-sm">
                                <i class="fas fa-undo-alt mr-1"></i> Reset Filters
                            </button>
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
    <script>
    $(document).ready(function() {
        $(document).on('change', '.filter-input', function() {
            window.LaravelDataTables['pr-status-table'].draw();
        });

        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#report-filter-form')[0].reset();
            window.LaravelDataTables['pr-status-table'].draw();
        });
    });
    </script>
@endpush
