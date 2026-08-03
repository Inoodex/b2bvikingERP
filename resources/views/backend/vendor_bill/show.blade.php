@extends('backend.layouts.master')

@section('title', 'Vendor Bill: ' . $bill->bill_no)

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.vendor-bills.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Vendor Bill: <code>{{ $bill->bill_no }}</code></h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-bills.index') }}">Vendor Bills</a></div>
            <div class="breadcrumb-item">Bill Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-file-invoice text-primary mr-2"></i> Bill Overview</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Payment Status:</strong> {!! $bill->formatted_status !!}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Bill Date:</strong> {{ $bill->bill_date ? $bill->bill_date->format('d M Y') : 'N/A' }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Due Date:</strong> {{ $bill->due_date ? $bill->due_date->format('d M Y') : 'N/A' }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Supplier:</strong> {{ $bill->vendor?->name }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>PO Reference:</strong>
                                <a href="{{ route('admin.purchase-orders.show', $bill->purchase_id) }}" target="_blank">
                                    {{ $bill->purchase?->po_no ?? 'PO #'.$bill->purchase_id }}
                                </a>
                            </li>
                            @if($bill->goodsReceipt)
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>GRN Reference:</strong>
                                <a href="{{ route('admin.goods-receipts.show', $bill->goods_receipt_id) }}" target="_blank">
                                    {{ $bill->goodsReceipt->grn_no }}
                                </a>
                            </li>
                            @endif
                        </ul>

                        <div class="card card-secondary mt-3 mb-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal:</span>
                                    <strong>{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($bill->subtotal, 2) }}</strong>
                                </div>
                                @if($bill->debit_note_adjustment > 0)
                                <div class="d-flex justify-content-between text-danger mb-1">
                                    <span>Debit Note Adjustment:</span>
                                    <strong>-{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($bill->debit_note_adjustment, 2) }}</strong>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span class="h6 mb-0">Grand Total:</span>
                                    <strong class="h6 mb-0 text-primary">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($bill->grand_total, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between text-success mt-1">
                                    <span>Total Paid:</span>
                                    <strong>{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($bill->paid_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between text-danger mt-1 border-top pt-1">
                                    <span class="font-weight-bold">Balance Due:</span>
                                    <strong class="font-weight-bold">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($bill->due_amount, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        @if(in_array($bill->payment_status, ['unpaid', 'partial']))
                            <a href="{{ route('admin.purchase-payments.create', ['bill_id' => $bill->id]) }}" class="btn btn-success btn-block mt-3">
                                <i class="fas fa-money-bill-wave mr-2"></i> Record Payment Voucher
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-list text-primary mr-2"></i> Bill Billed Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Billed Qty</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Landed Cost</th>
                                        <th class="text-right">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bill->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product?->name }}</strong>
                                                @if($item->variant)
                                                    <br><small class="text-muted">Variant: {{ $item->variant->name }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($item->qty, 2) }}</td>
                                            <td class="text-right">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-right">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($item->landed_cost, 2) }}</td>
                                            <td class="text-right font-weight-bold">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($bill->debitNoteSettlements->count() > 0)
                        <div class="section-title mt-4">Applied Debit Note Credit Settlements</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Debit Note No</th>
                                        <th>Settlement Date</th>
                                        <th>Settled Amount</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bill->debitNoteSettlements as $dns)
                                        <tr>
                                            <td><code>{{ $dns->vendorReturn?->debit_note_no }}</code></td>
                                            <td>{{ $dns->settlement_date ? $dns->settlement_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                            <td class="text-danger font-weight-bold">-{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'Kr.') }}{{ number_format($dns->settled_amount, 2) }}</td>
                                            <td>{{ $dns->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
