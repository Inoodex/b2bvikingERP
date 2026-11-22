<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\VendorBillDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\VendorBill\StoreVendorBillRequest;
use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Models\VendorBill;
use App\Models\VendorReturn;
use App\Services\VendorBillService;
use Illuminate\Http\Request;

class VendorBillController extends Controller
{
    protected VendorBillService $billService;

    public function __construct(VendorBillService $billService)
    {
        $this->billService = $billService;
    }

    /**
     * Display a listing of Vendor Bills.
     */
    public function index(VendorBillDataTable $dataTable)
    {
        // 4 Live Executive AP KPI Metrics (Normalized to Base DKK)
        $bills = VendorBill::with(['purchase', 'vendor.currency', 'currency'])->where('due_amount', '>', 0)->get();

        $totalApPayable = 0;
        $dueInNext7Days = 0;
        $criticalOverdueAp = 0;
        $today = now()->toDateString();
        $next7Days = now()->addDays(7)->toDateString();

        foreach ($bills as $bill) {
            $rate = (float)($bill->purchase?->exchange_rate_used ?: ($bill->currency?->exchange_rate ?: ($bill->vendor?->effective_exchange_rate ?: 1.0)));
            $baseDue = round((float)$bill->due_amount * $rate, 2);
            $totalApPayable += $baseDue;

            if ($bill->due_date) {
                $dueDate = $bill->due_date->toDateString();
                if ($dueDate >= $today && $dueDate <= $next7Days) {
                    $dueInNext7Days += $baseDue;
                }
                if ($dueDate < $today) {
                    $criticalOverdueAp += $baseDue;
                }
            }
        }

        if ($totalApPayable <= 0) {
            $purchases = Purchase::with(['vendor.currency', 'currency'])->where('due_amount', '>', 0)->get();
            foreach ($purchases as $p) {
                $rate = (float)($p->exchange_rate_used ?: ($p->currency?->exchange_rate ?: 1.0));
                $totalApPayable += round((float)$p->due_amount * $rate, 2);
            }
        }

        $totalBillsCount = VendorBill::count();

        return $dataTable->render('backend.vendor_bill.index', compact(
            'totalApPayable',
            'dueInNext7Days',
            'criticalOverdueAp',
            'totalBillsCount'
        ));
    }

    /**
     * Show the form for creating a new Vendor Bill from a GRN or PO.
     */
    public function create(Request $request)
    {
        $grnId = $request->query('grn_id');
        $poId = $request->query('purchase_id');

        $goodsReceipt = null;
        $purchase = null;
        $pendingReturns = collect();
        $debitNoteAmount = 0;

        $purchases = Purchase::with(['vendor', 'currency'])
            ->has('items')
            ->latest()
            ->get();

        $goodsReceipts = GoodsReceipt::with(['purchase.vendor'])
            ->has('items')
            ->latest()
            ->get();

        if ($grnId) {
            $goodsReceipt = GoodsReceipt::with(['purchase.vendor', 'items.product', 'items.variant'])->find($grnId);
            if ($goodsReceipt) {
                $purchase = $goodsReceipt->purchase;
                if ($purchase) {
                    $purchase->loadMissing(['items.product', 'items.variant', 'vendor', 'currency']);
                }
            }
        } else if ($poId) {
            $purchase = Purchase::with(['vendor', 'currency', 'items.product', 'items.variant'])->find($poId);
        }

        if ($purchase) {
            // Check for pending Debit Notes on this PO / Vendor Return
            $pendingReturns = VendorReturn::where('purchase_id', $purchase->id)
                ->where('status', 'approved')
                ->get();

            $debitNoteAmount = $pendingReturns->sum('total_claim_amount');
        }

        return view('backend.vendor_bill.create', compact('goodsReceipt', 'purchase', 'purchases', 'goodsReceipts', 'pendingReturns', 'debitNoteAmount'));
    }

    /**
     * Store a newly created Vendor Bill in storage.
     */
    public function store(StoreVendorBillRequest $request)
    {
        try {
            $bill = $this->billService->createBill($request->validated());

            // Submit for multi-step approval
            $approvalService = app(\App\Services\ApprovalService::class);
            $approvalService->submitForApproval($bill, (float)$bill->grand_total);

            if ($bill->approval_status === 'pending') {
                toastr()->info("Vendor Bill {$bill->bill_no} submitted and waiting for managerial approval before payment voucher release.", 'Pending Approval');
            } else {
                toastr()->success('Vendor Bill ' . $bill->bill_no . ' created and approved successfully!');
            }

            return redirect()->route('admin.vendor-bills.show', $bill->id);
        } catch (\Exception $e) {
            toastr()->error('Failed to create Vendor Bill: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified Vendor Bill.
     */
    public function show(int $id)
    {
        $bill = VendorBill::with([
            'purchase',
            'vendor',
            'goodsReceipt',
            'currency',
            'createdBy',
            'items.product',
            'items.variant',
            'debitNoteSettlements.vendorReturn'
        ])->findOrFail($id);

        $approvalService = app(\App\Services\ApprovalService::class);
        $canApprove = $approvalService->canUserApproveCurrentStep($bill);
        $activeApproval = \App\Models\Approval::with('step.approverRole')
            ->where('approvable_type', get_class($bill))
            ->where('approvable_id', $bill->id)
            ->where('status', 'pending')
            ->first();

        return view('backend.vendor_bill.show', compact('bill', 'canApprove', 'activeApproval'));
    }

    public function approve(Request $request, int $id)
    {
        $bill = VendorBill::findOrFail($id);
        $approvalService = app(\App\Services\ApprovalService::class);
        $success = $approvalService->approveStep($bill, auth()->id(), $request->comments);

        if ($success) {
            toastr()->success('Vendor Bill approval step approved successfully!');
        } else {
            toastr()->error('Unauthorized or failed to approve Vendor Bill.');
        }

        return redirect()->back();
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $bill = VendorBill::findOrFail($id);
        $approvalService = app(\App\Services\ApprovalService::class);
        $success = $approvalService->rejectStep($bill, auth()->id(), $request->reason);

        if ($success) {
            toastr()->warning('Vendor Bill rejected.');
        } else {
            toastr()->error('Unauthorized or failed to reject Vendor Bill.');
        }

        return redirect()->back();
    }
}
