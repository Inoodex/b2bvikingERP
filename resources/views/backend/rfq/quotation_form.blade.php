@extends('backend.layouts.master')
@section('title', 'Submit Quotation')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Submit Quotation for RFQ: {{ $rfq->rfq_no }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Vendor: {{ $vendor->shop_name }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.rfqs.quotations.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="rfq_id" value="{{ $rfq->id }}">
                                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Quotation No (From Vendor)</label>
                                            <input type="text" name="quotation_no" class="form-control" placeholder="e.g. Q-10293">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select name="currency_id" class="form-control select2" required>
                                                @php
                                                    $defaultCurrencyId = null;
                                                    if ($vendor->currency_id) {
                                                        $defaultCurrencyId = $vendor->currency_id;
                                                    } elseif ($vendor->currency_name) {
                                                        $matched = $currencies->where('code', $vendor->currency_name)->first();
                                                        if ($matched) {
                                                            $defaultCurrencyId = $matched->id;
                                                        }
                                                    }
                                                    if (!$defaultCurrencyId) {
                                                        $baseCurrency = $currencies->where('is_base', true)->first();
                                                        $defaultCurrencyId = $baseCurrency ? $baseCurrency->id : null;
                                                    }
                                                @endphp
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}" {{ $currency->id == $defaultCurrencyId ? 'selected' : '' }}>
                                                        {{ $currency->name }} ({{ $currency->symbol }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Delivery Terms</label>
                                            <input type="text" name="delivery_terms" class="form-control" placeholder="e.g. FOB, CIF">
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h5>Quoted Items</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Requested Qty</th>
                                                <th>Quoted Unit Price *</th>
                                                <th>Total Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rfq->items as $index => $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->product->name }}
                                                        @if($item->variant)
                                                            <br><small class="badge badge-info mt-1">{{ $item->variant->name }}</small>
                                                        @endif
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                        <input type="hidden" name="items[{{ $index }}][variant_id]" value="{{ $item->variant_id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][qty]" class="form-control qty-input" value="{{ $item->qty }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" step="0.01" required>
                                                    </td>
                                                    <td>
                                                        <span class="row-total">0.00</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-right">Grand Total:</th>
                                                <th id="grand-total">0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Submit Quotation</button>
                                    <a href="{{ route('admin.rfqs.show', $rfq->id) }}" class="btn btn-secondary">Cancel</a>
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
        function calculateTotal() {
            let grandTotal = 0;
            $('tbody tr').each(function() {
                let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                let price = parseFloat($(this).find('.price-input').val()) || 0;
                let total = qty * price;
                $(this).find('.row-total').text(total.toFixed(2));
                grandTotal += total;
            });
            $('#grand-total').text(grandTotal.toFixed(2));
        }

        $('.price-input').on('input', calculateTotal);
    });
</script>
@endpush
