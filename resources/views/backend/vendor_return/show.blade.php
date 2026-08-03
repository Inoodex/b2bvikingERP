@extends('backend.layouts.master')

@section('title', 'Vendor Return & Debit Note - ' . $vendorReturn->debit_note_no)

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.vendor-returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Debit Note: <code>{{ $vendorReturn->debit_note_no }}</code></h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-returns.index') }}">Vendor Returns</a></div>
            <div class="breadcrumb-item">Debit Note Details</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4><i class="fas fa-file-invoice-dollar text-warning mr-2"></i> Debit Note Summary</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Status:</strong> {!! $vendorReturn->status_badge !!}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Settlement:</strong> {!! $vendorReturn->settlement_badge !!}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Return No:</strong> <code>{{ $vendorReturn->return_no }}</code>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Debit Note No:</strong> <code class="text-danger">{{ $vendorReturn->debit_note_no }}</code>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>PO Reference:</strong>
                                <a href="{{ route('admin.purchase-orders.show', $vendorReturn->purchase_id) }}" target="_blank">
                                    {{ $vendorReturn->purchase?->po_no ?? 'PO #'.$vendorReturn->purchase_id }}
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Vendor / Supplier:</strong> {{ $vendorReturn->purchase?->vendor?->name }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>GRN Reference:</strong> {{ $vendorReturn->goodsReceipt?->grn_no }}
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Approved By:</strong> {{ $vendorReturn->approvedBy?->name }}
                            </li>
                        </ul>

                        @if($vendorReturn->settlement_type === 'pending')
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalProductReplacement">
                                <i class="fas fa-boxes mr-1"></i> Settle via Product Replacement (Swap)
                            </button>
                            <button type="button" class="btn btn-success btn-block mt-2" data-toggle="modal" data-target="#modalCashRefund">
                                <i class="fas fa-hand-holding-usd mr-1"></i> Settle via Direct Money Refund
                            </button>
                        </div>
                        @else
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-check-circle mr-1"></i> Settled on {{ $vendorReturn->settled_at ? $vendorReturn->settled_at->format('d M Y, h:i A') : 'N/A' }} via <strong>{{ ucfirst(str_replace('_', ' ', $vendorReturn->settlement_type)) }}</strong>.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-danger">
                    <div class="card-header">
                        <h4><i class="fas fa-undo text-danger mr-2"></i> Returned Line Items & Claims</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Variant</th>
                                        <th class="text-right">Returned Qty</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right text-danger font-weight-bold">Claim Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandTotal = 0; @endphp
                                    @foreach($vendorReturn->items as $index => $item)
                                        @php $grandTotal += (float)$item->total_amount; @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $item->product?->name }}</strong></td>
                                            <td>{{ $item->variant?->name ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($item->qty, 2) }}</td>
                                            <td class="text-right">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($item->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-right font-weight-bold">Total Debit Claim:</td>
                                        <td class="text-right font-weight-bold text-danger" style="font-size: 1.2em;">
                                            {{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($grandTotal, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($vendorReturn->refunds->count() > 0)
                        <div class="section-title mt-4">Direct Money Refund History</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Refund Voucher #</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Bank / Ref</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendorReturn->refunds as $ref)
                                        <tr>
                                            <td><code>{{ $ref->refund_no }}</code></td>
                                            <td>{{ $ref->refund_date ? $ref->refund_date->format('d M Y') : 'N/A' }}</td>
                                            <td><span class="badge badge-success">{{ ucfirst(str_replace('_', ' ', $ref->payment_method)) }}</span></td>
                                            <td>{{ $ref->bank_name ?? 'N/A' }} {{ $ref->cheque_no ? '('.$ref->cheque_no.')' : '' }}</td>
                                            <td class="text-right font-weight-bold text-success">{{ $settings->currency_icon ?? 'Kr.' }}{{ number_format($ref->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i> This Debit Note serves as an official AP claim against {{ $vendorReturn->purchase?->vendor?->name }} accounts payable balance.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MODAL 1: Settle via Product Replacement (Swap) -->
<div class="modal fade" id="modalProductReplacement" tabindex="-1" role="dialog" aria-labelledby="modalProductReplacementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalProductReplacementLabel"><i class="fas fa-boxes mr-2"></i> Receive Replacement Stock (Product Swap)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.vendor-returns.settle-replacement') }}" method="POST">
                @csrf
                <input type="hidden" name="vendor_return_id" value="{{ $vendorReturn->id }}">
                <div class="modal-body">
                    <p class="text-muted mb-3">Select the item received as replacement (same SKU or substitute product) to increment warehouse inventory stock:</p>
                    
                    <div class="form-group">
                        <label>Select Replacement Product: <span class="text-danger">*</span></label>
                        <select name="replacement_product_id" class="form-control select2" style="width:100%" required>
                            @php $firstItem = $vendorReturn->items->first(); @endphp
                            @foreach($allProducts as $p)
                                <option value="{{ $p->id }}" {{ $firstItem && $firstItem->product_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} {{ $p->product_number ? '('.$p->product_number.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quantity Received: <span class="text-danger">*</span></label>
                                <input type="number" step="0.0001" name="qty" class="form-control" value="{{ $vendorReturn->items->sum('qty') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Receive Date: <span class="text-danger">*</span></label>
                                <input type="date" name="receive_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes / Receiving Remarks:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Enter details about replacement lott, quality check, or product swap..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Receive Stock & Settle Debit Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: Settle via Direct Money Refund -->
<div class="modal fade" id="modalCashRefund" tabindex="-1" role="dialog" aria-labelledby="modalCashRefundLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCashRefundLabel"><i class="fas fa-hand-holding-usd mr-2"></i> Record Direct Money Refund Voucher</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.vendor-returns.settle-refund') }}" method="POST">
                @csrf
                <input type="hidden" name="vendor_return_id" value="{{ $vendorReturn->id }}">
                <div class="modal-body">
                    <p class="text-muted mb-3">Record cash/bank refund deposited by vendor to settle Debit Note #<code>{{ $vendorReturn->debit_note_no }}</code>:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Refund Amount ({{ $settings->currency_icon ?? 'Kr.' }}): <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $grandTotal }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Refund Date: <span class="text-danger">*</span></label>
                                <input type="date" name="refund_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Method: <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="lc_refund">LC Refund</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bank Name / Cash Account:</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="e.g. Nordea Bank">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cheque / Txn Ref No:</label>
                                <input type="text" name="cheque_no" class="form-control" placeholder="e.g. TXN-112233">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes / Refund Remarks:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Enter bank deposit reference or refund details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Record Deposit & Settle Debit Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
