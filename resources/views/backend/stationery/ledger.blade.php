@extends('backend.layouts.master')

@push('css')
<style>
    .executive-header-bar {
        border-bottom: 1px solid #e2e8f0;
        padding-top: 14px;
        padding-bottom: 18px;
        margin-bottom: 22px;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="executive-header-bar d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
        <div>
            <h1 class="font-weight-bold text-dark mb-1" style="font-size: 24px; letter-spacing: -0.025em; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;">
                Supplies Movement Audit Ledger
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Complete immutable chronological audit trail tracking stock inflows, outflows, and running balances.
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('admin.stationery-supplies.index') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #ffffff; color: #0f172a; border-radius: 8px; font-size: 12px; padding: 8px 16px; border: 1px solid #e2e8f0;">
                <i class="fas fa-boxes mr-1 text-muted"></i> Items Master
            </a>
            <a href="{{ route('admin.stationery-supplies.purchases.create') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #059669; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #059669;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Stock In
            </a>
            <a href="{{ route('admin.stationery-supplies.consumptions.create') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #d97706; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #d97706;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Issue to Dept
            </a>
        </div>
    </div>

    <div class="section-body">
        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.stationery-supplies.ledger') }}" class="form-row align-items-center">
                    <div class="col-md-3 my-1">
                        <select name="item_id" class="form-control select2">
                            <option value="">-- All Supply Items --</option>
                            @foreach($items as $sItem)
                                <option value="{{ $sItem->id }}" {{ request('item_id') == $sItem->id ? 'selected' : '' }}>
                                    {{ $sItem->name }} ({{ $sItem->item_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 my-1">
                        <select name="transaction_type" class="form-control">
                            <option value="">-- All Transaction Types --</option>
                            <option value="purchase_in" {{ request('transaction_type') == 'purchase_in' ? 'selected' : '' }}>📥 Purchase Stock In</option>
                            <option value="consumption_out" {{ request('transaction_type') == 'consumption_out' ? 'selected' : '' }}>📤 Department Issue (Stock Out)</option>
                            <option value="adjustment_in" {{ request('transaction_type') == 'adjustment_in' ? 'selected' : '' }}>➕ Stock Opening / Adjustment (+)</option>
                            <option value="adjustment_out" {{ request('transaction_type') == 'adjustment_out' ? 'selected' : '' }}>➖ Stock Adjustment (-)</option>
                        </select>
                    </div>

                    <div class="col-md-2 my-1">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                    </div>

                    <div class="col-md-2 my-1">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                    </div>

                    <div class="col-md-2 my-1 d-flex" style="gap: 5px;">
                        <button type="submit" class="btn font-weight-bold flex-fill shadow-sm" style="background: #0f172a; color: #fff; border-radius: 8px;">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.stationery-supplies.ledger') }}" class="btn btn-light border" title="Reset Filters" style="border-radius: 8px;">
                            <i class="fas fa-undo text-muted"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Movement Ledger Table --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                    <i class="fas fa-stream mr-2 text-primary"></i>Movement Audit Trail Records
                </h5>
                <span class="badge badge-light border text-muted px-2.5 py-1" style="font-size: 11.5px; border-radius: 6px;">
                    Showing {{ $ledgers->firstItem() ?? 0 }} - {{ $ledgers->lastItem() ?? 0 }} of {{ $ledgers->total() }} records
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px; color: #64748b;">
                            <tr>
                                <th>Date</th>
                                <th>Supply Item</th>
                                <th>Transaction Type</th>
                                <th>Reference Voucher #</th>
                                <th class="text-center" style="background: #f8fafc;">Before</th>
                                <th class="text-center text-success" style="background: #f0fdf4;">Qty In (+)</th>
                                <th class="text-center text-danger" style="background: #fef2f2;">Qty Out (-)</th>
                                <th class="text-center font-weight-bold" style="background: #f1f5f9;">Balance</th>
                                <th class="text-right">Unit Cost</th>
                                <th class="text-right">Total Value</th>
                                <th>Audited By & Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgers as $log)
                                @php
                                    $badgeClass = match($log->transaction_type) {
                                        'purchase_in' => 'badge-success',
                                        'consumption_out' => 'badge-warning text-dark',
                                        'adjustment_in' => 'badge-info',
                                        'adjustment_out' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };
                                    $icon = match($log->transaction_type) {
                                        'purchase_in' => 'fa-truck-loading',
                                        'consumption_out' => 'fa-hand-holding',
                                        'adjustment_in' => 'fa-plus',
                                        'adjustment_out' => 'fa-minus',
                                        default => 'fa-exchange-alt',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="text-dark">{{ $log->date->format('d M, Y') }}</strong>
                                        <small class="text-muted d-block" style="font-size: 10px;">{{ $log->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <img src="{{ $log->item->image_url ?? asset('uploads/no-image.svg') }}" 
                                                 loading="lazy" decoding="async"
                                                 onerror="this.onerror=null;this.src='{{ asset('uploads/no-image.svg') }}';" 
                                                 style="width: 32px; height: 32px; object-fit: contain; border-radius: 6px; background: #f8fafc; padding: 2px;" 
                                                 class="border flex-shrink-0" alt="">
                                            <div class="min-w-0">
                                                <strong class="text-dark d-block text-truncate" style="font-size: 13px;" title="{{ $log->item->name ?? 'Item' }}">
                                                    {{ $log->item->name ?? 'Deleted Item' }}
                                                </strong>
                                                <span class="badge badge-light border text-muted" style="font-size: 9.5px;">
                                                    {{ $log->item->item_code ?? 'N/A' }} [{{ $log->item->unit_name ?? 'pcs' }}]
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }} px-2 py-1 font-weight-bold" style="font-size: 10px; border-radius: 4px;">
                                            <i class="fas {{ $icon }} mr-1"></i>{{ $log->transaction_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px;">
                                            {{ $log->reference_no ?: 'MANUAL' }}
                                        </span>
                                    </td>
                                    <td class="text-center font-weight-bold text-muted" style="background: #f8fafc;">
                                        {{ number_format($log->stock_before) }}
                                    </td>
                                    <td class="text-center font-weight-bold text-success" style="background: #f0fdf4;">
                                        {{ $log->qty_in > 0 ? '+' . number_format($log->qty_in) : '-' }}
                                    </td>
                                    <td class="text-center font-weight-bold text-danger" style="background: #fef2f2;">
                                        {{ $log->qty_out > 0 ? '-' . number_format($log->qty_out) : '-' }}
                                    </td>
                                    <td class="text-center font-weight-bold text-dark" style="background: #f1f5f9; font-size: 13px;">
                                        {{ number_format($log->stock_after) }}
                                    </td>
                                    <td class="text-right text-muted" style="font-size: 12px;">
                                        {!! formatWithCurrency($log->unit_cost) !!}
                                    </td>
                                    <td class="text-right font-weight-bold text-dark" style="font-size: 12.5px;">
                                        {!! formatWithCurrency($log->total_value) !!}
                                    </td>
                                    <td>
                                        <div class="text-muted" style="font-size: 11.5px;">
                                            <i class="fas fa-user-circle mr-1"></i>{{ $log->creator->name ?? 'System' }}
                                        </div>
                                        @if($log->notes)
                                            <small class="text-muted d-block font-italic text-truncate" style="max-width: 180px;" title="{{ $log->notes }}">
                                                {{ $log->notes }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <p class="mb-0 font-weight-bold">No stock movement ledger records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap" style="gap: 12px;">
                    <div class="text-muted small" style="color: #64748b; font-size: 13px;">
                        Showing {{ $ledgers->firstItem() ?? ($ledgers->total() > 0 ? 1 : 0) }} to {{ $ledgers->lastItem() ?? $ledgers->total() }} of {{ number_format($ledgers->total()) }} entries
                    </div>
                    <div>
                        {{ $ledgers->withQueryString()->links('vendor.pagination.custom-report') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
