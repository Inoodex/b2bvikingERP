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
                                <input type="text" class="form-control" value="{{ $purchase->po_no }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Vendor Name:</label>
                                <input type="text" class="form-control" value="{{ $purchase->vendor?->name }}" readonly>
                            </div>
                            @if($goodsReceipt)
                            <div class="form-group">
                                <label>GRN Reference:</label>
                                <input type="text" class="form-control" value="{{ $goodsReceipt->grn_no }}" readonly>
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
                                        @if($goodsReceipt)
                                            @foreach($goodsReceipt->items as $index => $item)
                                                @if($item->accepted_qty > 0)
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
                                                        <input type="number" step="0.0001" name="items[{{ $index }}][qty]" class="form-control form-control-sm item-qty" value="{{ $item->accepted_qty }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm item-price" value="{{ $item->product?->price ?? 0 }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][landed_cost]" class="form-control form-control-sm item-landed" value="{{ $item->accepted_qty > 0 ? round(($item->line_total ?? ($item->accepted_qty * ($item->product?->price ?? 0))) / $item->accepted_qty, 2) : 0 }}" readonly>
                                                    </td>
                                                    <td class="text-right font-weight-bold align-middle">
                                                        {{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($item->accepted_qty * ($item->product?->price ?? 0), 2) }}
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            @foreach($purchase->details as $index => $detail)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $detail->product?->name }}</strong>
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $detail->product_id }}">
                                                        <input type="hidden" name="items[{{ $index }}][variant_id]" value="{{ $detail->variant_id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" name="items[{{ $index }}][qty]" class="form-control form-control-sm" value="{{ $detail->quantity }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm" value="{{ $detail->unit_price }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="items[{{ $index }}][landed_cost]" class="form-control form-control-sm" value="{{ $detail->landed_cost ?? $detail->unit_price }}" readonly>
                                                    </td>
                                                    <td class="text-right font-weight-bold align-middle">
                                                        {{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($detail->total_amount, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end mt-4">
                                <div class="col-md-6">
                                    <table class="table table-sm text-right">
                                        <tr>
                                            <th>Subtotal:</th>
                                            <td class="font-weight-bold">{{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($goodsReceipt ? $goodsReceipt->items->sum(fn($i) => $i->accepted_qty * ($i->product?->price ?? 0)) : $purchase->total_amount, 2) }}</td>
                                        </tr>
                                        @if($debitNoteAmount > 0)
                                        <tr class="text-danger">
                                            <th>Less Debit Notes:</th>
                                            <td class="font-weight-bold">-{{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($debitNoteAmount, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="table-active">
                                            <th>Net Payable Amount:</th>
                                            <th class="text-primary h5">{{ $purchase->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format(max(0, ($goodsReceipt ? $goodsReceipt->items->sum(fn($i) => $i->accepted_qty * ($i->product?->price ?? 0)) : $purchase->total_amount) - $debitNoteAmount), 2) }}</th>
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
    </div>
</section>
@endsection
