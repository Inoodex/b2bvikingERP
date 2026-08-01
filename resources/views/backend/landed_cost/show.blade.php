@extends('backend.layouts.master')

@section('title', 'Landed Cost Allocation Matrix')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.purchase-orders.show', $purchase->id) }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Weighted Average Landed Cost Matrix</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">PO Register</a></div>
            <div class="breadcrumb-item">Landed Cost Matrix</div>
        </div>
    </div>

    <div class="section-body">
        <div class="card card-primary shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h4 class="text-dark font-weight-bold mb-0"><i class="fas fa-calculator text-primary mr-2"></i> Landed Cost Allocation: <code class="text-primary">{{ $purchase->po_no ?? 'PO #'.$purchase->id }}</code></h4>
                <a href="{{ route('admin.landed-cost.recalculate', $purchase->id) }}" class="btn btn-primary font-weight-bold btn-sm shadow-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Recalculate Matrix
                </a>
            </div>
            <div class="card-body">
                <!-- Executive Summary Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <div class="p-3 bg-light rounded border border-primary-subtle d-flex align-items-center">
                            <div class="bg-primary text-white rounded p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 11px;">PO Base Total</small>
                                <strong class="text-dark" style="font-size: 18px;">
                                    {{ $purchase->currency?->symbol ?? '$' }} {{ number_format($purchase->foreign_amount ?? $purchase->total_amount, 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <div class="p-3 bg-light rounded border border-danger-subtle d-flex align-items-center">
                            <div class="bg-danger text-white rounded p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-plane-arrival fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 11px;">Total LC Expenses</small>
                                <strong class="text-danger" style="font-size: 18px;">
                                    {{ $purchase->currency?->symbol ?? '$' }} {{ number_format($purchase->letterOfCredit?->expenses->where('goes_to_unit_cost', 1)->sum('amount') ?? 0, 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <div class="p-3 bg-light rounded border border-warning-subtle d-flex align-items-center">
                            <div class="bg-warning text-white rounded p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-university fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 11px;">LC / PI Reference</small>
                                <strong class="text-dark" style="font-size: 16px;">
                                    {{ $purchase->letterOfCredit?->lc_no ?? $purchase->proformaInvoice?->pi_no ?? 'N/A' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <div class="p-3 bg-light rounded border border-success-subtle d-flex align-items-center">
                            <div class="bg-success text-white rounded p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-boxes fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 11px;">Line SKUs</small>
                                <strong class="text-dark" style="font-size: 18px;">
                                    {{ count($matrix) }} Item(s)
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clean Table Matrix -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover border">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th class="text-center font-weight-bold" width="4%">#</th>
                                <th class="font-weight-bold" width="28%">Product Description</th>
                                <th class="font-weight-bold text-center" width="10%">Variant</th>
                                <th class="text-right font-weight-bold" width="12%">Accepted Qty</th>
                                <th class="text-right font-weight-bold" width="14%">PO Base Cost</th>
                                <th class="text-right font-weight-bold text-danger" width="16%">Allocated LC Overhead</th>
                                <th class="text-right font-weight-bold text-success" width="16%">True Landed Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $symbol = $purchase->currency?->symbol ?? '$'; @endphp
                            @foreach($matrix as $index => $row)
                                @php
                                    $acceptedQty = max(1, (float)$row['accepted_qty']);
                                    $totalOverheadForLine = (float)$row['allocated_overhead'];
                                    $overheadPerUnit = $totalOverheadForLine / $acceptedQty;
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td><strong class="text-dark">{{ $row['product_name'] }}</strong></td>
                                    <td class="text-center text-muted">{{ $row['variant_name'] ?: '—' }}</td>
                                    <td class="text-right font-weight-bold text-dark">{{ number_format($row['accepted_qty'], 2) }}</td>
                                    <td class="text-right font-weight-bold text-muted">{{ $symbol }} {{ number_format($row['base_unit_cost'], 2) }} / unit</td>
                                    <td class="text-right font-weight-bold text-danger">
                                        + {{ $symbol }} {{ number_format($overheadPerUnit, 2) }} / unit
                                        <small class="d-block text-muted font-weight-normal" style="font-size: 11px;">Line Total: {{ $symbol }} {{ number_format($totalOverheadForLine, 2) }}</small>
                                    </td>
                                    <td class="text-right font-weight-bold text-success" style="font-size: 16px;">
                                        {{ $symbol }} {{ number_format($row['landed_unit_cost'], 2) }} <small class="d-block text-muted font-weight-normal" style="font-size: 11px;">/ unit</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-light border shadow-none mt-3 mb-0">
                    <i class="fas fa-info-circle text-primary mr-1"></i> <strong>Weighted Average Allocation Formula:</strong>  
                    <code>Item Overhead = Total LC Expenses &times; (Item Line Subtotal / Total PO Amount)</code>.  
                    The <strong>True Landed Cost</strong> is automatically synchronized with warehouse inventory stock ledgers upon GRN approval.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
