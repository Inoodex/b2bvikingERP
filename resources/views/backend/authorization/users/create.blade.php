@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Create User')
@section('content')

    <section class="section">
        <div class="section-header">
            <h1>User</h1>
        </div>
        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create User</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Email</label>
                                        <input type="text" class="form-control" name="email"
                                            value="{{ old('email') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Image <code>(optional)</code> </label>
                                        <input type="file" class="form-control" name="image">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Role</label>
                                        <select id="inputState" class="form-control" name="user_role">
                                            <option value="">--select--</option>
                                            @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                     <div class="form-group col-md-6">
                                         <label for="customer_segment">Customer Segment / Tier</label>
                                         <select id="customer_segment" class="form-control" name="customer_segment">
                                             <option value="retail" selected>Retail Customer</option>
                                             <option value="wholesale">Wholesale Customer</option>
                                             <option value="b2b_vip">B2B VIP Partner</option>
                                             <option value="distributor">Distributor</option>
                                         </select>
                                     </div>
                                     <div class="form-group col-md-6">
                                         <label for="credit_limit">Credit Limit ({{ $settings->currency_icon ?? 'kr' }})</label>
                                         <input type="number" id="credit_limit" class="form-control" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit', '0.00') }}" placeholder="Enter maximum credit limit">
                                     </div>
                                     <div class="form-group col-md-12">
                                         <h6>User Level Discount Information</h6>
                                     </div>

                                    <div class="form-group col-md-6">
                                        <label for="discount_type">Discount Type</label>
                                        <select id="discount_type" class="form-control" name="discount_type">
                                            <option value="">No Discount</option>
                                            <option value="percent">Percentage (%)</option>
                                            <option value="flat">Flat Amount</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="discount_value">Discount Value</label>
                                        <input type="number" id="discount_value" class="form-control" name="discount_value"
                                               step="0.01" min="0" value="{{ old('discount_value') }}"
                                               placeholder="Enter discount value">
                                    </div>
                                    <div class="form-group col-md-6" id="min_order_group" style="display: none;">
                                        <label for="min_order_amount">Min Order Amount ({{ $settings->currency_icon ?? 'kr' }})</label>
                                        <input type="number" id="min_order_amount" class="form-control" name="min_order_amount"
                                               step="0.01" min="0" value="{{ old('min_order_amount') }}"
                                               placeholder="Minimum order amount for flat discount">
                                    </div>

                                </div>
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary px-4">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function toggleMinOrder() {
            let type = $('#discount_type').val();
            if (type === 'flat') {
                $('#min_order_group').show();
            } else {
                $('#min_order_group').hide();
                $('#min_order_amount').val(''); // Clear value when hidden
            }
        }

        $('#discount_type').on('change', function() {
            toggleMinOrder();
        });

        // Initialize on load
        toggleMinOrder();
    });
</script>
@endpush
