@extends('backend.layouts.master')
@section('title', 'Create Customer Return')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.sales-returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Create Customer Return (RMA)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales-returns.index') }}">Customer Returns</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.sales-returns.store') }}" method="POST" id="sales-return-form">
                @csrf

                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary shadow-sm">
                            <div class="card-header text-center d-block py-3">
                                <h4 class="d-inline-block font-weight-bold"><i class="fas fa-search text-primary mr-2"></i>Select Order / Invoice to Process Return</h4>
                            </div>
                            <div class="card-body py-4">
                                <div class="row justify-content-center">
                                    <div class="col-md-8 col-12 text-center">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold d-block h6 text-dark mb-2">
                                                <i class="fas fa-file-invoice text-primary mr-1"></i> Select Commercial Order / Invoice <span class="text-danger">*</span>
                                            </label>
                                            <select name="order_id" id="order_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Approved / Completed Order --</option>
                                                @foreach ($orders as $order)
                                                    <option value="{{ $order->id }}">
                                                        Order #{{ $order->order_no }} — {{ $order->user ? ($order->user->outlet_name ?: $order->user->name) : 'Guest' }} (Total: kr. {{ number_format((float)$order->total_amount, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Items Table Card --}}
                <div class="card shadow-sm border-0 d-none" id="items-card">
                    <div class="card-header bg-white">
                        <h4 class="font-weight-bold text-dark"><i class="fas fa-list-alt text-primary mr-2"></i>Select Items & Quantities to Return</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="return-items-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product / Variant</th>
                                        <th class="text-center" style="width: 90px;">Ordered</th>
                                        <th class="text-center" style="width: 90px;">Returned</th>
                                        <th class="text-center" style="width: 110px;">Unit Price</th>
                                        <th class="text-center" style="width: 120px;">Return Qty</th>
                                        <th style="width: 220px;">Warehouse Stock Action</th>
                                        <th>Return Reason</th>
                                        <th class="text-right" style="width: 130px;">Refund Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Dynamically populated via AJAX --}}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-right font-weight-bold">Grand Total Refund Amount:</th>
                                        <th class="text-right font-weight-bold text-success h5 mb-0" id="grand-refund-total">kr. 0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right bg-white">
                        <a href="{{ route('admin.sales-returns.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i> Submit Return Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#order_id').select2();
            }

            $('#order_id').on('change', function() {
                var orderId = $(this).val();
                if (!orderId) {
                    $('#items-card').addClass('d-none');
                    $('#return-items-table tbody').html('');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.sales-returns.get-order-items') }}",
                    type: "GET",
                    data: { order_id: orderId },
                    beforeSend: function() {
                        $('#return-items-table tbody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Loading order items...</td></tr>');
                        $('#items-card').removeClass('d-none');
                    },
                    success: function(response) {
                        if (response.success && response.items.length > 0) {
                            var html = '';
                            $.each(response.items, function(index, item) {
                                var variantText = '';
                                if (item.variant_name || item.color_name || item.size_name) {
                                    var parts = [];
                                    if (item.color_name) parts.push('Color: ' + item.color_name);
                                    if (item.size_name) parts.push('Size: ' + item.size_name);
                                    if (item.variant_name) parts.push(item.variant_name);
                                    variantText = ' <small class="text-muted">(' + parts.join(', ') + ')</small>';
                                }

                                var isDisabled = item.max_returnable <= 0 ? 'disabled' : '';

                                html += '<tr>';
                                html += '<td><strong class="text-dark">' + item.product_name + '</strong>' + variantText + '<input type="hidden" name="items[' + index + '][order_item_id]" value="' + item.order_item_id + '"></td>';
                                html += '<td class="text-center font-weight-bold">' + item.ordered_qty + '</td>';
                                html += '<td class="text-center text-muted">' + item.already_returned + '</td>';
                                html += '<td class="text-center">kr. ' + item.unit_price.toFixed(2) + '<input type="hidden" class="unit-price" value="' + item.unit_price + '"></td>';
                                html += '<td><input type="number" step="0.01" min="0" max="' + item.max_returnable + '" name="items[' + index + '][qty]" class="form-control form-control-sm text-center return-qty" value="0" ' + isDisabled + '></td>';
                                html += '<td><select name="items[' + index + '][disposition]" class="form-control form-control-sm" ' + isDisabled + '><option value="restock">📦 Restock to Inventory</option><option value="scrap">🗑️ Scrap (Damaged in Transit)</option><option value="rtv">🔁 Return to Vendor (RTV)</option><option value="quarantine">🔬 Quarantine (Inspection)</option></select></td>';
                                html += '<td><input type="text" name="items[' + index + '][reason]" class="form-control form-control-sm" placeholder="e.g. Damaged, Defective" ' + isDisabled + '></td>';
                                html += '<td class="text-right font-weight-bold line-refund">kr. 0.00</td>';
                                html += '</tr>';
                            });
                            $('#return-items-table tbody').html(html);
                            calculateTotal();
                        } else {
                            $('#return-items-table tbody').html('<tr><td colspan="8" class="text-center text-danger py-4">No returnable items found for this order.</td></tr>');
                        }
                    },
                    error: function() {
                        alert('Failed to load order items. Please try again.');
                    }
                });
            });

            $(document).on('input change', '.return-qty', function() {
                var row = $(this).closest('tr');
                var qty = parseFloat($(this).val()) || 0;
                var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
                var maxQty = parseFloat($(this).attr('max')) || 0;

                if (qty > maxQty) {
                    qty = maxQty;
                    $(this).val(maxQty);
                }

                var lineTotal = qty * unitPrice;
                row.find('.line-refund').text('kr. ' + lineTotal.toFixed(2));
                calculateTotal();
            });

            function calculateTotal() {
                var grandTotal = 0;
                $('.return-qty').each(function() {
                    var row = $(this).closest('tr');
                    var qty = parseFloat($(this).val()) || 0;
                    var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
                    grandTotal += (qty * unitPrice);
                });
                $('#grand-refund-total').text('kr. ' + grandTotal.toFixed(2));
            }

            $('#sales-return-form').on('submit', function(e) {
                var hasQty = false;
                $('.return-qty').each(function() {
                    if (parseFloat($(this).val()) > 0) {
                        hasQty = true;
                    }
                });

                if (!hasQty) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Return Quantity Entered',
                            text: 'Please enter a return quantity greater than 0 for at least one item before submitting.',
                            confirmButtonColor: '#6777ef'
                        });
                    } else {
                        alert('Please enter a return quantity greater than 0 for at least one item before submitting.');
                    }
                    return false;
                }
            });
        });
    </script>
@endpush
