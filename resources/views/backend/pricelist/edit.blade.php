@extends('backend.layouts.master')

@section('title', 'Edit Customer Pricelist - ' . $pricelist->name)

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
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">Edit Customer Pricelist</h4>
                        <p class="text-muted mb-0 small">Update tier pricing items for {{ $pricelist->name }}</p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.pricelists.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="section-body">
            <form action="{{ route('admin.pricelists.update', $pricelist->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-primary"></i> Pricelist Header Info</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Pricelist Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $pricelist->name) }}" required style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Target Customer Segment <span class="text-danger">*</span></label>
                                        <select name="customer_segment" class="form-control" required style="border-radius: 8px;">
                                            <option value="wholesale" {{ $pricelist->customer_segment == 'wholesale' ? 'selected' : '' }}>Wholesale Customer</option>
                                            <option value="b2b_vip" {{ $pricelist->customer_segment == 'b2b_vip' ? 'selected' : '' }}>B2B VIP Partner</option>
                                            <option value="distributor" {{ $pricelist->customer_segment == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                            <option value="retail" {{ $pricelist->customer_segment == 'retail' ? 'selected' : '' }}>Retail Customer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Status</label>
                                        <select name="status" class="form-control" style="border-radius: 8px;">
                                            <option value="1" {{ $pricelist->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $pricelist->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Valid From (Optional)</label>
                                        <input type="date" name="valid_from" class="form-control" value="{{ $pricelist->valid_from ? $pricelist->valid_from->format('Y-m-d') : '' }}" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Valid To (Optional)</label>
                                        <input type="date" name="valid_to" class="form-control" value="{{ $pricelist->valid_to ? $pricelist->valid_to->format('Y-m-d') : '' }}" style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="font-weight-bold text-dark">Region / Location (Optional)</label>
                                        <input type="text" name="region" class="form-control" value="{{ old('region', $pricelist->region) }}" style="border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tier Price Items Grid --}}
                        <div class="card card-primary border-0 shadow-sm mb-4" style="border-radius: 16px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-layer-group mr-2 text-primary"></i> Tier Pricing Items Grid</h6>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="addPriceRow" style="border-radius: 8px;">
                                    <i class="fas fa-plus mr-1"></i> Add Tier Item
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="pricelistTable">
                                        <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                            <tr>
                                                <th style="width: 55%;">Product <span class="text-danger">*</span></th>
                                                <th style="width: 35%;">Tier Special Price (Base Currency) <span class="text-danger">*</span></th>
                                                <th style="width: 10%;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="priceRows">
                                            @foreach($pricelist->items as $index => $item)
                                                <tr class="price-row">
                                                    <td>
                                                        <select name="items[{{ $index }}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                                            <option value="">-- Select Product --</option>
                                                            @foreach($products as $prod)
                                                                <option value="{{ $prod->id }}" data-mrp="{{ $prod->price }}" {{ $item->product_id == $prod->id ? 'selected' : '' }}>
                                                                    {{ $prod->name }} (MRP: kr. {{ number_format($prod->price, 2) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][price]" class="form-control price-input" value="{{ $item->price }}" required style="border-radius: 8px;">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="border-radius: 6px;"><i class="fas fa-times"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top text-right p-4">
                                <button type="submit" class="btn btn-success px-5 py-2 font-weight-bold shadow-sm" style="border-radius: 10px;">
                                    <i class="fas fa-save mr-2"></i> Update Pricelist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let rowIdx = {{ count($pricelist->items) }};

            $('#addPriceRow').on('click', function() {
                let html = `
                    <tr class="price-row">
                        <td>
                            <select name="items[${rowIdx}][product_id]" class="form-control product-select" required style="border-radius: 8px;">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-mrp="{{ $prod->price }}">
                                        {{ $prod->name }} (MRP: kr. {{ number_format($prod->price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="items[${rowIdx}][price]" class="form-control price-input" placeholder="Enter special price" required style="border-radius: 8px;">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="border-radius: 6px;"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $('#priceRows').append(html);
                rowIdx++;
            });

            $(document).on('click', '.remove-row', function() {
                if ($('.price-row').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    toastr.warning('Pricelist must contain at least 1 tier price item.');
                }
            });

            $(document).on('change', '.product-select', function() {
                let mrp = $(this).find(':selected').data('mrp');
                if (mrp && !$(this).closest('tr').find('.price-input').val()) {
                    $(this).closest('tr').find('.price-input').val(parseFloat(mrp).toFixed(2));
                }
            });
        });
    </script>
    @endpush
@endsection
