@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>View Issue {{ $issue->issue_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.issues.index') }}">Stock Issues</a></div>
                <div class="breadcrumb-item">{{ $issue->issue_no }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Stock Issue</h2>
                                <div class="invoice-number">{{ $issue->issue_no }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Issue Date:</strong><br>
                                        {{ $issue->created_at->format('d F, Y') }}<br><br>
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Note:</strong><br>
                                        {{ $issue->note ?? 'N/A' }}
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Issued Items</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tr>
                                        <th data-width="40">#</th>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th class="text-center">Quantity</th>
                                    </tr>
                                    @foreach ($issue->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product->name }}</strong><br>
                                                <span style="font-size: 11px; color: #666;">Product No: {{ $item->product->product_number ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($item->variant)
                                                    @php
                                                        $variantColorName = is_object($item->variant->color ?? null)
                                                            ? ($item->variant->color->name ?? '')
                                                            : ((string) ($item->variant->color ?? ''));
                                                        $variantSizeName = is_object($item->variant->size ?? null)
                                                            ? ($item->variant->size->name ?? '')
                                                            : ((string) ($item->variant->size ?? ''));
                                                    @endphp
                                                    {{ $item->variant->name }}
                                                    @if($variantColorName || $variantSizeName)
                                                        ({{ $variantColorName }}{{ $variantColorName && $variantSizeName ? ' / ' : '' }}{{ $variantSizeName }})
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Total Quantity</div>
                                        <div class="invoice-detail-value">{{ $issue->total_qty }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                {{-- <div class="text-md-right">
                    <button class="btn btn-warning btn-icon icon-left" onclick="window.print();"><i class="fas fa-print"></i> Print</button>
                </div> --}}
            </div>
        </div>
    </section>
@endsection
