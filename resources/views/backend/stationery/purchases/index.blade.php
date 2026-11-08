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
                Supplies Purchases (Stock In)
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Sequential vendor purchase vouchers with automated Weighted Average Cost (WAC) calculations.
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('admin.stationery-supplies.index') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #ffffff; color: #0f172a; border-radius: 8px; font-size: 12px; padding: 8px 16px; border: 1px solid #e2e8f0;">
                <i class="fas fa-boxes mr-1 text-muted"></i> Items Master
            </a>
            <a href="{{ route('admin.stationery-supplies.purchases.create') }}" class="btn font-weight-bold d-inline-flex align-items-center shadow-sm" style="background: #059669; color: #ffffff; border-radius: 8px; font-size: 12px; padding: 8px 16px; gap: 6px; border: 1px solid #059669;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Purchase Inward
            </a>
        </div>
    </div>

    <div class="section-body">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                    <i class="fas fa-file-invoice mr-2 text-primary"></i>Purchase Inward Vouchers
                </h5>
                <form method="GET" action="{{ route('admin.stationery-supplies.purchases') }}" class="d-flex" style="gap: 5px;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search PO #, invoice #..." value="{{ request('search') }}" style="border-radius: 6px;">
                    <button type="submit" class="btn btn-sm btn-dark" style="border-radius: 6px;"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('admin.stationery-supplies.purchases') }}" class="btn btn-sm btn-light border" style="border-radius: 6px;"><i class="fas fa-times"></i></a>
                    @endif
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px; color: #64748b;">
                            <tr>
                                <th class="text-center" style="width: 170px;">Purchase Voucher</th>
                                <th>Date</th>
                                <th>Purchased From</th>
                                <th>Purchased Items</th>
                                <th class="text-right">Total Amount</th>
                                <th class="text-center">Status</th>
                                <th>Recorded By</th>
                                <th class="text-center" style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $po)
                                @php
                                    $itemsPayload = $po->items->map(function($it) {
                                        return [
                                            'name' => $it->stationeryItem->name ?? 'Item',
                                            'code' => $it->stationeryItem->item_code ?? '-',
                                            'unit' => $it->stationeryItem->unit_name ?? 'pcs',
                                            'qty' => number_format($it->quantity),
                                            'price' => formatWithCurrency($it->unit_price),
                                            'subtotal' => formatWithCurrency($it->subtotal),
                                            'image' => $it->stationeryItem->image_url ?? asset('uploads/no-image.svg'),
                                        ];
                                    })->values()->all();
                                    $itemsJsonStr = json_encode($itemsPayload);
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px; border-radius: 4px;">
                                            {{ $po->purchase_no }}
                                        </span>
                                        @if($po->invoice_no)
                                            <small class="text-muted d-block" style="font-size: 10px;">Inv: {{ $po->invoice_no }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $po->purchase_date->format('d M, Y') }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block">{{ $po->display_vendor }}</strong>
                                        @if($po->vendor && $po->vendor->phone)
                                            <small class="text-muted">{{ $po->vendor->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-weight-600 px-2.5 py-1.5" style="border-radius: 6px; font-size: 11.5px; background: #f8fafc;">
                                            <i class="fas fa-boxes mr-1 text-primary"></i> {{ $po->items->count() }} {{ Str::plural('Item', $po->items->count()) }} ({{ number_format($po->items->sum('quantity')) }} Units)
                                        </span>
                                    </td>
                                    <td class="text-right font-weight-bold text-success" style="font-size: 13.5px;">
                                        {!! formatWithCurrency($po->total_amount) !!}
                                    </td>
                                    <td class="text-center">
                                        @if($po->payment_status === 'paid')
                                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 10px; border-radius: 4px;">
                                                <i class="fas fa-check-circle mr-1"></i> Paid
                                            </span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 10px; border-radius: 4px;" title="Due / Accounts Payable">
                                                <i class="fas fa-clock mr-1"></i> Unpaid (Due)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size: 12px;">
                                        <i class="fas fa-user-circle mr-1"></i>{{ $po->creator->name ?? 'Admin' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-purchase-items-btn" 
                                                    style="border-radius: 6px; padding: 4px 8px;"
                                                    data-voucher="{{ $po->purchase_no }}"
                                                    data-invoice="{{ $po->invoice_no ?? '-' }}"
                                                    data-date="{{ $po->purchase_date->format('d M, Y') }}"
                                                    data-vendor="{{ $po->display_vendor }}"
                                                    data-creator="{{ $po->creator->name ?? 'Admin' }}"
                                                    data-status="{{ ucfirst($po->payment_status) }}"
                                                    data-notes="{{ $po->notes }}"
                                                    data-total="{!! formatWithCurrency($po->total_amount) !!}"
                                                    data-items="{{ $itemsJsonStr }}"
                                                    title="View Voucher Breakdown">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($po->payment_status !== 'paid')
                                                <button type="button" class="btn btn-sm btn-outline-success settle-due-btn"
                                                    data-voucher="{{ $po->purchase_no }}"
                                                    data-vendor="{{ $po->display_vendor }}"
                                                    data-amount="{!! formatWithCurrency($po->total_amount) !!}"
                                                    data-action="{{ route('admin.stationery-supplies.purchases.settle-payment', $po->id) }}"
                                                    style="border-radius: 6px; padding: 4px 8px;"
                                                    title="Settle Due Payment">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <p class="mb-2 font-weight-bold">No stationery purchases recorded yet.</p>
                                        <a href="{{ route('admin.stationery-supplies.purchases.create') }}" class="btn btn-sm font-weight-bold" style="background: #059669; color: #fff; border-radius: 6px;">
                                            <i class="fas fa-plus-circle mr-1"></i> Record Stock Inward
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top flex-wrap" style="gap: 12px;">
                    <div class="text-muted small" style="color: #64748b; font-size: 13px;">
                        Showing {{ $purchases->firstItem() ?? ($purchases->total() > 0 ? 1 : 0) }} to {{ $purchases->lastItem() ?? $purchases->total() }} of {{ number_format($purchases->total()) }} entries
                    </div>
                    <div>
                        {{ $purchases->withQueryString()->links('vendor.pagination.custom-report') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- MODAL: View Purchase Items Breakdown --}}
<div class="modal fade" id="purchaseItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header px-4 py-3" style="background: #0f172a; color: #fff;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-invoice text-success" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="modalPurchaseVoucher">ST-PO-0001</h5>
                        <small class="text-white-50" id="modalPurchaseDate">08 Nov, 2026</small>
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
                        <small class="text-muted text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Supplier / Vendor</small>
                        <strong class="text-dark" id="modalPurchaseVendor">-</strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Vendor Invoice #</small>
                        <strong class="text-dark" id="modalPurchaseInvoice">-</strong>
                    </div>
                    <div class="col-sm-4 text-sm-right">
                        <small class="text-muted text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Payment Status</small>
                        <span class="badge badge-success px-2 py-1 font-weight-bold" id="modalPurchaseStatus">Paid</span>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="card border shadow-sm mb-0 overflow-hidden">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark small"><i class="fas fa-boxes text-primary mr-1"></i> Inward Supply Items</span>
                        <span class="badge badge-primary font-weight-bold" id="modalPurchaseItemCount">0 Items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="bg-light text-uppercase" style="font-size: 10px; letter-spacing: 0.5px; color: #64748b;">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Item Details</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modalPurchaseItemsBody">
                                {{-- Filled via JS --}}
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="4" class="text-right">Grand Total:</td>
                                    <td class="text-right text-success" id="modalPurchaseGrandTotal" style="font-size: 14px;">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mt-3 p-2.5 rounded bg-white border" id="modalPurchaseNotesWrap" style="display: none;">
                    <small class="text-muted d-block font-weight-bold" style="font-size: 10.5px;">Notes / Remarks:</small>
                    <small class="text-dark" id="modalPurchaseNotes"></small>
                </div>
            </div>

            <div class="modal-footer bg-white border-top px-4 py-2.5 d-flex justify-content-between">
                <small class="text-muted" id="modalPurchaseCreator"></small>
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
{{-- MODAL: Settle Due Payment --}}
<div class="modal fade" id="settlePaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="" id="settlePaymentForm" class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            @csrf
            <div class="modal-header px-4 py-3" style="background: #059669; color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0 text-white"><i class="fas fa-money-bill-wave mr-2"></i> Settle Due Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background: #f8fafc;">
                <div class="p-3 bg-white rounded border mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Purchase Voucher:</span>
                        <strong class="text-dark" id="settleVoucherNo">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Purchased From:</span>
                        <strong class="text-dark" id="settleVendorName">-</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small font-weight-bold">Due Amount to Pay:</span>
                        <strong class="text-danger" id="settleDueAmount" style="font-size: 15px;">-</strong>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Pay From Account <span class="text-danger">*</span></label>
                    <select name="payment_account_code" class="form-control font-weight-bold" required style="border-radius: 8px;">
                        @foreach($paymentAccounts as $pAcc)
                            <option value="{{ $pAcc->account_code }}" {{ $pAcc->account_code === '1010' ? 'selected' : '' }}>
                                {{ $pAcc->account_code }} — {{ $pAcc->account_name }} ({{ ucfirst($pAcc->account_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Payment Settlement Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">Notes / Reference</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Cleared via Danske Bank transfer" style="border-radius: 8px;">
                </div>
            </div>
            <div class="modal-footer bg-white border-top px-4 py-2.5 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm font-weight-bold px-4"><i class="fas fa-check mr-1"></i> Confirm Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.settle-due-btn').on('click', function() {
        const btn = $(this);
        const action = btn.data('action');
        const voucher = btn.data('voucher');
        const vendor = btn.data('vendor');
        const amount = btn.data('amount');

        $('#settlePaymentForm').attr('action', action);
        $('#settleVoucherNo').text(voucher);
        $('#settleVendorName').text(vendor);
        $('#settleDueAmount').html(amount);

        $('#settlePaymentModal').modal('show');
    });

    $('.view-purchase-items-btn').on('click', function() {
        const btn = $(this);
        const voucher = btn.data('voucher');
        const invoice = btn.data('invoice');
        const date = btn.data('date');
        const vendor = btn.data('vendor');
        const creator = btn.data('creator');
        const status = btn.data('status');
        const notes = btn.data('notes');
        const total = btn.data('total');
        const items = btn.data('items') || [];

        $('#modalPurchaseVoucher').text(voucher);
        $('#modalPurchaseDate').text(date);
        $('#modalPurchaseVendor').text(vendor);
        $('#modalPurchaseInvoice').text(invoice);
        $('#modalPurchaseStatus').text(status);
        $('#modalPurchaseCreator').html('<i class="fas fa-user-circle mr-1"></i> Recorded by: <strong>' + creator + '</strong>');
        $('#modalPurchaseGrandTotal').html(total);
        $('#modalPurchaseItemCount').text(items.length + (items.length === 1 ? ' Item' : ' Items'));

        if (notes && notes.trim() !== '') {
            $('#modalPurchaseNotes').text(notes);
            $('#modalPurchaseNotesWrap').show();
        } else {
            $('#modalPurchaseNotesWrap').hide();
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
                    <td class="text-right text-muted">${item.price}</td>
                    <td class="text-right font-weight-bold text-success">${item.subtotal}</td>
                </tr>
            `;
        });

        $('#modalPurchaseItemsBody').html(html);
        $('#purchaseItemsModal').modal('show');
    });
});
</script>
@endpush
