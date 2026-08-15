@extends('backend.layouts.master')

@section('title', 'Edit Coupon Code - ' . $coupon->code)

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-edit text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Edit Coupon Code</h4>
                        <p class="text-muted mb-0 small">Update coupon rules for {{ $coupon->code }}</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-ticket-alt mr-2 text-primary"></i> Edit Coupon Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Coupon Code <span class="text-danger">*</span></label>
                                        <input type="text" name="code" class="form-control text-uppercase font-weight-bold" value="{{ old('code', $coupon->code) }}" required style="border-radius: 8px; letter-spacing: 1px;">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold text-dark">Linked Discount Rule <span class="text-danger">*</span></label>
                                        <select name="discount_id" class="form-control" required style="border-radius: 8px;">
                                            <option value="">-- Select Discount Rule --</option>
                                            @foreach($discounts as $disc)
                                                <option value="{{ $disc->id }}" {{ $coupon->discount_id == $disc->id ? 'selected' : '' }}>
                                                    {{ $disc->name }} ({{ $disc->discount_type === 'flat' ? 'kr. ' . $disc->discount_value : $disc->discount_value . '%' }} OFF)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Usage Limit (Optional)</label>
                                        <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1" style="border-radius: 8px;">
                                        <small class="form-text text-muted">Currently Used: {{ $coupon->used_count }} times</small>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Expiration Date (Optional)</label>
                                        <input type="date" name="expires_at" class="form-control" value="{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '' }}" style="border-radius: 8px;">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Status</label>
                                        <select name="status" class="form-control" style="border-radius: 8px;">
                                            <option value="1" {{ $coupon->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $coupon->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="text-right border-top pt-4 mt-3">
                                    <button type="submit" class="btn btn-success px-5 py-2 font-weight-bold shadow-sm" style="border-radius: 10px;">
                                        <i class="fas fa-save mr-2"></i> Update Coupon Code
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
