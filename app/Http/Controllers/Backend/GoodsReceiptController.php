<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\GrnDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\StoreGoodsReceiptRequest;
use App\Jobs\GenerateGrnPdfJob;
use App\Models\GoodsReceipt;
use App\Models\Outlet;
use App\Models\Purchase;
use App\Services\GrnPdfService;
use App\Services\StockReceiveService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function create(Request $request): View
    {
        $purchaseId = $request->get('purchase_id');
        $purchase = null;
        $remainingQtyMap = [];

        if ($purchaseId) {
            $purchase = Purchase::with(['items.product', 'items.variant', 'vendor', 'goodsReceipts.items'])->findOrFail($purchaseId);

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

        $purchases = Purchase::whereIn('approval_status', ['approved'])
            ->where('milestone_status', '!=', 'goods_received')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(purchase_type, 'local')) = 'local'")
                      ->orWhereHas('shipments', fn($sq) => $sq->where('status', 'cleared'));
            })
            ->orderBy('id', 'desc')
            ->get();

        $outlets = Outlet::all();

        return view('backend.grn.create', compact('purchases', 'purchase', 'outlets', 'remainingQtyMap'));
    }

    public function store(StoreGoodsReceiptRequest $request): RedirectResponse
    {
        $grn = $this->stockReceiveService->createGoodsReceipt($request->validated(), Auth::id() ?? 1);

        GenerateGrnPdfJob::dispatch($grn->id);

        Toastr::success("GRN #{$grn->grn_no} created successfully. Stock ledgers updated.", 'Success');
        return redirect()->route('admin.goods-receipts.show', $grn->id);
    }

    public function show($id): View
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
