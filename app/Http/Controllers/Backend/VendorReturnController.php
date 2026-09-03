<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\VendorReturn;
use App\Models\VendorReturnItem;
use App\Models\GoodsReceipt;
use App\DataTables\VendorReturnDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;

class VendorReturnController extends Controller
{
    public function index(VendorReturnDataTable $dataTable)
    {
        return $dataTable->render('backend.vendor_return.index');
    }

    public function create(Request $request)
    {
        $grnId = $request->get('grn_id');
        $grn = GoodsReceipt::with(['purchase.vendor', 'items.product', 'items.variant'])->findOrFail($grnId);

        // Check if VendorReturn / Debit Note already exists for this GRN
        $existingReturn = VendorReturn::where('goods_receipt_id', $grn->id)->first();
        if ($existingReturn) {
            Toastr::warning("Vendor Return and Debit Note #{$existingReturn->debit_note_no} has already been issued for this GRN.", 'Duplicate Action Blocked');
            return redirect()->route('admin.vendor-returns.show', $existingReturn->id);
        }

        // Filter items that have rejected_qty > 0
        $rejectedItems = $grn->items->where('rejected_qty', '>', 0);

        return view('backend.vendor_return.create', compact('grn', 'rejectedItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'reason'           => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason'     => 'nullable|string',
        ]);

        $grn = GoodsReceipt::with('purchase')->findOrFail($request->goods_receipt_id);

        // Enterprise Security Guard: Prevent Duplicate Vendor Returns for the same GRN
        $existingReturn = VendorReturn::where('goods_receipt_id', $grn->id)->first();
        if ($existingReturn) {
            Toastr::warning("Vendor Return and Debit Note #{$existingReturn->debit_note_no} was already issued for this GRN.", 'Duplicate Prevented');
            return redirect()->route('admin.vendor-returns.show', $existingReturn->id);
        }

        $vendorReturn = DB::transaction(function () use ($request, $grn) {
            $seq = VendorReturn::whereDate('created_at', now()->toDateString())->count() + 1;
            $returnNo = 'RET-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $debitNoteNo = 'DN-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $vendorReturn = VendorReturn::create([
                'return_no'        => $returnNo,
                'purchase_id'      => $grn->purchase_id,
                'goods_receipt_id' => $grn->id,
                'debit_note_no'    => $debitNoteNo,
                'reason'           => $request->reason,
                'status'           => 'pending',
                'approval_status'  => 'pending',
                'approved_by'      => null,
            ]);

            $totalClaim = 0;
            foreach ($request->items as $itemData) {
                $qty = (float) $itemData['qty'];
                $price = (float) $itemData['unit_price'];
                $lineTotal = round($qty * $price, 2);
                $totalClaim += $lineTotal;

                VendorReturnItem::create([
                    'vendor_return_id' => $vendorReturn->id,
                    'product_id'       => $itemData['product_id'],
                    'variant_id'       => $itemData['variant_id'] ?? null,
                    'qty'              => $qty,
                    'unit_price'       => $price,
                    'total_amount'     => $lineTotal,
                    'reason'           => $itemData['reason'] ?? 'QC Rejection',
                ]);
            }

            // Submit for multi-step approval
            $approvalService = app(\App\Services\ApprovalService::class);
            $approvalService->submitForApproval($vendorReturn, (float)$totalClaim);

            // If auto-approved by workflow engine (no workflow exists or under threshold)
            if ($vendorReturn->approval_status === 'approved') {
                $vendorReturn->update([
                    'status'      => 'approved',
                    'approved_by' => Auth::id(),
                ]);
            }

            return $vendorReturn;
        });

        return redirect()->route('admin.vendor-returns.show', $vendorReturn->id)
            ->with('success', "Vendor Return #{$vendorReturn->return_no} and Debit Note #{$vendorReturn->debit_note_no} created successfully.");
    }

    public function show($id)
    {
        $vendorReturn = VendorReturn::with([
            'purchase.vendor',
            'goodsReceipt',
            'approvedBy',
            'items.product',
            'items.variant',
            'replacementProduct',
            'replacementVariant',
            'refunds.createdBy',
            'settlements.vendorBill'
        ])->findOrFail($id);

        $allProducts = \App\Models\Product::where('status', 1)->get();

        $approvalService = app(\App\Services\ApprovalService::class);
        $canApprove = $approvalService->canUserApproveCurrentStep($vendorReturn);
        $activeApproval = \App\Models\Approval::with('step.approverRole')
            ->where('approvable_type', get_class($vendorReturn))
            ->where('approvable_id', $vendorReturn->id)
            ->where('status', 'pending')
            ->first();

        return view('backend.vendor_return.show', compact('vendorReturn', 'allProducts', 'canApprove', 'activeApproval'));
    }

    public function approve(Request $request, $id)
    {
        $vendorReturn = VendorReturn::findOrFail($id);
        $approvalService = app(\App\Services\ApprovalService::class);
        $success = $approvalService->approveStep($vendorReturn, Auth::id(), $request->comments);

        if ($success) {
            if ($vendorReturn->approval_status === 'approved') {
                $vendorReturn->update([
                    'status'      => 'approved',
                    'approved_by' => Auth::id(),
                ]);
            }
            Toastr::success('Vendor Return approval step approved successfully!');
        } else {
            Toastr::error('Unauthorized or failed to approve vendor return step.');
        }

        return redirect()->back();
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $vendorReturn = VendorReturn::findOrFail($id);
        $approvalService = app(\App\Services\ApprovalService::class);
        $success = $approvalService->rejectStep($vendorReturn, Auth::id(), $request->reason);

        if ($success) {
            $vendorReturn->update(['status' => 'rejected']);
            Toastr::warning('Vendor Return claim rejected.');
        } else {
            Toastr::error('Unauthorized or failed to reject vendor return step.');
        }

        return redirect()->back();
    }

    /**
     * Settle Debit Note via Product Replacement / Swap (Same SKU or Substitute Item)
     */
    public function settleReplacement(\App\Http\Requests\Backend\VendorReturn\StoreReplacementReceiveRequest $request, \App\Services\VendorReturnService $returnService)
    {
        $vendorReturn = $returnService->settleViaProductReplacement($request->validated());

        return redirect()->route('admin.vendor-returns.show', $vendorReturn->id)
            ->with('success', "Debit Note #{$vendorReturn->debit_note_no} successfully settled via Product Replacement stock receipt.");
    }

    /**
     * Settle Debit Note via Direct Money Refund (Cash/Bank Transfer Deposit)
     */
    public function settleRefund(\App\Http\Requests\Backend\VendorReturn\StoreVendorRefundRequest $request, \App\Services\VendorReturnService $returnService)
    {
        $refund = $returnService->settleViaCashRefund($request->validated());

        return redirect()->route('admin.vendor-returns.show', $refund->vendor_return_id)
            ->with('success', "Debit Note successfully settled via Direct Money Refund Voucher #{$refund->refund_no}.");
    }
}
