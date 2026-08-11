@extends('backend.layouts.master')
@section('title', 'Comparison Statement Matrix')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.rfqs.show', $rfq->id) }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Comparison Statement (CS) Matrix: {{ $rfq->rfq_no }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.rfqs.index') }}">Procurement</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.rfqs.show', $rfq->id) }}">RFQ Details</a></div>
            <div class="breadcrumb-item">CS Matrix</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.rfqs.cs.store', $rfq->id) }}" method="POST">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <h4>Quotation Analysis</h4>
                </div>
                <div class="card-body">
                    
                    <div class="form-group">
                        <label>Award Type</label>
                        <select name="award_type" id="award_type" class="form-control col-md-4">
                            <option value="single">Single Vendor (Award Entire RFQ)</option>
                            <option value="split">Split Award (Award Items to Different Vendors)</option>
                        </select>
                    </div>

                    <div class="form-group" id="single_vendor_selection">
                        <label>Recommended Vendor for Entire RFQ</label>
                        <select name="recommended_vendor_id" class="form-control select2 col-md-4">
                            <option value="">-- Select Winner --</option>
                            @foreach($quotations as $quotation)
                                <option value="{{ $quotation->vendor_id }}">{{ $quotation->vendor->shop_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered text-center" id="cs-matrix-table">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 70px;">Image</th>
                                    <th style="width: 200px;">Product</th>
                                    <th>Unit Type</th>
                                    <th>Requested Qty</th>
                                    @foreach($quotations as $quotation)
                                        <th>
                                            {{ $quotation->vendor->shop_name }}<br>
                                            <span class="badge badge-info">{{ $quotation->currency->code ?? 'Base' }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rfq->items as $index => $rfqItem)
                                    <tr>
                                        <td class="text-center">
                                            @if(optional($rfqItem->product)->thumb_image)
                                                <img src="{{ asset($rfqItem->product->thumb_image) }}" alt="product" class="img-thumbnail" style="width: 45px; height: 45px; object-fit: cover;">
                                            @else
                                                <img src="{{ asset('backend/assets/img/news/img01.jpg') }}" alt="product" class="img-thumbnail" style="width: 45px; height: 45px; object-fit: cover;">
                                            @endif
                                        </td>
                                        <td class="text-left">
                                            <strong>{{ $rfqItem->product->name }}</strong>
                                            @if($rfqItem->variant)
                                                <br><small>{{ $rfqItem->variant->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $rfqItem->product->unit->name ?? 'Pcs' }}</span>
                                        </td>
                                        <td>{{ $rfqItem->qty }}</td>
                                        
                                        @foreach($quotations as $quotation)
                                            @php
                                                // Match quotation item by index position first, or by product_id + variant_id + qty
                                                $quotedItem = $quotation->items->values()->get($index);
                                                if (!$quotedItem || $quotedItem->product_id != $rfqItem->product_id || $quotedItem->variant_id != $rfqItem->variant_id) {
                                                    $quotedItem = $quotation->items->where('product_id', $rfqItem->product_id)
                                                                                   ->where('variant_id', $rfqItem->variant_id)
                                                                                   ->where('qty', $rfqItem->qty)
                                                                                   ->first() ?? $quotation->items->where('product_id', $rfqItem->product_id)
                                                                                                                 ->where('variant_id', $rfqItem->variant_id)
                                                                                                                 ->first();
                                                }
                                                
                                                $exchangeRate = $quotation->currency ? $quotation->currency->exchange_rate : 1;
                                                $unitBasePrice = $quotedItem ? ($quotedItem->unit_price * $exchangeRate) : null;
                                                $lineTotalBase = $quotedItem ? ($unitBasePrice * $rfqItem->qty) : null;
                                            @endphp
                                            
                                            <td class="price-cell" data-normalized-price="{{ $unitBasePrice ?? 9999999999 }}">
                                                @if($quotedItem)
                                                    @php
                                                        $baseCurrency = \App\Models\Currency::where('is_base', true)->first();
                                                        $baseSymbol = $baseCurrency->symbol ?? 'kr.';
                                                    @endphp
                                                    <div class="mb-2">
                                                        <span class="d-block font-weight-bold text-dark">{{ number_format($quotedItem->unit_price, 2) }}</span>
                                                        <small class="text-muted d-block" title="Unit Base Price">
                                                            Unit Base: {{ $baseSymbol }}{{ number_format($unitBasePrice, 2) }}
                                                        </small>
                                                        <small class="text-primary font-weight-bold d-block mt-1" title="Line Total Base Amount">
                                                            Total: {{ $baseSymbol }}{{ number_format($lineTotalBase, 2) }}
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="split-radio" style="display: none;">
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" 
                                                                   id="vqi_{{ $index }}_{{ $quotation->id }}" 
                                                                   name="items[{{ $index }}][selected_vqi_id]" 
                                                                   value="{{ $quotedItem->id }}" 
                                                                   class="custom-control-input">
                                                            <label class="custom-control-label font-weight-bold" for="vqi_{{ $index }}_{{ $quotation->id }}">Select</label>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-danger">No Bid</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Generate Comparison Statement</button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle Award Type Logic
        $('#award_type').on('change', function() {
            if($(this).val() === 'single') {
                $('#single_vendor_selection').show();
                $('.split-radio').hide();
                // Clear split radios
                $('input[type=radio]').prop('checked', false);
            } else {
                $('#single_vendor_selection').hide();
                $('.split-radio').show();
                // Clear single vendor select
                $('select[name="recommended_vendor_id"]').val('').trigger('change');
            }
        });

        // Highlight L1 Logic
        $('#cs-matrix-table tbody tr').each(function() {
            let row = $(this);
            let minPrice = 9999999999;
            let cells = row.find('.price-cell');
            
            // Find lowest price
            cells.each(function() {
                let price = parseFloat($(this).data('normalized-price'));
                if (price < minPrice && price !== 9999999999 && price > 0) {
                    minPrice = price;
                }
            });

            // Highlight cells with lowest price
            cells.each(function() {
                let price = parseFloat($(this).data('normalized-price'));
                if (price === minPrice && minPrice !== 9999999999) {
                    $(this).css('background-color', '#d4edda'); // Light green
                    $(this).css('border-color', '#c3e6cb');
                    
                    // Auto-select radio for split
                    $(this).find('input[type=radio]').prop('checked', true);
                }
            });
        });
    });
</script>
@endpush
