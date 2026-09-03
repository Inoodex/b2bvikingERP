@extends('backend.layouts.master')
@section('title', 'Pending Approvals Inbox — Enterprise Governance Hub')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-dark font-weight-bold mb-1"><i class="fas fa-check-double text-warning mr-2"></i> Approvals Inbox</h1>
            <p class="text-muted mb-0 small">Centralized Management Sign-off Hub across Procurement, Inventory, Sales, and Finance</p>
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.master.approval-workflows.index') }}">Approval Workflows</a></div>
            <div class="breadcrumb-item active">Approvals Inbox</div>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $category === 'all' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.approvals.index', ['category' => 'all']) }}">
                        <i class="fas fa-inbox mr-1"></i> All Pending 
                        <span class="badge badge-light ml-1">{{ $counts['all'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $category === 'procurement' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.approvals.index', ['category' => 'procurement']) }}">
                        <i class="fas fa-shopping-cart mr-1"></i> Procurement (PR/PO/CS/LC)
                        <span class="badge badge-light ml-1">{{ $counts['procurement'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $category === 'inventory' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.approvals.index', ['category' => 'inventory']) }}">
                        <i class="fas fa-boxes mr-1"></i> Inventory (Transfers)
                        <span class="badge badge-light ml-1">{{ $counts['inventory'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $category === 'sales' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.approvals.index', ['category' => 'sales']) }}">
                        <i class="fas fa-chart-line mr-1"></i> Sales (Orders)
                        <span class="badge badge-light ml-1">{{ $counts['sales'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $category === 'accounts' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.approvals.index', ['category' => 'accounts']) }}">
                        <i class="fas fa-coins mr-1"></i> Finance & Accounts (Bills/Transfers)
                        <span class="badge badge-light ml-1">{{ $counts['accounts'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Approvals Table Card -->
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-tasks text-primary mr-2"></i> Documents Awaiting Your Authorization</h6>
            <span class="badge badge-warning font-weight-bold px-3 py-2" style="border-radius: 20px;">
                {{ $approvals->total() }} Action Required
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Document Type</th>
                            <th>Reference / Number</th>
                            <th>Target / Details</th>
                            <th>Total Value</th>
                            <th>Active Approval Step</th>
                            <th>Submitted Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvals as $approval)
                            @php
                                $doc = $approval->approvable;
                                $docType = class_basename($approval->approvable_type);
                                $docNo = '-';
                                $docDetail = '-';
                                $docAmount = 0.00;
                                $viewRoute = '#';

                                if ($doc) {
                                    if ($docType === 'Purchase') {
                                        $docNo = $doc->po_no ?? 'PO #'.$doc->id;
                                        $docDetail = $doc->vendor?->shop_name ?? 'Vendor';
                                        $docAmount = (float)($doc->total_amount ?? 0);
                                        $viewRoute = route('admin.purchase-orders.show', $doc->id);
                                    } elseif ($docType === 'ProductRequest') {
                                        $docNo = $doc->request_no ?? 'PR #'.$doc->id;
                                        $docDetail = $doc->department?->name ?? ($doc->user?->name ?? 'Requisition');
                                        $docAmount = (float)($doc->total_amount ?? 0);
                                        $viewRoute = route('admin.product-requests.show', $doc->id);
                                    } elseif ($docType === 'ComparisonStatement') {
                                        $docNo = 'CS #'.$doc->id;
                                        $docDetail = $doc->rfq?->rfq_no ?? 'RFQ';
                                        $docAmount = 0;
                                        $viewRoute = route('admin.rfqs.show', $doc->rfq_id);
                                    } elseif ($docType === 'StockTransfer') {
                                        $docNo = $doc->transfer_no ?? 'TR #'.$doc->id;
                                        $docDetail = ($doc->fromOutlet?->name ?? 'Source') . ' ➔ ' . ($doc->toOutlet?->name ?? 'Dest');
                                        $docAmount = $doc->items ? (float)$doc->items->sum(fn($i) => (float)$i->qty * (float)$i->unit_cost) : 0;
                                        $viewRoute = route('admin.stock-transfers.show', $doc->id);
                                    } elseif ($docType === 'LetterOfCredit') {
                                        $docNo = $doc->lc_no ?? 'LC #'.$doc->id;
                                        $docDetail = $doc->issuing_bank ?? 'Bank LC';
                                        $docAmount = (float)($doc->amount ?? 0);
                                        $viewRoute = route('admin.letters-of-credit.show', $doc->id);
                                    } elseif ($docType === 'VendorReturn') {
                                        $docNo = $doc->return_no ?? 'RET #'.$doc->id;
                                        $docDetail = 'Debit Note: ' . ($doc->debit_note_no ?? '-');
                                        $docAmount = (float)($doc->total_claim_amount ?? 0);
                                        $viewRoute = route('admin.vendor-returns.show', $doc->id);
                                    } elseif ($docType === 'Order') {
                                        $docNo = $doc->order_no ?? 'SO #'.$doc->id;
                                        $docDetail = $doc->user?->name ?? 'Customer';
                                        $docAmount = (float)($doc->total_amount ?? 0);
                                        $viewRoute = route('admin.sales-orders.show', $doc->id);
                                    } elseif ($docType === 'VendorBill') {
                                        $docNo = $doc->bill_no ?? 'BILL #'.$doc->id;
                                        $docDetail = $doc->vendor?->shop_name ?? 'Supplier';
                                        $docAmount = (float)($doc->grand_total ?? 0);
                                        $viewRoute = route('admin.vendor-bills.show', $doc->id);
                                    } elseif ($docType === 'FundTransfer') {
                                        $docNo = 'TRANSFER #' . $doc->id;
                                        $docDetail = ($doc->fromAccount?->account_name ?? 'Bank 1') . ' ➔ ' . ($doc->toAccount?->account_name ?? 'Bank 2');
                                        $docAmount = (float)($doc->amount ?? 0);
                                        $viewRoute = route('admin.fund-transfers.index');
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge badge-info px-2 py-1 font-weight-bold">
                                        {{ $docType }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $docNo }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted d-block">{{ $docDetail }}</small>
                                </td>
                                <td>
                                    <strong class="text-dark">kr. {{ number_format($docAmount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-warning px-2 py-1">
                                        <i class="fas fa-user-clock mr-1"></i> {{ $approval->step?->step_name ?: 'Step ' . ($approval->step?->step_order ?? 1) }}
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        Role: {{ $approval->step?->approverRole?->name ?? 'Any' }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $approval->created_at ? $approval->created_at->format('d M Y, h:i A') : '-' }}</small>
                                </td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        @if($viewRoute !== '#')
                                            <a href="{{ $viewRoute }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View Source Document">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 btn-approve-modal" data-id="{{ $approval->id }}" data-no="{{ $docNo }}">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger font-weight-bold px-3 btn-reject-modal" data-id="{{ $approval->id }}" data-no="{{ $docNo }}">
                                            <i class="fas fa-times mr-1"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-success">
                                    <i class="fas fa-check-circle fa-3x mb-3 d-block"></i>
                                    <h5 class="font-weight-bold">Your Approvals Inbox is completely clear!</h5>
                                    <p class="text-muted small mb-0">No documents are currently awaiting your managerial sign-off.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($approvals->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $approvals->withQueryString()->links() }}
            </div>
        @endif
    </div>
</section>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" id="approveForm" action="">
            @csrf
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-check-circle mr-2"></i> Approve Document</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Are you sure you want to approve document <strong id="approveDocNo"></strong>? This will advance the workflow to the next step or fully release the document for execution.</p>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-muted">Approval Comments (Optional):</label>
                        <textarea name="comments" class="form-control" rows="2" placeholder="e.g. Approved and cleared for next stage..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4"><i class="fas fa-check mr-1"></i> Confirm Approval</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" id="rejectForm" action="">
            @csrf
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-times-circle mr-2"></i> Reject Document</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Are you sure you want to reject document <strong id="rejectDocNo"></strong>? The workflow will stop and the document will be marked as rejected.</p>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-danger">Reason for Rejection (Mandatory) *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="State exact reason for rejection (e.g. Budget exceeded, missing attachments)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold px-4"><i class="fas fa-times mr-1"></i> Reject Document</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.btn-approve-modal').on('click', function() {
        var id = $(this).data('id');
        var docNo = $(this).data('no');
        $('#approveDocNo').text(docNo);
        $('#approveForm').attr('action', '{{ url("admin/approvals") }}/' + id + '/approve');
        $('#approveModal').modal('show');
    });

    $('.btn-reject-modal').on('click', function() {
        var id = $(this).data('id');
        var docNo = $(this).data('no');
        $('#rejectDocNo').text(docNo);
        $('#rejectForm').attr('action', '{{ url("admin/approvals") }}/' + id + '/reject');
        $('#rejectModal').modal('show');
    });
});
</script>
@endpush
