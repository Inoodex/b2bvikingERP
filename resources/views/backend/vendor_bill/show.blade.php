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
        <!-- 3-Way Matching Executive Audit Banner -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="font-weight-bold mb-1 text-white">
                            <i class="fas fa-shield-alt text-warning mr-2"></i> 3-Way Match Audit Verification
                        </h5>
                        <p class="text-muted small mb-0" style="color: #94a3b8 !important;">Cross-verifying Purchase Order, Goods Receipt Note (GRN), and Supplier Billed Totals.</p>
                    </div>
                    <div>
                        @if($bill->goods_receipt_id || $bill->goodsReceipt)
                            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px; border-radius: 20px;">
                                <i class="fas fa-check-double mr-1"></i> 3-Way Matched & Cleared
                            </span>
                        @else
                            <span class="badge badge-info px-3 py-2 font-weight-bold" style="font-size: 13px; border-radius: 20px;">
                                <i class="fas fa-file-invoice mr-1"></i> 2-Way Matched (Direct PO)
                            </span>
                        @endif
                    </div>
                </div>
                <div class="row text-center mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="small text-uppercase font-weight-bold" style="color: #94a3b8;">1. Purchase Order</div>
                        <div class="font-weight-bold text-white mt-1">
                            <i class="fas fa-check-circle text-success mr-1"></i> {{ $bill->purchase?->po_no ?? 'PO #'.$bill->purchase_id }}
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="small text-uppercase font-weight-bold" style="color: #94a3b8;">2. Goods Receipt (GRN)</div>
                        <div class="font-weight-bold text-white mt-1">
                            @if($bill->goodsReceipt)
                                <i class="fas fa-check-circle text-success mr-1"></i> {{ $bill->goodsReceipt->grn_no }} (Received)
                            @else
                                <i class="fas fa-info-circle text-info mr-1"></i> Direct PO Invoiced
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-uppercase font-weight-bold" style="color: #94a3b8;">3. Vendor Invoice</div>
                        <div class="font-weight-bold text-white mt-1">
                            <i class="fas fa-check-circle text-success mr-1"></i> {{ $bill->bill_no }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                <strong>Approval:</strong> 
                                @if($bill->approval_status === 'approved')
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Approved</span>
                                @elseif($bill->approval_status === 'rejected')
                                    <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Rejected</span>
                                @elseif($bill->approval_status === 'level1_approved')
                                    <span class="badge badge-info"><i class="fas fa-tasks mr-1"></i> Multi-Step In Progress</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-hourglass-half mr-1"></i> Pending Approval</span>
                                @endif
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

                        @php
                            $exchangeRate = $bill->purchase?->exchange_rate_used ?? $bill->currency?->exchange_rate ?? $bill->exchange_rate ?? 1;
                            $baseCurrencySymbol = $settings->currency_icon ?? 'kr.';
                            $isForeignCurrency = $bill->currency_id && ($bill->currency?->code !== 'DKK');
                        @endphp
                        <div class="card card-secondary mt-3 mb-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal:</span>
                                    <strong>{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($bill->subtotal, 2) }}</strong>
                                </div>
                                @if($bill->debit_note_adjustment > 0)
                                <div class="d-flex justify-content-between text-danger mb-1">
                                    <span>Debit Note Adjustment:</span>
                                    <strong>-{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($bill->debit_note_adjustment, 2) }}</strong>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span class="h6 mb-0">Grand Total:</span>
                                    <div class="text-right">
                                        <strong class="h6 mb-0 text-primary">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($bill->grand_total, 2) }}</strong>
                                        @if($isForeignCurrency && $exchangeRate > 0)
                                            <div class="text-muted small font-weight-bold" style="font-size: 11px;">(≈ {{ $baseCurrencySymbol }}{{ number_format($bill->grand_total * $exchangeRate, 2) }} Base)</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-success mt-1">
                                    <span>Total Paid:</span>
                                    <strong>{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($bill->paid_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between text-danger mt-1 border-top pt-1">
                                    <span class="font-weight-bold">Balance Due:</span>
                                    <div class="text-right">
                                        <strong class="font-weight-bold">{{ $bill->currency?->symbol ?? ($settings->currency_icon ?? 'kr.') }}{{ number_format($bill->due_amount, 2) }}</strong>
                                        @if($isForeignCurrency && $exchangeRate > 0)
                                            <div class="text-muted small font-weight-bold" style="font-size: 11px;">(≈ {{ $baseCurrencySymbol }}{{ number_format($bill->due_amount * $exchangeRate, 2) }} Base)</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($bill->approval_status !== 'approved')
                            <button type="button" class="btn btn-secondary btn-block mt-3 disabled" disabled title="Payment locked until bill is fully approved">
                                <i class="fas fa-lock mr-2"></i> Payment Locked (Approval In Progress)
                            </button>
                        @elseif(in_array($bill->payment_status, ['unpaid', 'partial']))
                            <a href="{{ route('admin.purchase-payments.create', ['bill_id' => $bill->id]) }}" class="btn btn-success btn-block mt-3">
                                <i class="fas fa-money-bill-wave mr-2"></i> Record Payment Voucher
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Visual Multi-Step Approval Chain & Audit Stepper --}}
                @include('backend.components.approval_chain', [
                    'model' => $bill,
                    'approveRoute' => 'admin.vendor-bills.approve',
                    'rejectRoute' => 'admin.vendor-bills.reject',
                    'rejectModalId' => 'rejectBillModal'
                ])
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

    <!-- Reject Bill Modal -->
    <div class="modal fade" id="rejectBillModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.vendor-bills.reject', $bill->id) }}">
                @csrf
                <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                    <div class="modal-header bg-danger text-white py-3">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-times-circle mr-2"></i> Reject Vendor Bill</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-3">Are you sure you want to reject Vendor Bill <strong>#{{ $bill->bill_no }}</strong>? Payment vouchers cannot be recorded against a rejected bill.</p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-danger">Reason for Rejection *</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="State exact reason for rejection (e.g. Discrepancy in billed amount or terms)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger font-weight-bold px-4"><i class="fas fa-times mr-1"></i> Reject Bill</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
