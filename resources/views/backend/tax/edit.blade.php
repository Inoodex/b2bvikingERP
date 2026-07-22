@extends('backend.layouts.master')
@section('title', 'Tax / VAT')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Tax / VAT</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Tax / VAT Rule</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.taxes.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.taxes.update', $tax->id) }}" method="post">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Rule Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $tax->name) }}" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Type</label>
                                        <select class="form-control" id="tax_type" name="type" required>
                                            <option value="percent" {{ old('type', $tax->type) == 'percent' ? 'selected' : '' }}>Percent</option>
                                            <option value="flat" {{ old('type', $tax->type) == 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label id="tax_value_label">Value (%)</label>
                                        <input type="number" step="0.01" min="0" class="form-control" name="value"
                                            value="{{ old('value', $tax->value) }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Set as Default</label>
                                        <select class="form-control" name="is_default">
                                            <option value="0" {{ old('is_default', $tax->is_default ? '1' : '0') == '0' ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('is_default', $tax->is_default ? '1' : '0') == '1' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Status</label>
                                        <select class="form-control" name="status" required>
                                            <option value="1" {{ old('status', $tax->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $tax->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
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
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function updateValueLabel() {
                const type = $('#tax_type').val();
                $('#tax_value_label').text(type === 'percent' ? 'Value (%)' : 'Value (Flat)');
            }

            $('#tax_type').on('change', updateValueLabel);
            updateValueLabel();
        });
    </script>
@endpush

