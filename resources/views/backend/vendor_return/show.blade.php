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
                                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">{{ number_format($item->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-right font-weight-bold">Total Debit Claim:</td>
                                        <td class="text-right font-weight-bold text-danger" style="font-size: 1.2em;">
                                            {{ number_format($grandTotal, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i> This Debit Note serves as an official AP claim against {{ $vendorReturn->purchase?->vendor?->name }} accounts payable balance.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
