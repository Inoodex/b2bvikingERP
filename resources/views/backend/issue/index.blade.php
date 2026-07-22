@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Stock Issues</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Stock Issues</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Stock Issues</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.issues.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Issue</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-issues">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Issue No</th>
                                            <th>Note</th>
                                            <th>Total Qty</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($issues as $issue)
                                            <tr>
                                                <td>{{ $issue->created_at->format('Y-m-d') }}</td>
                                                <td>{{ $issue->issue_no }}</td>
                                                <td>{{ $issue->note }}</td>
                                                <td>{{ $issue->total_qty }}</td>
                                                <td>
                                                    <div class="badge badge-success">{{ ucfirst($issue->status) }}</div>
                                                </td>
                                                <td>
                                                    {{-- <a href="{{ route('admin.issues.show', $issue->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a> --}}
                                                    <a href="{{ route('admin.issues.view-invoice', $issue->id) }}" target="_blank" class="btn btn-warning btn-sm"><i class="fas fa-file-invoice"></i></a>
                                                    <a href="{{ route('admin.issues.download-invoice', $issue->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-download"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $("#table-issues").dataTable({
            "columnDefs": [
                { "sortable": false, "targets": [5] }
            ],
            "order": [[0, "desc"]]
        });
    </script>
@endpush
