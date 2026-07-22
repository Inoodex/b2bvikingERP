@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Place</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Order Place</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Order Place</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>
                                    Create New</a>
                            </div>
                        </div>
                        <div class="table-responsive card-body">
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

    @if(session()->has('clear_booking_basket'))
    <script>
        localStorage.removeItem('booking_basket');
    </script>
    @endif

    <script>
         $(document).ready(function() {
             $('body').on('change', '.change-booking-status', function() {
                let status = $(this).val();
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.bookings.status-update') }}",
                    method: 'PUT',
                    data: {
                        status: status,
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message)
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })
        })
    </script>
@endpush
