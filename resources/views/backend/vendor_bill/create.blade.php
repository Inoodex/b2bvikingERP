@extends('backend.layouts.master')

@section('title', 'Generate Vendor Bill')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.vendor-bills.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Generate Vendor Bill (Invoice)</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-bills.index') }}">Vendor Bills</a></div>
            <div class="breadcrumb-item">Generate Bill</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Document Selector Card --}}
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h4><i class="fas fa-search mr-2"></i>Select Source Purchase Order or Goods Receipt</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Select Purchase Order (PO):</label>
                            <select id="po_selector" class="form-control select2" onchange="if(this.value) window.location.href='{{ route('admin.vendor-bills.create') }}?purchase_id=' + this.value;">
                                <option value="">-- Choose Purchase Order --</option>
                                @foreach($purchases as $p)
                                    <option value="{{ $p->id }}" {{ isset($purchase) && $purchase->id == $p->id && !$goodsReceipt ? 'selected' : '' }}>
                                        {{ $p->po_no ?: 'PO #' . $p->id }} ({{ $p->vendor?->name ?? 'Unknown Vendor' }} - {{ $p->currency?->symbol ?? 'kr.' }}{{ number_format($p->total_amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Or Select Goods Receipt (GRN):</label>
                            <select id="grn_selector" class="form-control select2" onchange="if(this.value) window.location.href='{{ route('admin.vendor-bills.create') }}?grn_id=' + this.value;">
                                <option value="">-- Choose Goods Receipt --</option>
                                @foreach($goodsReceipts as $g)
                                    <option value="{{ $g->id }}" {{ isset($goodsReceipt) && $goodsReceipt->id == $g->id ? 'selected' : '' }}>
                                        {{ $g->grn_no }} (PO: {{ $g->purchase?->po_no }} - {{ $g->purchase?->vendor?->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($purchase)
        <form action="{{ route('admin.vendor-bills.store') }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
            @if($goodsReceipt)
                <input type="hidden" name="goods_receipt_id" value="{{ $goodsReceipt->id }}">
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle text-primary mr-2"></i> Document Reference</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>PO Reference:</label>
                                <input type="text" class="form-control font-weight-bold" value="{{ $purchase->po_no ?: 'PO #' . $purchase->id }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Vendor Name:</label>
                                <input type="text" class="form-control" value="{{ $purchase->vendor?->name }}" readonly>
                            </div>
                            @if($goodsReceipt)
                            <div class="form-group">
                                <label>GRN Reference:</label>
                                <input type="text" class="form-control font-weight-bold text-success" value="{{ $goodsReceipt->grn_no }}" readonly>
                            </div>
                            @endif
                            <div class="form-group">
                                <label>Bill Date <span class="text-danger">*</span></label>
                                <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Due Date:</label>
                                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>
                            <div class="form-group">
                                <label>Notes / Remarks:</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Enter bill notes or invoice payment terms..."></textarea>
                            </div>
                        </div>
                    </div>

                    @if($debitNoteAmount > 0)
                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Pending Debit Notes</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Unsettled QC Rejection Debit Notes found for this PO:</p>
                            <h3 class="text-danger">{{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($debitNoteAmount, 2) }}</h3>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="apply_debit_notes" value="1" id="applyDebitNotes" checked>
                                <label class="form-check-label font-weight-bold" for="applyDebitNotes">
                                    Auto-apply Debit Notes as Credit Adjustment
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-list-alt text-primary mr-2"></i> Bill Line Items</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th style="width: 110px;">Qty</th>
                                            <th style="width: 140px;">Unit Price</th>
                                            <th style="width: 140px;">Landed Cost</th>
                                            <th class="text-right">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $currSym = $purchase->vendor?->currency?->symbol ?? $purchase->currency?->symbol ?? 'kr.';
                                            $calculatedSubtotal = 0;
                                        @endphp
                                        @if($goodsReceipt)
                                            @foreach($goodsReceipt->items as $index => $item)
                                                @if($item->accepted_qty > 0)
                                                @php
                                                    $poDetail = $purchase->items->where('product_id', $item->product_id)->where('variant_id', $item->variant_id)->first();
                                                    $unitCost = $poDetail && $poDetail->unit_cost > 0 ? (float)$poDetail->unit_cost : (float)($item->product?->purchase_price ?: ($item->product?->price ?: 0));
                                                    $lineTotal = (float)$item->accepted_qty * $unitCost;
                                                    $calculatedSubtotal += $lineTotal;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->product?->name }}</strong>
                                                        @if($item->variant)
                                                            <br><small class="text-muted">Variant: {{ $item->variant->name }}</small>
                                                        @endif
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                        <input type="hidden" name="items[{{ $index }}][variant_id]" value="{{ $item->variant_id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" name="items[{{ $index }}][qty]" class="form-control form-control-sm item-qty text-right font-weight-bold" value="{{ $item->accepted_qty }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm item-price text-right" value="{{ $unitCost }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][landed_cost]" class="form-control form-control-sm item-landed text-right" value="{{ $poDetail ? (float)($poDetail->landed_cost > 0 ? $poDetail->landed_cost : $unitCost) : $unitCost }}" readonly>
                                                    </td>
                                                    <td class="text-right font-weight-bold align-middle">
                                                        {{ $currSym }} {{ number_format($lineTotal, 2) }}
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            @foreach($purchase->items as $index => $detail)
                                                @php
                                                    $itemQty = (float)($detail->qty ?? $detail->quantity ?? 0);
                                                    $itemPrice = (float)($detail->unit_cost > 0 ? $detail->unit_cost : ($detail->unit_price > 0 ? $detail->unit_price : ($detail->product?->purchase_price > 0 ? $detail->product?->purchase_price : ($detail->product?->price ?? 0))));
                                                    $itemTotal = (float)($detail->total > 0 ? $detail->total : ($detail->total_amount > 0 ? $detail->total_amount : ($itemQty * $itemPrice)));
                                                    $calculatedSubtotal += $itemTotal;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $detail->product?->name }}</strong>
                                                        @if($detail->variant)
                                                            <br><small class="text-muted">Variant: {{ $detail->variant->name }}</small>
                                                        @endif
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $detail->product_id }}">
                                                        <input type="hidden" name="items[{{ $index }}][variant_id]" value="{{ $detail->variant_id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" name="items[{{ $index }}][qty]" class="form-control form-control-sm text-right font-weight-bold" value="{{ $itemQty }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm text-right" value="{{ $itemPrice }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][landed_cost]" class="form-control form-control-sm text-right" value="{{ (float)($detail->landed_cost > 0 ? $detail->landed_cost : $itemPrice) }}" readonly>
                                                    </td>
                                                    <td class="text-right font-weight-bold align-middle">
                                                        {{ $currSym }} {{ number_format($itemTotal, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        @if($calculatedSubtotal <= 0)
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-warning">
                                                    <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                                                    <strong>No line items available to bill.</strong>
                                                    <p class="small text-muted mb-0">Please verify that the source Goods Receipt has accepted items or select another document.</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end mt-4">
                                <div class="col-md-6">
                                    <table class="table table-sm text-right">
                                        <tr>
                                            <th>Subtotal:</th>
                                            <td class="font-weight-bold">{{ $currSym }} {{ number_format($calculatedSubtotal, 2) }}</td>
                                        </tr>
                                        @if($debitNoteAmount > 0)
                                        <tr class="text-danger">
                                            <th>Less Debit Notes:</th>
                                            <td class="font-weight-bold">-{{ $currSym }} {{ number_format($debitNoteAmount, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="table-active">
                                            <th>Net Payable Amount:</th>
                                            <th class="text-primary h5">{{ $currSym }} {{ number_format(max(0, $calculatedSubtotal - $debitNoteAmount), 2) }}</th>
                                        </tr>
                                    </table>

                                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                                        <i class="fas fa-check-circle mr-2"></i> Save & Issue Vendor Bill
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @else
        <div class="card card-light text-center py-5">
            <div class="card-body">
                <i class="fas fa-file-invoice-dollar fa-3x text-primary mb-3"></i>
                <h5>Select a Purchase Order or Goods Receipt Above</h5>
                <p class="text-muted">Choose any purchase order or goods receipt from the dropdown above to load line items and generate a supplier vendor invoice.</p>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
