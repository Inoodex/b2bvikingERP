@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Update User')
@section('content')

    <section class="section">
        <div class="section-header">
            <h1>User & Entity Management</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 16px;">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-user-edit mr-2 text-primary"></i> Update User Profile</h5>
                            <div class="card-header-action">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary font-weight-bold" style="border-radius: 8px;">
                                    <i class="fas fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.users.update', $user->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                
                                {{-- Core Profile Info --}}
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" required value="{{ old('name') ?? $user->name }}" style="border-radius: 8px;">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" required value="{{ old('email') ?? $user->email }}" style="border-radius: 8px;">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Phone</label>
                                        <input type="text" class="form-control" name="phone" value="{{ old('phone') ?? $user->phone }}" style="border-radius: 8px;">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Role & Permission Group <span class="text-danger">*</span></label>
                                        <select id="userRoleSelect" class="form-control select2" name="user_role" required style="border-radius: 8px;">
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" data-permissions-count="{{ $role->permissions_count }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Status</label>
                                        <select class="form-control" name="status" style="border-radius: 8px;">
                                            <option {{ $user->status == 1 ? 'selected' : '' }} value="1">Active</option>
                                            <option {{ $user->status == 0 ? 'selected' : '' }} value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Profile Image</label>
                                        <input type="file" class="form-control" name="image" style="border-radius: 8px;">
                                        @if($user->image)
                                            <div class="mt-2">
                                                <img src="{{ asset($user->image) }}" alt="User" width="60px" height="60px" class="rounded-circle shadow-sm">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Section A: Internal Enterprise Assignment (Visible for Staff / Manager / Admin / Outlet User) --}}
                                <div id="internalStaffSection" class="mt-3 p-3 bg-light rounded" style="border: 1px dashed #cbd5e1; border-radius: 12px;">
                                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-building mr-1"></i> Internal Enterprise Organization & Location</h6>
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label class="font-weight-bold text-dark">Belongs to Company</label>
                                            <select name="company_id" class="form-control" style="border-radius: 8px;">
                                                <option value="">-- Global / All Companies --</option>
                                                @foreach($companies as $comp)
                                                    <option value="{{ $comp->id }}" {{ $user->company_id == $comp->id ? 'selected' : '' }}>
                                                        {{ $comp->name }} ({{ $comp->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="font-weight-bold text-dark">Department</label>
                                            <select name="department_id" class="form-control" style="border-radius: 8px;">
                                                <option value="">-- No Department --</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}" {{ $user->department_id == $dept->id ? 'selected' : '' }}>
                                                        {{ $dept->name }} ({{ $dept->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="font-weight-bold text-dark">Assigned Outlet / Hub</label>
                                            <select name="outlet_id" class="form-control" style="border-radius: 8px;">
                                                <option value="">-- Central / Head Office --</option>
                                                @foreach($outlets as $out)
                                                    <option value="{{ $out->id }}" {{ $user->outlet_id == $out->id ? 'selected' : '' }}>
                                                        {{ $out->name }} ({{ ucfirst($out->type) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Section B: Commercial B2B Customer Settings (Visible for External Customers) --}}
                                <div id="b2bCustomerSection" class="mt-3 p-3 bg-light rounded" style="border: 1px dashed #93c5fd; border-radius: 12px;">
                                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-briefcase mr-1"></i> Commercial B2B Customer Settings</h6>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label class="font-weight-bold text-dark" for="customer_segment">Customer Segment / Tier</label>
                                            <select id="customer_segment" class="form-control" name="customer_segment" style="border-radius: 8px;">
                                                <option value="wholesale" {{ ($user->customer_segment ?? '') == 'wholesale' ? 'selected' : '' }}>Wholesale Customer (Default B2B)</option>
                                                <option value="b2b_vip" {{ ($user->customer_segment ?? '') == 'b2b_vip' ? 'selected' : '' }}>B2B VIP Partner (Tier 1)</option>
                                                <option value="distributor" {{ ($user->customer_segment ?? '') == 'distributor' ? 'selected' : '' }}>Distributor (Tier 2)</option>
                                                <option value="retail" {{ ($user->customer_segment ?? 'retail') == 'retail' ? 'selected' : '' }}>Retail Walk-in Customer</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="font-weight-bold text-dark" for="credit_limit">Credit Limit ({{ $settings->currency_icon ?? 'kr' }})</label>
                                            <input type="number" id="credit_limit" class="form-control" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit') ?? $user->credit_limit }}" placeholder="Enter maximum credit limit" style="border-radius: 8px;">
                                        </div>
                                    </div>

                                    <h6 class="font-weight-bold text-dark mt-2 mb-2 small text-uppercase" style="letter-spacing: 0.5px;">Customer Specific Discount Override</h6>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label class="font-weight-bold text-dark" for="discount_type">Discount Type</label>
                                            <select id="discount_type" class="form-control" name="discount_type" style="border-radius: 8px;">
                                                <option value="">No Discount Override</option>
                                                <option value="percent" {{ $user->discount_type == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="flat" {{ $user->discount_type == 'flat' ? 'selected' : '' }}>Flat Amount</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="font-weight-bold text-dark" for="discount_value">Discount Value</label>
                                            <input type="number" id="discount_value" class="form-control" name="discount_value"
                                                   step="0.01" min="0" value="{{ old('discount_value') ?? $user->discount_value }}"
                                                   placeholder="Enter discount value" style="border-radius: 8px;">
                                        </div>
                                        <div class="form-group col-md-6" id="min_order_group" style="display: none;">
                                            <label class="font-weight-bold text-dark" for="min_order_amount">Min Order Amount ({{ $settings->currency_icon ?? 'kr' }})</label>
                                            <input type="number" id="min_order_amount" class="form-control" name="min_order_amount"
                                                   step="0.01" min="0" value="{{ old('min_order_amount') ?? $user->min_order_amount }}"
                                                   placeholder="Minimum order amount for flat discount" style="border-radius: 8px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-4">
                                    <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 10px;">
                                        <i class="fas fa-save mr-1"></i> Update User Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function toggleSections() {
            let selectedOption = $('#userRoleSelect option:selected');
            let permCount = parseInt(selectedOption.data('permissions-count')) || 0;
            let val = selectedOption.val();

            if (val && permCount > 0) {
                // Dynamic Internal Staff with backend permissions
                $('#b2bCustomerSection').hide();
                $('#internalStaffSection').show();
            } else {
                // Commercial Customer with 0 backend permissions
                $('#b2bCustomerSection').show();
                $('#internalStaffSection').hide();
            }
        }

        function toggleMinOrder() {
            let type = $('#discount_type').val();
            if (type === 'flat') {
                $('#min_order_group').show();
            } else {
                $('#min_order_group').hide();
            }
        }

        $('#userRoleSelect').on('change', toggleSections);
        $('#discount_type').on('change', toggleMinOrder);

        // Run on page load
        toggleSections();
        toggleMinOrder();
    });
</script>
@endpush
