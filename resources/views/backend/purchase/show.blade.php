@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Receive Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Order Receive</a></div>
                <div class="breadcrumb-item">Invoice</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Visual Multi-Step Approval Chain & Audit Stepper --}}
            @include('backend.components.approval_chain', [
                'model' => $purchase,
                'approveRoute' => 'admin.purchase-orders.approve',
                'rejectRoute' => null,
                'rejectModalId' => null
            ])

            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Invoice</h2>
                                <div class="invoice-number">Order #{{ $purchase->invoice_no }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Vendor:</strong><br>
                                        {{ $purchase->vendor->shop_name }}<br>
                                        {{ $purchase->vendor->address }}<br>
                                        {{ $purchase->vendor->phone }}
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Created By:</strong><br>
                                        {{ $purchase->user->name ?? 'System' }}<br>
                                        {{ $purchase->user->email ?? '' }}
                                    </address>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Order Date:</strong><br>
                                        {{ $purchase->date }}<br><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Order Summary</div>
                            <p class="section-lead">All items here cannot be deleted.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tr>
                                        <th data-width="40">#</th>
                                        <th width="60">Image</th>
                                        <th>Item</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Vendor Price</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Vendor Total</th>
                                    </tr>
                                    @foreach ($purchase->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($detail->product->thumb_image)
                                                <img src="{{ asset('storage/'.$detail->product->thumb_image) }}" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 50px; height: 50px;">N/A</div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $detail->product->name }}
                                            @if($detail->variant_info)
                                                <div class="mt-1">
                                                    @foreach($detail->variant_info as $name => $qty)
                                                        @if(is_array($qty)) {{-- Fallback for old single-item format if it exists --}}
                                                            @continue
                                                        @endif
                                                        <span class="badge badge-light border text-muted small mr-1 mb-1">
                                                            {{ is_numeric($name) ? '' : $name.':' }} {{ $qty }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                         <td class="text-center">{{ formatConverted($detail->unit_cost) }}</td>
                                         <td class="text-center">{{ $purchase->vendor->currency_icon }}{{ number_format($detail->unit_cost_vendor, 2) }}</td>
                                         <td class="text-center">{{ $detail->qty }}</td>
                                         <td class="text-right">{{ formatConverted($detail->total) }}</td>
                                         <td class="text-right">{{ $purchase->vendor->currency_icon }}{{ number_format($detail->unit_cost_vendor * $detail->qty, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                    @if($purchase->note)
                                        <div class="section-title">Note</div>
                                        <p class="section-lead">{{ $purchase->note }}</p>
                                    @endif

                                    @if($purchase->attachments->count() > 0 || $purchase->invoice_attachment)
                                        <div class="section-title">Attachment</div>
                                        <div class="section-lead mt-1">
                                            @if($purchase->attachments->count() > 0)
                                                @foreach($purchase->attachments as $index => $attachment)
                                                    <a href="{{ route('admin.purchases.download-attachment', [$purchase->id, $attachment->id]) }}" class="btn btn-info btn-sm mb-1">
                                                        <i class="fas fa-file-download mr-1"></i> Attachment {{ $index + 1 }}
                                                    </a>
                                                @endforeach
                                            @elseif($purchase->invoice_attachment)
                                                <a href="{{ route('admin.purchases.download-legacy-attachment', $purchase->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-file-download mr-1"></i> View Invoice Attachment
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                                        <div class="section-title mb-0">Vendor Payment History</div>
                                        @if($purchase->payments->count() > 0)
                                            <div>
                                                <a href="{{ route('admin.accounts.vendor-purchases.payments.pdf', $purchase->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-pdf mr-1"></i> Download All
                                                </a>
                                                <a href="{{ route('admin.accounts.vendor-purchases.payments.view', $purchase->id) }}" class="btn btn-sm btn-outline-primary ml-2" target="_blank">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-md">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Method</th>
                                                    <th>Transaction ID</th>
                                                    <th>Receipts</th>
                                                    <th>PDF</th>
                                                    <th class="text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($purchase->payments->sortByDesc('id') as $payment)
                                                    <tr>
                                                        <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                                                        <td><span class="badge badge-info">{{ strtoupper($payment->payment_method) }}</span></td>
                                                        <td>{{ $payment->transaction_id ?: 'N/A' }}</td>
                                                        <td>
                                                            @if($payment->receipts->count() > 0)
                                                                @foreach($payment->receipts as $receipt)
                                                                    <div class="mb-1">
                                                                        <a href="{{ route('admin.accounts.vendor-payments.receipts.download', $receipt->id) }}" class="btn btn-sm btn-outline-primary">
                                                                            <i class="fas fa-download mr-1"></i>
                                                                        </a>
                                                                        <a href="{{ route('admin.accounts.vendor-payments.receipts.destroy', $receipt->id) }}" class="btn btn-sm btn-outline-danger delete-item">
                                                                            <i class="fas fa-trash mr-1"></i>
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-nowrap">
                                                            <a href="{{ route('admin.accounts.vendor-payments.single.pdf', $payment->id) }}" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-file-pdf mr-1"></i>
                                                            </a>
                                                            <a href="{{ route('admin.accounts.vendor-payments.single.view', $payment->id) }}" class="btn btn-sm btn-outline-primary ml-2" target="_blank">
                                                                <i class="fas fa-eye mr-1"></i>
                                                            </a>
                                                        </td>
                                                        <td class="text-right font-weight-bold text-success">{{ formatConverted($payment->amount) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 text-muted">No vendor payments recorded yet.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-right">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Subtotal (Items)</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->total_amount - ($purchase->material_cost + $purchase->transport_cost + $purchase->tax)) }}</div>
                                    </div>
                                    @if($purchase->material_cost > 0)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Material Cost</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->material_cost) }}</div>
                                    </div>
                                    @endif
                                    @if($purchase->transport_cost > 0)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Transport Cost</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->transport_cost) }}</div>
                                    </div>
                                    @endif
                                    @if($purchase->tax > 0)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Tax</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->tax) }}</div>
                                    </div>
                                    @endif
                                    <hr class="mt-2 mb-2">
                                     @php
                                         $vendorItemTotal = $purchase->details->sum(function($d) { return $d->unit_cost_vendor * $d->qty; });
                                         $grandTotal = $purchase->total_amount;
                                         // Round for display if very close to integer or if currency logic suggests it
                                         $displayTotal = (abs($grandTotal - round($grandTotal)) < 0.01) ? round($grandTotal) : $grandTotal;
                                     @endphp
                                     <div class="invoice-detail-item">
                                         <div class="invoice-detail-name">Grand Total</div>
                                         <div class="invoice-detail-value invoice-detail-value-lg">{{ formatConverted($displayTotal) }}</div>
                                     </div>
                                     <div class="invoice-detail-item">
                                         <div class="invoice-detail-name">Vendor Total ({{ $purchase->vendor->currency_name }})</div>
                                         <div class="invoice-detail-value">{{ $purchase->vendor->currency_icon }}{{ number_format($vendorItemTotal, 2) }}</div>
                                     </div>
                                     <hr class="mt-2 mb-2">
                                     @php
                                         $paymentStatusClass = match($purchase->payment_status) {
                                             'paid' => 'success',
                                             'partial' => 'warning',
                                             default => 'secondary',
                                         };
                                     @endphp
                                     <div class="invoice-detail-item">
                                         <div class="invoice-detail-name">Paid Amount</div>
                                         <div class="invoice-detail-value text-success">{{ formatConverted($purchase->paid_amount) }}</div>
                                     </div>
                                     <div class="invoice-detail-item">
                                         <div class="invoice-detail-name">Due Amount</div>
                                         <div class="invoice-detail-value text-danger">{{ formatConverted($purchase->due_amount) }}</div>
                                     </div>
                                     <div class="invoice-detail-item">
                                         <div class="invoice-detail-name">Payment Status</div>
                                         <div class="invoice-detail-value">
                                             <span class="badge badge-{{ $paymentStatusClass }}">{{ ucfirst($purchase->payment_status ?? 'pending') }}</span>
                                         </div>
                                     </div>
                                     @if((float) $purchase->due_amount > 0)
                                         <div class="mt-3 text-right">
                                             <a href="{{ route('admin.accounts.vendor-payments.record-payment', ['invoice_no' => $purchase->invoice_no]) }}" class="btn btn-dark btn-sm px-3 py-2">
                                                 <i class="fas fa-money-bill-wave mr-1"></i> Pay Vendor Invoice
                                             </a>
                                         </div>
                                     @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                {{-- <div class="text-md-right">
                     <button class="btn btn-warning btn-icon icon-left" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                </div> --}}
            </div>
        </div>
    </section>
@endsection
