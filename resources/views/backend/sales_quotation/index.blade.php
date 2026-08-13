@extends('backend.layouts.master')

@section('title', 'Sales Quotations')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-file-signature text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Sales Quotations Management</h4>
                        <p class="text-muted mb-0 small">Create B2B customer quotes, export PDFs & convert to Sales Orders in 1-click</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.sales-quotations.create') }}" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 10px; background: #2563eb; border: none;">
                        <i class="fas fa-plus mr-1"></i> Create Quotation
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-list mr-2 text-primary"></i> All Sales Quotations
                                </h6>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-hover align-middle mb-0 w-100', 'id' => 'sales-quotation-table']) }}
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
