@extends('backend.layouts.master')

@section('title', 'Sales Invoice Details #' . $invoice->invoice_no)

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Invoice #{{ $invoice->invoice_no }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.sales-invoices.index') }}">Sales Invoices</a></div>
            <div class="breadcrumb-item active">#{{ $invoice->invoice_no }}</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Top Status Banner -->
        <div class="row">
            <div class="col-12">
                <div class="card card-hero">
                    <div class="card-header bg-{{ $invoice->status === 'posted' || $invoice->status === 'paid' ? 'success' : 'warning' }}">
                        <div class="card-icon">
                            <i class="fas fa-{{ $invoice->status === 'posted' || $invoice->status === 'paid' ? 'check-circle' : 'file-signature' }}"></i>
                        </div>
                        <h4>Commercial Sales Invoice #{{ $invoice->invoice_no }}</h4>
                        <div class="card-description">
                            Status: <strong>{{ strtoupper($invoice->status) }}</strong> | Total Amount: <strong>kr. {{ number_format((float)$invoice->total_amount, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left 8 Columns: Invoice Items -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-list-alt mr-2"></i>Invoiced Line Items</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.sales-invoices.pdf', $invoice->id) }}" target="_blank" class="btn btn-danger font-weight-bold">
                                <i class="fas fa-file-pdf mr-1"></i> PDF Commercial Invoice
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product / Variant</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Line Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->items as $index => $item)
                                        @php
                                            $lineSubtotal = (float)$item->qty * (float)$item->price;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product ? $item->product->name : 'Product' }}</strong>
                                                @if ($item->variant)
                                                    <br><small class="text-muted">Variant: {{ $item->variant->name }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold">{{ number_format((float)$item->qty, 2) }} {{ $item->product && $item->product->unit ? $item->product->unit->name : 'Pcs' }}</td>
                                            <td class="text-right">kr. {{ number_format((float)$item->price, 2) }}</td>
                                            <td class="text-right font-weight-bold">kr. {{ number_format($lineSubtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">Subtotal:</td>
                                        <td class="text-right font-weight-bold">kr. {{ number_format((float)$invoice->subtotal_amount, 2) }}</td>
                                    </tr>
                                    @if ($invoice->discount_amount > 0)
                                        <tr>
                                            <td colspan="4" class="text-right text-danger font-weight-bold">Discount:</td>
                                            <td class="text-right text-danger font-weight-bold">- kr. {{ number_format((float)$invoice->discount_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if ($invoice->tax_amount > 0)
                                        <tr>
                                            <td colspan="4" class="text-right font-weight-bold">VAT Tax:</td>
                                            <td class="text-right font-weight-bold">kr. {{ number_format((float)$invoice->tax_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold h5 text-primary">Invoice Total:</td>
                                        <td class="text-right font-weight-bold h5 text-primary">kr. {{ number_format((float)$invoice->total_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if ($invoice->notes)
                            <div class="alert alert-light border mt-3 mb-0">
                                <h6><i class="fas fa-sticky-note mr-2"></i>Notes / Wire Transfer Instructions:</h6>
                                <p class="mb-0 text-muted">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right 4 Columns: Billing & Financial Audit Card -->
            <div class="col-md-4">
                <!-- Action Card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-cogs mr-2"></i>Financial Actions</h4>
                    </div>
                    <div class="card-body">
                        @if ($invoice->status === 'draft')
                            <form action="{{ route('admin.sales-invoices.post', $invoice->id) }}" method="POST" id="postInvoiceForm">
                                @csrf
                                <button type="button" class="btn btn-success btn-lg btn-block font-weight-bold" id="btnPostInvoice">
                                    <i class="fas fa-check-circle mr-1"></i> Post & Journal Entry
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success mb-0 text-center">
                                <i class="fas fa-lock mr-2"></i> <strong>Posted & Accounting Locked</strong>
                            </div>
                        @endif

                        @if ($invoice->due_amount > 0)
                            <a href="{{ route('admin.customer-payments.create', ['sales_invoice_id' => $invoice->id]) }}" class="btn btn-success btn-block mt-2 font-weight-bold shadow-sm" style="border-radius: 6px;">
                                <i class="fas fa-money-check-alt mr-1"></i> Record Customer Payment
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Customer Info Card -->
                <div class="card card-info">
                    <div class="card-header">
                        <h4><i class="fas fa-user-tag mr-2"></i>B2B Customer Info</h4>
                    </div>
                    <div class="card-body">
                        @if ($invoice->order && $invoice->order->user)
                            <p class="mb-1"><strong>Company / Outlet:</strong> {{ $invoice->order->user->outlet_name ?: $invoice->order->user->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $invoice->order->user->email }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $invoice->order->user->phone ?: '-' }}</p>
                            <p class="mb-0"><strong>Address:</strong> {{ $invoice->order->user->address ?: '-' }}</p>
                        @else
                            <p class="text-muted mb-0">Guest / Cash Customer</p>
                        @endif
                    </div>
                </div>

                <!-- Invoice Meta Card -->
                <div class="card card-secondary">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle mr-2"></i>Billing Metadata</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Invoice Date:</strong> {{ $invoice->date ? $invoice->date->format('d M Y') : '-' }}</p>
                        <p class="mb-1"><strong>Payment Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</p>
                        <p class="mb-1"><strong>Linked Order:</strong> <a href="{{ route('admin.orders.show', $invoice->order_id) }}">#{{ $invoice->order ? $invoice->order->order_no : '-' }}</a></p>
                        <p class="mb-1"><strong>Total Amount:</strong> kr. {{ number_format((float)$invoice->total_amount, 2) }}</p>
                        <p class="mb-0"><strong>Balance Due:</strong> <span class="font-weight-bold text-danger">kr. {{ number_format((float)$invoice->due_amount, 2) }}</span></p>
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
        $('#btnPostInvoice').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Post Commercial Invoice?',
                text: "Posting will lock billing totals and record automated General Ledger journal entries!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Post Invoice Now!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#postInvoiceForm').submit();
                }
            });
        });
    });
</script>
@endpush
