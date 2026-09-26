@extends('backend.layouts.master')

@section('title', 'Sales Quotation Details - ' . $salesQuotation->quotation_no)

@section('content')
    <section class="section">
        {{-- Section Header --}}
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.sales-quotations.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Sales Quotation #{{ $salesQuotation->quotation_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales-quotations.index') }}">Sales Quotations</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        {{-- Top Action Bar --}}
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <span class="text-muted font-weight-bold mr-2">Status:</span>
                    @if($salesQuotation->status === 'converted')
                        <span class="badge badge-primary px-3 py-1 font-weight-bold">Converted to Sales Order</span>
                    @elseif($salesQuotation->status === 'accepted')
                        <span class="badge badge-success px-3 py-1 font-weight-bold">Accepted</span>
                    @elseif($salesQuotation->status === 'declined')
                        <span class="badge badge-danger px-3 py-1 font-weight-bold">Declined</span>
                    @else
                        <span class="badge badge-warning px-3 py-1 font-weight-bold">Draft / Pending</span>
                    @endif
                </div>

                <div class="d-flex align-items-center flex-wrap">
                    @if($salesQuotation->status === 'draft')
                        <a href="{{ route('admin.sales-quotations.edit', $salesQuotation->id) }}" class="btn btn-warning btn-sm font-weight-bold text-dark mr-2">
                            <i class="fas fa-edit mr-1"></i> Edit Quote
                        </a>
                    @endif

                    <a href="{{ route('admin.sales-quotations.pdf', $salesQuotation->id) }}" class="btn btn-info btn-sm font-weight-bold mr-2" target="_blank" title="Standard Quotation PDF">
                        <i class="fas fa-file-pdf mr-1"></i> SQ PDF
                    </a>

                    <a href="{{ route('admin.sales-quotations.excel', $salesQuotation->id) }}" class="btn btn-success btn-sm font-weight-bold mr-2" title="Download Excel Order Sheet with blank Order Qty column for buyers">
                        <i class="fas fa-file-excel mr-1"></i> Excel Order Sheet
                    </a>

                    <button type="button" class="btn btn-primary btn-sm font-weight-bold mr-2" id="btn_async_lookbook" title="Generate High-Res Visual Lookbook / Catalog PDF with Images & Barcodes">
                        <i class="fas fa-book-open mr-1"></i> Visual Lookbook
                    </button>

                    <form action="{{ route('admin.sales-quotations.clone', $salesQuotation->id) }}" method="POST" class="d-inline mr-2">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold" title="Clone Quotation">
                            <i class="fas fa-copy mr-1"></i> Clone Quote
                        </button>
                    </form>

                    @if($salesQuotation->status !== 'converted')
                        <form action="{{ route('admin.sales-quotations.convert-to-order', $salesQuotation->id) }}" method="POST" id="convertForm" class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btnConvertSo">
                                <i class="fas fa-check-circle mr-1"></i> Convert to Sales Order (SO)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body Content --}}
        <div class="section-body">
            <div class="row">
                {{-- Left Column: Quoted Items Table --}}
                <div class="col-12 col-lg-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-list mr-2"></i>Quotation Items ({{ $salesQuotation->items->count() }})</h4>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                                        <tr>
                                            <th class="text-center" width="8%">#</th>
                                            <th>Product Name</th>
                                            <th class="text-center" width="15%">Qty</th>
                                            <th class="text-right" width="20%">Unit Price</th>
                                            <th class="text-right pr-4" width="20%">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salesQuotation->items as $index => $item)
                                            <tr>
                                                <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $item->product?->name ?? 'Product' }}</strong>
                                                    @if($item->variant)
                                                        <br><small class="text-muted">Variant: {{ $item->variant->name }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light border">{{ $item->qty }}</span>
                                                </td>
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

                    @if($salesQuotation->clean_notes)
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h4><i class="fas fa-comment-alt mr-2"></i>Notes & Commercial Terms</h4>
                            </div>
                            <div class="card-body py-3">
                                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $salesQuotation->clean_notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Customer Info & Financial Summary --}}
                <div class="col-12 col-lg-4">
                    {{-- Status & Summary Card --}}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle mr-2"></i>Quotation Info</h4>
                        </div>
                        <div class="card-body">
                            <div class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted font-weight-bold">Issued Date:</span>
                                <span class="font-weight-bold text-dark">{{ $salesQuotation->created_at->format('d M, Y') }}</span>
                            </div>

                            <div class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted font-weight-bold">Valid Until:</span>
                                <span class="font-weight-bold text-info">{{ $salesQuotation->valid_until ? $salesQuotation->valid_until->format('d M, Y') : 'N/A' }}</span>
                            </div>

                            <div class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted font-weight-bold">Incoterm:</span>
                                <span class="badge badge-dark">{{ $salesQuotation->incoterm ?? 'EXW' }}</span>
                            </div>

                            <div class="py-2 d-flex justify-content-between align-items-center">
                                <span class="text-muted font-weight-bold">Currency:</span>
                                <span class="font-weight-bold text-dark">{{ $salesQuotation->currency?->code ?? 'DKK' }} (Rate: {{ number_format($salesQuotation->exchange_rate, 4) }})</span>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Details Card --}}
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-user mr-2"></i>{{ $salesQuotation->is_prospect ? 'Prospect Buyer Info' : 'Customer Info' }}</h4>
                            @if($salesQuotation->is_prospect)
                                <span class="badge badge-primary px-2 py-1" style="font-size: 10px;">Direct Prospect</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted text-uppercase font-weight-bold d-block">{{ $salesQuotation->is_prospect ? 'Prospect / Business Name' : 'Name' }}</small>
                                <span class="font-weight-bold text-dark">{{ $salesQuotation->buyer_display_name }}</span>
                            </div>
                            @if($salesQuotation->is_prospect)
                                <div class="mb-2">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Phone Number</small>
                                    <span>{{ $salesQuotation->buyer_phone ?: 'N/A' }}</span>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Account Status</small>
                                    <span class="badge badge-light border text-muted">Direct Commercial Lead</span>
                                </div>
                            @else
                                <div class="mb-2">
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Email</small>
                                    <span>{{ $salesQuotation->customer?->email ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Phone</small>
                                    <span>{{ $salesQuotation->customer?->phone ?? 'N/A' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Summary Card --}}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-calculator mr-2"></i>Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Subtotal:</span>
                                <span class="font-weight-bold">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->subtotal_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Tax / VAT:</span>
                                <span class="font-weight-bold">{{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->tax_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">Discount:</span>
                                <span class="font-weight-bold text-danger">- {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->discount_amount, 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-dark">Grand Total:</h5>
                                <h4 class="mb-0 text-primary font-weight-bold">
                                    {{ $salesQuotation->currency?->symbol ?? 'kr.' }} {{ number_format($salesQuotation->total_amount, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#btnConvertSo').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Convert to Sales Order?',
                    text: 'Are you sure you want to convert Quotation {{ $salesQuotation->quotation_no }} into an official Sales Order (SO)?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Convert to SO!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#convertForm').submit();
                    }
                });
            });

            // Async Lookbook PDF Generation with Zero-Crash Polling
            let lookbookPollingInterval = null;
            $('#btn_async_lookbook').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Preparing Lookbook...');

                $.ajax({
                    url: "{{ route('admin.sales-quotations.catalog-pdf.async', $salesQuotation->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        toastr.info('Visual Lookbook generation started in background. Please wait...', 'Generating PDF');
                        const dispatchedAt = res.dispatched_at || Math.floor(Date.now() / 1000);

                        // Poll every 2.5 seconds
                        lookbookPollingInterval = setInterval(function() {
                            $.ajax({
                                url: "{{ route('admin.sales-quotations.catalog-pdf.status') }}",
                                type: "GET",
                                data: {
                                    quotation_id: "{{ $salesQuotation->id }}",
                                    after: dispatchedAt
                                },
                                success: function(statusRes) {
                                    if (statusRes.ready && statusRes.download_url) {
                                        clearInterval(lookbookPollingInterval);
                                        $btn.prop('disabled', false).html('<i class="fas fa-book-open mr-1"></i> Visual Lookbook');
                                        toastr.success('Visual Lookbook PDF is ready! Starting download...', 'Success');
                                        window.location.href = statusRes.download_url;
                                    }
                                }
                            });
                        }, 2500);
                    },
                    error: function(err) {
                        $btn.prop('disabled', false).html('<i class="fas fa-book-open mr-1"></i> Visual Lookbook');
                        toastr.error('Failed to initiate Lookbook generation: ' + (err.responseJSON?.message || 'Server error'));
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
