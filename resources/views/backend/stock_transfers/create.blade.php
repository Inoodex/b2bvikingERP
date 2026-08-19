@extends('backend.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>New Stock Transfer</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></div>
            <div class="breadcrumb-item">Create</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.stock-transfers.store') }}" method="POST" id="transfer_form">
            @csrf
            <div class="row">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-route mr-2 text-primary"></i> Route & Logistics</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">From Warehouse (Source) <span class="text-danger">*</span></label>
                                <select name="from_outlet_id" id="from_outlet_select" class="form-control select2" required>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->outlet_name ?? $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">To Outlet / Branch (Destination) <span class="text-danger">*</span></label>
                                <select name="to_outlet_id" id="to_outlet_select" class="form-control select2" required>
                                    <option value="" disabled selected>Select Destination...</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->outlet_name ?? $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Vehicle / Truck No</label>
                                        <input type="text" name="vehicle_no" class="form-control" placeholder="e.g. DHAKA-METRO-1234">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Challan / Ref No</label>
                                        <input type="text" name="challan_no" class="form-control" placeholder="Optional">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Driver Name</label>
                                        <input type="text" name="driver_name" class="form-control" placeholder="Driver name">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark">Driver Phone</label>
                                        <input type="text" name="driver_phone" class="form-control" placeholder="Mobile no">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Transfer Notes</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Special handling or dispatch notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> Products to Transfer</h4>
                            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="add_transfer_row_btn">
                                <i class="fas fa-plus mr-1"></i> Add Product Line
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0" id="transfer_items_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="min-width: 250px;">Product</th>
                                            <th style="width: 120px;">Source Stock</th>
                                            <th style="width: 130px;">Transfer Qty <span class="text-danger">*</span></th>
                                            <th style="width: 150px;">Note</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="transfer_items_body">
                                        {{-- Dynamic Transfer Rows --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top text-right py-3">
                            <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Create Transfer (Draft)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Hidden Product Options Template --}}
<select id="transfer_product_template_select" style="display: none;">
    <option value="" disabled selected>Select Product...</option>
    @foreach($products as $p)
        @if($p->variants->isNotEmpty())
            @foreach($p->variants as $v)
                <option value="{{ $p->id }}" data-variant-id="{{ $v->id }}" data-price="{{ $p->purchase_price ?? $p->price }}">
                    {{ $p->name }} - {{ $v->color->name ?? '' }} {{ $v->size->name ?? '' }}
                </option>
            @endforeach
        @else
            <option value="{{ $p->id }}" data-variant-id="" data-price="{{ $p->purchase_price ?? $p->price }}">
                {{ $p->name }}
            </option>
        @endif
    @endforeach
</select>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        let transferRowIndex = 0;

        function addTransferRow() {
            let productOptions = $('#transfer_product_template_select').html();
            let rowHtml = `
                <tr id="trow_${transferRowIndex}">
                    <td>
                        <select name="items[${transferRowIndex}][product_id]" class="form-control select2-row product-select" required>
                            ${productOptions}
                        </select>
                        <input type="hidden" name="items[${transferRowIndex}][variant_id]" class="variant-id-input" value="">
                        <input type="hidden" name="items[${transferRowIndex}][unit_cost]" class="unit-cost-input" value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control source-stock-input text-center" readonly value="0.00" style="background: #f8fafc;">
                    </td>
                    <td>
                        <input type="number" step="any" min="0.01" name="items[${transferRowIndex}][qty]" class="form-control transfer-qty-input text-center font-weight-bold" required placeholder="Qty">
                    </td>
                    <td>
                        <input type="text" name="items[${transferRowIndex}][item_note]" class="form-control" placeholder="Item note">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-trow-btn"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#transfer_items_body').append(rowHtml);
            $('#trow_' + transferRowIndex + ' .select2-row').select2({ width: '100%' });
            transferRowIndex++;
        }

        addTransferRow();

        $('#add_transfer_row_btn').on('click', function () {
            addTransferRow();
        });

        $(document).on('click', '.remove-trow-btn', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '.product-select', function () {
            let row = $(this).closest('tr');
            let selectedOption = $(this).find('option:selected');
            let productId = selectedOption.val();
            let variantId = selectedOption.data('variant-id') || '';
            let fromOutletId = $('#from_outlet_select').val();

            row.find('.variant-id-input').val(variantId);

            if (productId) {
                $.ajax({
                    url: "{{ route('admin.stock-adjustments.get-item-stock') }}",
                    method: "GET",
                    data: {
                        product_id: productId,
                        variant_id: variantId,
                        outlet_id: fromOutletId
                    },
                    success: function (res) {
                        if (res.success) {
                            row.find('.source-stock-input').val(parseFloat(res.system_qty).toFixed(2));
                            row.find('.unit-cost-input').val(parseFloat(res.unit_cost).toFixed(2));
                        }
                    }
                });
            }
        });
    });
</script>
@endpush
