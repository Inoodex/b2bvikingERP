@extends('backend.layouts.master')
@section('title', 'Request For Quotations (RFQ)')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Request For Quotations (RFQ)</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All RFQs</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.rfqs.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New RFQ</a>
                            </div>
                        </div>
                            <div class="table-responsive">
                                {{ $dataTable->table() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
