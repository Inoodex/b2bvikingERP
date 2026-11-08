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
                <h1 class="font-weight-bold text-dark mb-1"
                    style="font-size: 24px; letter-spacing: -0.025em; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;">
                    Department Issues (Stock Out)
                </h1>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Internal supply consumption vouchers tracked to operational cost centers.
                </p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.stationery-supplies.index') }}"
                    class="btn font-weight-bold d-inline-flex align-items-center shadow-sm"
                    style="background: #ffffff; color: #0f172a; border-radius: 8px; font-size: 12px; padding: 8px 16px; border: 1px solid #e2e8f0;">
                    <i class="fas fa-boxes mr-1 text-muted"></i> Items Master
                </a>
                <a href="{{ route('admin.stationery-supplies.consumptions.create') }}"
                    class="btn font-weight-bold d-inline-flex align-items-center shadow-sm"
                    style="background: #d97706; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #d97706;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Issue to Department
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap"
                    style="gap: 10px;">
                    <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                        <i class="fas fa-file-export mr-2 text-primary"></i>Department Issue History & Expense
                    </h5>
                    <form method="GET" action="{{ route('admin.stationery-supplies.consumptions') }}" class="d-flex"
                        style="gap: 5px;">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search issue #, receiver, dept..." value="{{ request('search') }}"
                            style="border-radius: 6px;">
                        <button type="submit" class="btn btn-sm btn-dark" style="border-radius: 6px;"><i
                                class="fas fa-search"></i></button>
                        @if (request('search'))
                            <a href="{{ route('admin.stationery-supplies.consumptions') }}"
                                class="btn btn-sm btn-light border" style="border-radius: 6px;"><i
                                    class="fas fa-times"></i></a>
                        @endif
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-uppercase"
                                style="font-size: 10.5px; letter-spacing: 0.5px; color: #64748b;">
                                <tr>
                                    <th class="text-center" style="width: 170px;">Issue Voucher #</th>
                                    <th>Date</th>
                                    <th>Receiving Department</th>
                                    <th>Received By</th>
                                    <th>Issued Items</th>
                                    <th class="text-right">Total Expense</th>
                                    <th>Issued By</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consumptions as $iss)
                                    @php
                                        $itemsPayload = $iss->items->map(function($it) {
                                            return [
                                                'name' => $it->stationeryItem->name ?? 'Item',
                                                'code' => $it->stationeryItem->item_code ?? '-',
                                                'unit' => $it->stationeryItem->unit_name ?? 'pcs',
                                                'qty' => number_format($it->quantity),
                                                'cost' => formatWithCurrency($it->unit_cost),
                                                'subtotal' => formatWithCurrency($it->subtotal),
                                                'image' => $it->stationeryItem->image_url ?? asset('uploads/no-image.svg'),
                                            ];
                                        })->values()->all();
                                        $itemsJsonStr = json_encode($itemsPayload);
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold">
                                            <span class="badge badge-light border text-dark font-weight-bold"
                                                style="font-size: 11px; border-radius: 4px;">
                                                {{ $iss->issue_no }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark">{{ $iss->issue_date->format('d M, Y') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border text-dark font-weight-bold"
                                                style="font-size: 11px; border-radius: 4px;">
                                                {{ $iss->display_department }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark">{{ $iss->issued_to ?: 'Department Staff' }}</strong>
                                            @if ($iss->purpose_notes)
                                                <small class="text-muted d-block font-italic"
                                                    style="font-size: 10.5px;">{{ $iss->purpose_notes }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light border text-dark font-weight-600 px-2.5 py-1.5" style="border-radius: 6px; font-size: 11.5px; background: #f8fafc;">
                                                <i class="fas fa-boxes mr-1 text-warning"></i> {{ $iss->items->count() }} {{ Str::plural('Item', $iss->items->count()) }} ({{ number_format($iss->items->sum('quantity')) }} Units)
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold text-danger" style="font-size: 13.5px;">
                                            {!! formatWithCurrency($iss->total_cost) !!}
                                        </td>
                                        <td class="text-muted" style="font-size: 12px;">
                                            <i class="fas fa-user-circle mr-1"></i>{{ $iss->creator->name ?? 'Admin' }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning view-consumption-items-btn" 
                                                    style="border-radius: 6px; padding: 4px 8px;"
                                                    data-voucher="{{ $iss->issue_no }}"
                                                    data-date="{{ $iss->issue_date->format('d M, Y') }}"
                                                    data-department="{{ $iss->display_department }}"
                                                    data-receiver="{{ $iss->issued_to ?: 'Department Staff' }}"
                                                    data-creator="{{ $iss->creator->name ?? 'Admin' }}"
                                                    data-notes="{{ $iss->purpose_notes }}"
                                                    data-total="{!! formatWithCurrency($iss->total_cost) !!}"
                                                    data-items="{{ $itemsJsonStr }}"
                                                    title="View Voucher Breakdown">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <p class="mb-2 font-weight-bold">No supplies issued to departments yet.</p>
                                            <a href="{{ route('admin.stationery-supplies.consumptions.create') }}"
                                                class="btn btn-sm font-weight-bold"
                                                style="background: #d97706; color: #fff; border-radius: 6px;">
                                                <i class="fas fa-plus-circle mr-1"></i> Issue First Supply Batch
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap" style="gap: 12px;">
                        <div class="text-muted small" style="color: #64748b; font-size: 13px;">
                            Showing {{ $consumptions->firstItem() ?? ($consumptions->total() > 0 ? 1 : 0) }} to {{ $consumptions->lastItem() ?? $consumptions->total() }} of {{ number_format($consumptions->total()) }} entries
                        </div>
                        <div>
                            {{ $consumptions->withQueryString()->links('vendor.pagination.custom-report') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{-- MODAL: View Consumption Items Breakdown --}}
<div class="modal fade" id="consumptionItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header px-4 py-3" style="background: #0f172a; color: #fff;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dolly text-warning" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="modalConsumptionVoucher">ST-ISS-0001</h5>
                        <small class="text-white-50" id="modalConsumptionDate">08 Nov, 2026</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4" style="background: #f8fafc;">
                {{-- Meta Info Bar --}}
                <div class="row mb-3 bg-white p-3 rounded border shadow-sm mx-0" style="gap: 12px 0;">
                    <div class="col-sm-4">
                        <small class="text-muted text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Receiving Department</small>
                        <strong class="text-dark" id="modalConsumptionDept">-</strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Received By / Staff</small>
                        <strong class="text-dark" id="modalConsumptionReceiver">-</strong>
                    </div>
                    <div class="col-sm-4 text-sm-right">
                        <small class="text-muted text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Operating Expense</small>
                        <span class="badge badge-danger px-2 py-1 font-weight-bold" id="modalConsumptionTotalBadge">Expense</span>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="card border shadow-sm mb-0 overflow-hidden">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark small"><i class="fas fa-boxes text-warning mr-1"></i> Issued Supplies / Packaging Items</span>
                        <span class="badge badge-warning text-dark font-weight-bold" id="modalConsumptionItemCount">0 Items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="bg-light text-uppercase" style="font-size: 10px; letter-spacing: 0.5px; color: #64748b;">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Item Details</th>
                                    <th class="text-center">Quantity Issued</th>
                                    <th class="text-right">Unit Cost (WAC)</th>
                                    <th class="text-right">Subtotal Cost</th>
                                </tr>
                            </thead>
                            <tbody id="modalConsumptionItemsBody">
                                {{-- Filled via JS --}}
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="4" class="text-right">Grand Total Cost:</td>
                                    <td class="text-right text-danger" id="modalConsumptionGrandTotal" style="font-size: 14px;">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mt-3 p-2.5 rounded bg-white border" id="modalConsumptionNotesWrap" style="display: none;">
                    <small class="text-muted d-block font-weight-bold" style="font-size: 10.5px;">Purpose / Notes:</small>
                    <small class="text-dark" id="modalConsumptionNotes"></small>
                </div>
            </div>

            <div class="modal-footer bg-white border-top px-4 py-2.5 d-flex justify-content-between">
                <small class="text-muted" id="modalConsumptionCreator"></small>
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-consumption-items-btn').on('click', function() {
        const btn = $(this);
        const voucher = btn.data('voucher');
        const date = btn.data('date');
        const department = btn.data('department');
        const receiver = btn.data('receiver');
        const creator = btn.data('creator');
        const notes = btn.data('notes');
        const total = btn.data('total');
        const items = btn.data('items') || [];

        $('#modalConsumptionVoucher').text(voucher);
        $('#modalConsumptionDate').text(date);
        $('#modalConsumptionDept').text(department);
        $('#modalConsumptionReceiver').text(receiver);
        $('#modalConsumptionTotalBadge').html(total);
        $('#modalConsumptionCreator').html('<i class="fas fa-user-circle mr-1"></i> Issued by: <strong>' + creator + '</strong>');
        $('#modalConsumptionGrandTotal').html(total);
        $('#modalConsumptionItemCount').text(items.length + (items.length === 1 ? ' Item' : ' Items'));

        if (notes && notes.trim() !== '') {
            $('#modalConsumptionNotes').text(notes);
            $('#modalConsumptionNotesWrap').show();
        } else {
            $('#modalConsumptionNotesWrap').hide();
        }

        let html = '';
        items.forEach(function(item, idx) {
            html += `
                <tr>
                    <td class="text-center text-muted font-weight-bold">${idx + 1}</td>
                    <td>
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <img src="${item.image}" 
                                 onerror="this.onerror=null;this.src='{{ asset('uploads/no-image.svg') }}';" 
                                 style="width: 34px; height: 34px; object-fit: contain; border-radius: 6px; background: #f8fafc; padding: 2px;" 
                                 class="border flex-shrink-0" alt="">
                            <div class="min-w-0">
                                <span class="badge badge-light border text-dark font-weight-bold mr-1" style="font-size: 10px;">${item.code}</span>
                                <strong class="text-dark d-inline" style="font-size: 13px;">${item.name}</strong>
                            </div>
                        </div>
                    </td>
                    <td class="text-center font-weight-bold text-dark">
                        ${item.qty} <span class="text-muted font-weight-normal" style="font-size: 11px;">${item.unit}</span>
                    </td>
                    <td class="text-right text-muted">${item.cost}</td>
                    <td class="text-right font-weight-bold text-danger">${item.subtotal}</td>
                </tr>
            `;
        });

        $('#modalConsumptionItemsBody').html(html);
        $('#consumptionItemsModal').modal('show');
    });
});
</script>
@endpush
