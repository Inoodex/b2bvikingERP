@extends('backend.layouts.master')
@section('title', 'Create Delivery Order (Challan)')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.delivery-orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Create Delivery Order (Challan)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.delivery-orders.index') }}">Delivery Orders</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.delivery-orders.store') }}" method="POST" id="delivery-order-form">
                @csrf

                {{-- Centered Select Order Card --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary shadow-sm">
                            <div class="card-header text-center d-block py-3">
                                <h4 class="d-inline-block font-weight-bold"><i class="fas fa-search text-primary mr-2"></i>Select Order / Invoice to Fulfill</h4>
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
                                                    <option value="{{ $order->id }}" {{ (isset($selectedOrderId) && $selectedOrderId == $order->id) ? 'selected' : '' }}>
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

                {{-- Order Items & Logistics Card --}}
                <div class="card shadow-sm border-0 d-none" id="items-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="font-weight-bold text-dark"><i class="fas fa-boxes text-primary mr-2"></i>Select Items & Quantities to Dispatch</h4>
                    </div>
                    <div class="card-body">
                        {{-- Logistics & Shipping Info Inputs --}}
                        <div class="row bg-light p-3 rounded mb-4 mx-0">
                            <div class="col-md-4 col-12">
                                <div class="form-group mb-md-0">
                                    <label class="font-weight-bold"><i class="fas fa-shipping-fast text-primary mr-1"></i> Shipping Carrier / Logistics</label>
                                    <select name="carrier_name" class="form-control">
                                        <option value="DHL Express">DHL Express</option>
                                        <option value="PostNord">PostNord Logistics</option>
                                        <option value="DSV Freight">DSV Global Transport</option>
                                        <option value="FedEx">FedEx Express</option>
                                        <option value="Local Truck / Van" selected>Local Transport / Fleet Truck</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group mb-md-0">
                                    <label class="font-weight-bold"><i class="fas fa-barcode text-primary mr-1"></i> AWB / Tracking Number</label>
                                    <input type="text" name="awb_number" class="form-control" placeholder="e.g. AWB-982341823">
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold"><i class="fas fa-route text-primary mr-1"></i> Shipping Method</label>
                                    <select name="shipping_method" class="form-control">
                                        <option value="Express Road Freight" selected>Express Road Freight</option>
                                        <option value="Air Cargo">Standard Air Cargo</option>
                                        <option value="Sea Freight">Sea Container Freight</option>
                                        <option value="Customer Pickup">Direct Customer Pickup</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="delivery-items-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product / Variant</th>
                                        <th class="text-center" style="width: 100px;">Ordered</th>
                                        <th class="text-center" style="width: 100px;">Delivered</th>
                                        <th class="text-center" style="width: 120px;">Unit Price</th>
                                        <th class="text-center" style="width: 140px;">Dispatch Qty</th>
                                        <th class="text-right" style="width: 140px;">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Dynamically populated via AJAX --}}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right font-weight-bold">Notes / Driver Instructions:</th>
                                        <th colspan="2">
                                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Handle with care, deliver at rear warehouse gate">
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right bg-white">
                        <a href="{{ route('admin.delivery-orders.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i> Create Delivery Order (Challan)
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

            function loadOrderItems(orderId) {
                if (!orderId) {
                    $('#items-card').addClass('d-none');
                    $('#delivery-items-table tbody').html('');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.delivery-orders.get-order-items') }}",
                    type: "GET",
                    data: { order_id: orderId },
                    beforeSend: function() {
                        $('#delivery-items-table tbody').html('<tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Loading order items...</td></tr>');
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

                                var isDisabled = item.max_deliverable <= 0 ? 'disabled' : '';
                                var defaultQty = item.max_deliverable > 0 ? item.max_deliverable : 0;

                                html += '<tr>';
                                html += '<td><strong class="text-dark">' + item.product_name + '</strong>' + variantText + '<input type="hidden" name="items[' + index + '][order_item_id]" value="' + item.order_item_id + '"></td>';
                                html += '<td class="text-center font-weight-bold">' + item.ordered_qty + '</td>';
                                html += '<td class="text-center text-muted">' + item.already_delivered + '</td>';
                                html += '<td class="text-center">kr. ' + item.unit_price.toFixed(2) + '<input type="hidden" class="unit-price" value="' + item.unit_price + '"></td>';
                                html += '<td><input type="number" step="0.01" min="0" max="' + item.max_deliverable + '" name="items[' + index + '][qty]" class="form-control form-control-sm text-center dispatch-qty" value="' + defaultQty + '" ' + isDisabled + '></td>';
                                html += '<td class="text-right font-weight-bold line-total">kr. ' + (defaultQty * item.unit_price).toFixed(2) + '</td>';
                                html += '</tr>';
                            });
                            $('#delivery-items-table tbody').html(html);
                        } else {
                            $('#delivery-items-table tbody').html('<tr><td colspan="6" class="text-center text-danger py-4">No deliverable items found for this order.</td></tr>');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load order items. Please try again.';
                        $('#delivery-items-table tbody').html('<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle mr-1"></i> ' + errorMsg + '</td></tr>');
                    }
                });
            }

            $('#order_id').on('change', function() {
                loadOrderItems($(this).val());
            });

            if ($('#order_id').val()) {
                loadOrderItems($('#order_id').val());
            }

            $(document).on('input change', '.dispatch-qty', function() {
                var row = $(this).closest('tr');
                var qty = parseFloat($(this).val()) || 0;
                var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
                var maxQty = parseFloat($(this).attr('max')) || 0;

                if (qty > maxQty) {
                    qty = maxQty;
                    $(this).val(maxQty);
                }

                var lineTotal = qty * unitPrice;
                row.find('.line-total').text('kr. ' + lineTotal.toFixed(2));
            });

            $('#delivery-order-form').on('submit', function(e) {
                var hasQty = false;
                $('.dispatch-qty').each(function() {
                    if (parseFloat($(this).val()) > 0) {
                        hasQty = true;
                    }
                });

                if (!hasQty) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Dispatch Quantity Entered',
                            text: 'Please enter a dispatch quantity greater than 0 for at least one item before creating delivery order.',
                            confirmButtonColor: '#6777ef'
                        });
                    } else {
                        alert('Please enter a dispatch quantity greater than 0 for at least one item.');
                    }
                    return false;
                }
            });
        });
    </script>
@endpush
