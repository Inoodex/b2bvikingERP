@extends('backend.layouts.master')

@section('title')
Vendor
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Update Vendor</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Update Vendor</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.vendor.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.vendor.update', $vendor->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" class="form-control" name="shop_name" value="{{ $vendor->shop_name }}">
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" value="{{ $vendor->phone }}">
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="{{ $vendor->email }}">
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address" value="{{ $vendor->address }}">
                            </div>

                            <div class="form-group">
                                <label>Country</label>
                                <select name="country" class="form-control select2">
                                <option value="">Select</option>
                                @foreach (config('settings.country_list') as $country)
                                <option {{ $country === $vendor->country ? 'selected' : '' }} value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            </div>

                             <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Currency</label>
                                        <select name="currency_id" id="currency_select" class="form-control select2 @error('currency_id') is-invalid @enderror"
                                                style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                                            <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}" 
                                                    data-icon="{{ $currency->symbol }}" 
                                                    data-rate="{{ $currency->exchange_rate }}"
                                                    {{ (old('currency_id') ?? $vendor->currency_id) == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }} ({{ $currency->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('currency_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Currency Icon</label>
                                    <div class="h4" id="currency_icon_display">{{ $vendor->currency_icon ?? '-' }}</div>
                                </div>

                             </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description">{{ $vendor->description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option {{ $vendor->status == 1 ? 'selected' : '' }} value="1">Active</option>
                                    <option {{ $vendor->status == 0 ? 'selected' : '' }} value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-4">Update</button>
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
            $('#currency_select').on('change', function(e, init) {
                let icon = $(this).find(':selected').data('icon');
                $('#currency_icon_display').text(icon || '—');
            });
            
            // Trigger on load for initialization
            if ($('#currency_select').val()) {
                $('#currency_select').trigger('change', [true]);
            }
        });
    </script>
@endpush
