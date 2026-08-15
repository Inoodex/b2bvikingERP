@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Update User')
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
                            <h4>Update User</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.users.update', $user->id) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') ?? $user->name }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Email</label>
                                        <input type="text" class="form-control" name="email"
                                            value="{{ old('email') ?? $user->email }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone') ?? $user->phone }}">
                                    </div>
                                    {{-- <div class="form-group col-md-6">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password">
                                    </div> --}}
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Role</label>
                                        <select id="inputState" class="form-control" name="user_role">
                                            <option value="">--select--</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option {{ $user->status == 1 ? 'selected' : '' }} value="1">Active
                                            </option>
                                            <option {{ $user->status == 0 ? 'selected' : '' }} value="0">Inactive
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Image <code>(optional)</code> </label>
                                        <input type="file" class="form-control" name="image">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Image preview </label>
                                        <img src="{{ asset($user->image) }}" alt="" width="150px">
                                    </div>
                                     <div class="form-group col-md-6">
                                         <label for="customer_segment">Customer Segment / Tier</label>
                                         <select id="customer_segment" class="form-control" name="customer_segment">
                                             <option value="retail" {{ ($user->customer_segment ?? 'retail') == 'retail' ? 'selected' : '' }}>Retail Customer</option>
                                             <option value="wholesale" {{ ($user->customer_segment ?? '') == 'wholesale' ? 'selected' : '' }}>Wholesale Customer</option>
                                             <option value="b2b_vip" {{ ($user->customer_segment ?? '') == 'b2b_vip' ? 'selected' : '' }}>B2B VIP Partner</option>
                                             <option value="distributor" {{ ($user->customer_segment ?? '') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                         </select>
                                     </div>
                                     <div class="form-group col-md-6">
                                         <label for="credit_limit">Credit Limit ({{ $settings->currency_icon ?? 'kr' }})</label>
                                         <input type="number" id="credit_limit" class="form-control" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit') ?? $user->credit_limit }}" placeholder="Enter maximum credit limit">
                                     </div>
                                     <div class="form-group col-md-12">
                                         <h6>User Level Discount Information</h6>
                                     </div>
                                    <div class="row col-md-12">
                                        <div class="form-group col-md-6">
                                            <label for="discount_type">Discount Type</label>
                                            <select id="discount_type" class="form-control" name="discount_type">
                                                <option value="">No Discount</option>
                                                <option value="percent" {{ $user->discount_type == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="flat" {{ $user->discount_type == 'flat' ? 'selected' : '' }}>Flat Amount</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="discount_value">Discount Value</label>
                                            <input type="number" id="discount_value" class="form-control" name="discount_value"
                                                   step="0.01" min="0" value="{{ old('discount_value') ?? $user->discount_value }}"
                                                   placeholder="Enter discount value">
                                        </div>
                                        <div class="form-group col-md-6" id="min_order_group" style="display: none;">
                                            <label for="min_order_amount">Min Order Amount ({{ $settings->currency_icon ?? 'kr' }})</label>
                                            <input type="number" id="min_order_amount" class="form-control" name="min_order_amount"
                                                   step="0.01" min="0" value="{{ old('min_order_amount') ?? $user->min_order_amount }}"
                                                   placeholder="Minimum order amount for flat discount">
                                        </div>
                                    </div>

                                </div>
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary px-4">Update</button>
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
                // We keep the value here in edit mode but it won't be used by the backend for non-flat
            }
        }

        $('#discount_type').on('change', function() {
            toggleMinOrder();
            if ($('#discount_type').val() !== 'flat') {
                $('#min_order_amount').val('');
            }
        });

        // Initialize on load
        toggleMinOrder();
    });
</script>
@endpush
