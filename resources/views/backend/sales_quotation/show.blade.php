@extends('backend.layouts.master')

@section('title', 'Sales Quotation Details - ' . $salesQuotation->quotation_no)

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header border-0 shadow-sm mb-4" style="background: #ffffff; border-radius: 16px; padding: 20px 24px;">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="mr-3 p-3 rounded-circle text-white shadow-sm" style="background: linear-gradient(135deg, #0a0e1a 0%, #1e293b 100%); border: 1px solid rgba(205, 160, 90, 0.3);">
                        <i class="fas fa-file-invoice text-warning" style="font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Sales Quotation: {{ $salesQuotation->quotation_no }}
                        </h4>
                        <p class="text-muted mb-0 small">Issued on {{ $salesQuotation->created_at->format('d M, Y') }} | Status: 
                            <span class="badge badge-info text-capitalize">{{ $salesQuotation->status }}</span>
                        </p>
                    </div>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <a href="{{ route('admin.sales-quotations.pdf', $salesQuotation->id) }}" class="btn btn-secondary px-3 py-2 font-weight-bold mr-2 shadow-sm" style="border-radius: 10px;" target="_blank">
                        <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF Export
                    </a>

                    @if($salesQuotation->status !== 'converted')
                        <form action="{{ route('admin.sales-quotations.convert-to-order', $salesQuotation->id) }}" method="POST" class="d-inline mr-2" onsubmit="return confirm('Are you sure you want to convert this Quotation into an official Sales Order (SO)?');">
                            @csrf
                            <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
                                <i class="fas fa-check-circle mr-1"></i> Convert to Sales Order (SO)
                            </button>
                        </form>
                    @else
                        <span class="badge badge-primary px-3 py-2 font-weight-bold mr-2" style="font-size: 0.9rem;">Converted to Sales Order</span>
                    @endif

                    <a href="{{ route('admin.sales-quotations.index') }}" class="btn btn-outline-secondary px-3 py-2 font-weight-bold" style="border-radius: 10px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-lg-8">
                    {{-- Customer & Summary --}}
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-user-tag mr-2 text-primary"></i> Customer Information</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted font-weight-bold text-uppercase d-block">Customer Name</small>
                                    <span class="font-weight-bold text-dark" style="font-size: 1.05rem;">{{ $salesQuotation->customer?->name ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted font-weight-bold text-uppercase d-block">Email Address</small>
                                    <span class="font-weight-semibold text-dark">{{ $salesQuotation->customer?->email ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <small class="text-muted font-weight-bold text-uppercase d-block">Validity Date</small>
                                    <span class="font-weight-bold text-info">{{ $salesQuotation->valid_until ? $salesQuotation->valid_until->format('d M, Y') : 'N/A' }}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <small class="text-muted font-weight-bold text-uppercase d-block">Incoterm</small>
                                    <span class="badge badge-dark px-3 py-1 font-weight-bold">{{ $salesQuotation->incoterm ?? 'EXW' }}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <small class="text-muted font-weight-bold text-uppercase d-block">Currency</small>
                                    <span class="badge badge-light border font-weight-bold">{{ $salesQuotation->currency?->code ?? 'DKK' }} (Rate: {{ number_format($salesQuotation->exchange_rate, 4) }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quotation Items Table --}}
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-2 text-primary"></i> Quoted Line Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                        <tr>
                                            <th class="pl-4">#</th>
                                            <th>Product Name</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right pr-4">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salesQuotation->items as $index => $item)
                                            <tr>
                                                <td class="pl-4 font-weight-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="font-weight-bold text-dark">
                                                    {{ $item->product?->name ?? 'Product' }}
                                                    @if($item->variant)
                                                        <br><small class="text-muted">Variant: {{ $item->variant->name }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $item->qty }}</td>
                                                <td class="text-right font-weight-semibold">
                                                    {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($item->unit_price, 2) }}
                                                </td>
                                                <td class="text-right pr-4 font-weight-bold text-dark">
                                                    {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($item->qty * $item->unit_price, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Side Card --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-calculator mr-2 text-primary"></i> Financial Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted font-weight-semibold">Subtotal:</span>
                                <span class="font-weight-bold text-dark">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->subtotal_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted font-weight-semibold">Tax / VAT Amount:</span>
                                <span class="font-weight-bold text-dark">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->tax_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted font-weight-semibold">Discount Amount:</span>
                                <span class="font-weight-bold text-danger">- {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->discount_amount, 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">Grand Total:</span>
                                <span class="font-weight-bold text-primary" style="font-size: 1.3rem;">
                                    {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->total_amount, 2) }}
                                </span>
                            </div>

                            @if($salesQuotation->notes)
                                <div class="p-3 bg-light rounded mb-3">
                                    <small class="text-uppercase font-weight-bold text-muted d-block mb-1">Notes & Terms</small>
                                    <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $salesQuotation->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
