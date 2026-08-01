@extends('backend.layouts.master')

@section('title', 'Process Vendor Return')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Process Vendor Return from GRN QC Rejection</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.vendor-returns.index') }}">Vendor Returns</a></div>
            <div class="breadcrumb-item active">New Return</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('admin.vendor-returns.store') }}" method="POST">
            @csrf
            <input type="hidden" name="goods_receipt_id" value="{{ $grn->id }}">

            <div class="card card-warning">
                <div class="card-header">
                    <h4><i class="fas fa-undo text-warning mr-2"></i> Return Details for GRN: <code>{{ $grn->grn_no }}</code></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Supplier / Vendor</label>
                            <input type="text" class="form-control" value="{{ $grn->purchase?->vendor?->name }}" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="reason">General Return Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Quality Control Inspection Rejection">
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 text-primary"><i class="fas fa-list mr-1"></i> Rejected Items List</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-warning text-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Variant</th>
                                    <th class="text-right">QC Rejected Qty</th>
                                    <th class="text-right" style="width: 150px;">Unit Price</th>
                                    <th>Rejection Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rejectedItems as $index => $item)
                                    @php
                                        // Find PO unit price
                                        $poDetail = $grn->purchase?->items
                                            ->where('product_id', $item->product_id)
                                            ->where('variant_id', $item->variant_id)
                                            ->first();
                                        $unitPrice = $poDetail ? (float)$poDetail->unit_cost : 0.00;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->product?->name }}</strong>
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="items[{{ $index }}][variant_id]" value="{{ $item->variant_id }}">
                                        </td>
                                        <td>{{ $item->variant?->name ?? '-' }}</td>
                                        <td class="text-right font-weight-bold text-danger">
                                            <input type="number" step="0.01" name="items[{{ $index }}][qty]" value="{{ $item->rejected_qty }}" class="form-control text-right" readonly>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $unitPrice }}" class="form-control text-right" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][reason]" value="{{ $item->rejection_reason ?? 'QC Rejection' }}" class="form-control">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group text-right mt-4">
                        <a href="{{ route('admin.goods-receipts.show', $grn->id) }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-file-invoice-dollar mr-1"></i> Issue Debit Note & Submit Return</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
