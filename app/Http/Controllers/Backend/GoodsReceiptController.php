<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Purchase;
use App\Models\Outlet;
use App\DataTables\GrnDataTable;
use App\Services\StockReceiveService;
use App\Services\GrnPdfService;
use App\Jobs\GenerateGrnPdfJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;

class GoodsReceiptController extends Controller
{
    protected StockReceiveService $stockReceiveService;
    protected GrnPdfService $pdfService;

    public function __construct(StockReceiveService $stockReceiveService, GrnPdfService $pdfService)
    {
        $this->stockReceiveService = $stockReceiveService;
        $this->pdfService = $pdfService;
    }

    public function index(GrnDataTable $dataTable)
    {
        return $dataTable->render('backend.grn.index');
    }

    public function create(Request $request)
    {
        $purchaseId = $request->get('purchase_id');
        $purchase = null;
        $remainingQtyMap = [];

        if ($purchaseId) {
            $purchase = Purchase::with(['items.product', 'items.variant', 'vendor', 'goodsReceipts.items'])->findOrFail($purchaseId);
            
            // Calculate remaining qty for each line item (ordered qty - sum of accepted_qty in previous GRNs)
            // Rejected items are returned to vendor, so remaining qty allows vendor redelivery / replacement GRNs
            foreach ($purchase->items as $item) {
                $previouslyReceived = DB::table('goods_receipt_items')
                    ->join('goods_receipts', 'goods_receipt_items.goods_receipt_id', '=', 'goods_receipts.id')
                    ->where('goods_receipts.purchase_id', $purchase->id)
                    ->where('goods_receipts.qc_status', '!=', 'failed')
                    ->where('goods_receipt_items.product_id', $item->product_id)
                    ->when($item->variant_id, fn($q) => $q->where('goods_receipt_items.variant_id', $item->variant_id))
                    ->sum('goods_receipt_items.accepted_qty');

                $remaining = max(0, (float) $item->qty - (float) $previouslyReceived);
                $remainingQtyMap[$item->id] = $remaining;
            }
        }

        // Enterprise Rule: Include Approved POs that are either Local OR Foreign POs having at least one Customs Cleared Shipment
        $purchases = Purchase::whereIn('approval_status', ['approved'])
            ->where('milestone_status', '!=', 'goods_received')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(purchase_type, 'local')) = 'local'")
                      ->orWhereHas('shipments', function ($sq) {
                          $sq->where('status', 'cleared');
                      });
            })
            ->orderBy('id', 'desc')
            ->get();

        $outlets = Outlet::all();

        return view('backend.grn.create', compact('purchases', 'purchase', 'outlets', 'remainingQtyMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'outlet_id'   => 'required|exists:outlets,id',
            'remarks'     => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.variant_id'   => 'nullable|exists:product_variants,id',
            'items.*.accepted_qty' => 'required|numeric|min:0',
            'items.*.rejected_qty' => 'required|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
        ]);

        $purchase = Purchase::with(['items', 'shipments'])->findOrFail($request->purchase_id);

        // Enterprise Security Guard: Validate Accepted Qty against Remaining Qty to prevent stock distortion
        foreach ($request->items as $itemData) {
            $poItem = $purchase->items->where('product_id', $itemData['product_id'])
                ->when(!empty($itemData['variant_id']), fn($q) => $q->where('variant_id', $itemData['variant_id']))
                ->first();

            if ($poItem) {
                $previouslyReceived = DB::table('goods_receipt_items')
                    ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                    ->where('goods_receipts.purchase_id', $purchase->id)
                    ->where('goods_receipts.qc_status', '!=', 'failed')
                    ->where('goods_receipt_items.product_id', $itemData['product_id'])
                    ->when(!empty($itemData['variant_id']), fn($q) => $q->where('goods_receipt_items.variant_id', $itemData['variant_id']))
                    ->sum('goods_receipt_items.accepted_qty');

                $remaining = max(0, (float) $poItem->qty - (float) $previouslyReceived);
                if ((float) $itemData['accepted_qty'] > ($remaining + 0.0001)) {
                    Toastr::error("Accepted quantity ({$itemData['accepted_qty']}) cannot exceed remaining ordered quantity ({$remaining})!", 'Over-Receipt Guard');
                    return redirect()->back()->withInput();
                }
            }
        }

        // Enterprise Security Guard: Foreign Purchases MUST have a Customs Cleared shipment before receiving goods
        if (strtolower($purchase->purchase_type ?? 'local') === 'foreign') {
            $hasClearedShipment = $purchase->shipments()->where('status', 'cleared')->exists();
            if (!$hasClearedShipment) {
                Toastr::error('Enterprise Rule Violation: Foreign Purchase goods cannot be received until shipment status is Customs Cleared!', 'Logistics Blocked');
                return redirect()->back();
            }
        }

        $grn = DB::transaction(function () use ($request, $purchase) {
            // Generate GRN No: GRN-YYYYMMDD-XXXX (Atomic locking for enterprise race condition prevention)
            $seq = GoodsReceipt::whereDate('created_at', now()->toDateString())->lockForUpdate()->count() + 1;
            $grnNo = 'GRN-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Determine overall QC Status based on items
            $totalAccepted = 0;
            $totalRejected = 0;

            foreach ($request->items as $itemData) {
                $totalAccepted += (float) $itemData['accepted_qty'];
                $totalRejected += (float) $itemData['rejected_qty'];
            }

            $qcStatus = 'passed';
            if ($totalAccepted > 0 && $totalRejected > 0) {
                $qcStatus = 'partial';
            } elseif ($totalAccepted == 0 && $totalRejected > 0) {
                $qcStatus = 'failed';
            }

            $grn = GoodsReceipt::create([
                'grn_no'      => $grnNo,
                'purchase_id' => $purchase->id,
                'outlet_id'   => $request->outlet_id,
                'received_by' => Auth::id(),
                'qc_status'   => $qcStatus,
                'remarks'     => $request->remarks,
            ]);

            foreach ($request->items as $itemData) {
                if ((float)$itemData['accepted_qty'] > 0 || (float)$itemData['rejected_qty'] > 0) {
                    GoodsReceiptItem::create([
                        'goods_receipt_id' => $grn->id,
                        'product_id'       => $itemData['product_id'],
                        'variant_id'       => $itemData['variant_id'] ?? null,
                        'accepted_qty'     => $itemData['accepted_qty'],
                        'rejected_qty'     => $itemData['rejected_qty'],
                        'rejection_reason' => $itemData['rejection_reason'] ?? null,
                    ]);
                }
            }

            // Execute Stock Receive Service if QC passed or partial
            if (in_array($qcStatus, ['passed', 'partial'])) {
                $this->stockReceiveService->processStockReceive($grn);
            }

            return $grn;
        });

        // Dispatch background job to render & cache PDF
        GenerateGrnPdfJob::dispatch($grn->id);

        return redirect()->route('admin.goods-receipts.show', $grn->id)
            ->with('success', "GRN #{$grn->grn_no} created successfully. Stock ledgers updated.");
    }

    public function show($id)
    {
        $grn = GoodsReceipt::with([
            'purchase.vendor',
            'purchase.items',
            'outlet',
            'receivedBy',
            'items.product',
            'items.variant',
            'vendorReturn'
        ])->findOrFail($id);

        return view('backend.grn.show', compact('grn'));
    }

    public function streamPdf($id)
    {
        $grn = GoodsReceipt::findOrFail($id);
        $pdfContent = $this->pdfService->getOrGeneratePdf($grn);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="GRN-' . $grn->grn_no . '.pdf"',
        ]);
    }
}
