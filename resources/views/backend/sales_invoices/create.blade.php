@extends('backend.layouts.master')

@section('title', 'Create Commercial Sales Invoice')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Create Commercial Sales Invoice</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.sales-invoices.index') }}">Sales Invoices</a></div>
            <div class="breadcrumb-item active">Create</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.sales-invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-search mr-2"></i>Select Sales Order or Delivery Order</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Select Delivery Order (Challan) <span class="text-muted">(Recommended)</span></label>
                                        <select class="form-control select2" id="delivery_order_id_select" name="delivery_order_id">
                                            <option value="">-- Choose Dispatched Challan --</option>
                                            @foreach ($deliveryOrders as $do)
                                                <option value="{{ $do->id }}" {{ $selectedDeliveryOrderId == $do->id ? 'selected' : '' }}>
                                                    #{{ $do->delivery_no }} (Order #{{ $do->order ? $do->order->order_no : '' }} - {{ $do->order && $do->order->user ? ($do->order->user->outlet_name ?: $do->order->user->name) : 'Guest' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Or Select Sales Order</label>
                                        <select class="form-control select2" id="order_id_select" name="order_id">
                                            <option value="">-- Choose Approved Sales Order --</option>
                                            @foreach ($orders as $o)
                                                <option value="{{ $o->id }}" {{ $selectedOrderId == $o->id ? 'selected' : '' }}>
                                                    #{{ $o->order_no }} ({{ $o->user ? ($o->user->outlet_name ?: $o->user->name) : 'Guest' }} - Total: kr. {{ number_format((float)$o->total_amount, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Invoice Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Payment Due Date</label>
                                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Initial Status</label>
                                        <select name="status" class="form-control font-weight-bold">
                                            <option value="draft">Draft (Review & Post Later)</option>
                                            <option value="posted">Posted (Direct GL Entry)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preloaded Item Grid -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4><i class="fas fa-boxes mr-2"></i>Invoice Line Items & Billing Calculation</h4>
                        </div>
                        <div class="card-body">
                            <div id="itemsContainer" class="table-responsive">
                                <table class="table table-bordered table-striped" id="invoiceItemsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product / Variant</th>
                                            <th class="text-center" style="width: 120px;">Qty</th>
                                            <th class="text-right" style="width: 160px;">Unit Price</th>
                                            <th class="text-right" style="width: 180px;">Line Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItemsBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle mr-1"></i> Please select a Delivery Order or Sales Order above to load item details.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold h6">Subtotal:</td>
                                            <td class="text-right font-weight-bold h6 text-dark" id="calcSubtotal">kr. 0.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">Discount Amount:</td>
                                            <td class="text-right">
                                                <input type="number" step="0.01" min="0" name="discount_amount" id="discountInput" class="form-control text-right" value="{{ number_format((float)(isset($preloadedOrder) ? $preloadedOrder->discount_amount : (isset($preloadedDeliveryOrder->order) ? $preloadedDeliveryOrder->order->discount_amount : 0)), 2, '.', '') }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">VAT Tax %:</td>
                                            <td class="text-right">
                                                <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="taxRateInput" class="form-control text-right" value="{{ number_format((float)(isset($preloadedOrder) && $preloadedOrder->vat_rate !== null ? $preloadedOrder->vat_rate : ($defaultTaxRate ?? 0.00)), 2, '.', '') }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold h5 text-primary">Invoice Total:</td>
                                            <td class="text-right font-weight-bold h5 text-primary" id="calcTotal">kr. 0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="form-group mt-3">
                                <label class="font-weight-bold">Invoice Notes / Bank Wire Instructions</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Payment due within 30 days via wire transfer to IBAN DK1234567890. Quote invoice number."></textarea>
                            </div>

                            <div class="text-right mt-4">
                                <a href="{{ route('admin.sales-invoices.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" class="btn btn-success font-weight-bold px-4" style="border-radius: 6px;">
                                    <i class="fas fa-check-circle mr-1"></i> Issue Commercial Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let rawSubtotal = 0;

        function calculateTotals() {
            let discount = parseFloat($('#discountInput').val()) || 0;
            let taxRate = parseFloat($('#taxRateInput').val()) || 0;

            let taxableBase = Math.max(0, rawSubtotal - discount);
            let taxAmount = (taxableBase * taxRate) / 100;
            let grandTotal = taxableBase + taxAmount;

            $('#calcSubtotal').text('kr. ' + rawSubtotal.toFixed(2));
            $('#calcTotal').text('kr. ' + grandTotal.toFixed(2));
        }

        $('#discountInput, #taxRateInput').on('input change', function() {
            calculateTotals();
        });

        function fetchInvoiceItems(params) {
            $.ajax({
                url: "{{ route('admin.sales-invoices.get-items') }}",
                type: "GET",
                data: params,
                beforeSend: function() {
                    $('#invoiceItemsBody').html('<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Loading items...</td></tr>');
                },
                success: function(res) {
                    if (res.success && res.items && res.items.length > 0) {
                        let html = '';
                        rawSubtotal = 0;

                        $.each(res.items, function(idx, item) {
                            rawSubtotal += parseFloat(item.line_subtotal);
                            html += `
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                                        <input type="hidden" name="items[${idx}][variant_id]" value="${item.variant_id || ''}">
                                        <strong>${item.product_name}</strong>
                                        ${item.variant_name ? `<br><small class="text-muted">Variant: ${item.variant_name}</small>` : ''}
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="0.01" min="0.01" name="items[${idx}][qty]" class="form-control text-center item-qty" value="${item.qty}" data-idx="${idx}">
                                    </td>
                                    <td class="text-right">
                                        <input type="number" step="0.01" min="0" name="items[${idx}][price]" class="form-control text-right item-price" value="${item.unit_price.toFixed(2)}" data-idx="${idx}">
                                    </td>
                                    <td class="text-right font-weight-bold line-subtotal-display" id="subtotal_${idx}">
                                        kr. ${item.line_subtotal.toFixed(2)}
                                    </td>
                                </tr>
                            `;
                        });

                        $('#invoiceItemsBody').html(html);
                        if (res.discount_amount !== undefined && res.discount_amount !== null) {
                            $('#discountInput').val(parseFloat(res.discount_amount).toFixed(2));
                        } else {
                            $('#discountInput').val('0.00');
                        }
                        if (res.vat_rate !== undefined && res.vat_rate !== null) {
                            $('#taxRateInput').val(parseFloat(res.vat_rate).toFixed(2));
                        } else {
                            $('#taxRateInput').val('0.00');
                        }
                        calculateTotals();

                    } else {
                        $('#invoiceItemsBody').html('<tr><td colspan="4" class="text-center text-muted py-4">No items found for selection.</td></tr>');
                    }
                },
                error: function() {
                    $('#invoiceItemsBody').html('<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-1"></i> Failed to fetch item details.</td></tr>');
                }
            });
        }

        $(document).on('input change', '.item-qty, .item-price', function() {
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('.item-qty').val()) || 0;
            let price = parseFloat(row.find('.item-price').val()) || 0;
            let lineSubtotal = qty * price;
            
            row.find('.line-subtotal-display').text('kr. ' + lineSubtotal.toFixed(2));

            rawSubtotal = 0;
            $('#invoiceItemsBody tr').each(function() {
                let q = parseFloat($(this).find('.item-qty').val()) || 0;
                let p = parseFloat($(this).find('.item-price').val()) || 0;
                rawSubtotal += (q * p);
            });

            calculateTotals();
        });

        $('#delivery_order_id_select').on('change', function() {
            let doId = $(this).val();
            if (doId) {
                fetchInvoiceItems({ delivery_order_id: doId });
            }
        });

        $('#order_id_select').on('change', function() {
            let orderId = $(this).val();
            let doId = $('#delivery_order_id_select').val();
            if (orderId && !doId) {
                fetchInvoiceItems({ order_id: orderId });
            }
        });

        // Trigger on load if preselected
        @if ($selectedDeliveryOrderId)
            fetchInvoiceItems({ delivery_order_id: {{ $selectedDeliveryOrderId }} });
        @elseif ($selectedOrderId)
            fetchInvoiceItems({ order_id: {{ $selectedOrderId }} });
        @endif
    });
</script>
@endpush
