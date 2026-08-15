@extends('backend.layouts.master')

@section('title', 'Customer Pricelists & Dynamic Pricing Tiers')

@section('content')
    <section class="section">
        {{-- Section Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-tags text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Customer Pricelists</h4>
                        <p class="text-muted mb-0 small">Manage dynamic tier prices per customer segment (Retail, Wholesale, B2B VIP, Distributor)</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.pricelists.create') }}" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-plus mr-1"></i> Create Pricelist
                    </a>
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="card card-primary border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-list-alt mr-2 text-primary"></i> Active Tier Pricelists</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-striped table-hover w-100']) }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            $('body').on('change', '.change-pricelist-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.pricelists.change-status') }}",
                    method: 'PUT',
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: isChecked,
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message);
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Failed to update status.');
                    }
                });
            });
        });
    </script>
@endpush
