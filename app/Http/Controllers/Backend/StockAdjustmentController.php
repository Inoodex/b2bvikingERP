<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockAdjustmentDataTable;
use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Services\Inventory\StockAdjustmentService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    protected StockAdjustmentService $adjustmentService;

    public function __construct(StockAdjustmentService $adjustmentService)
    {
        $this->adjustmentService = $adjustmentService;
    }

    public function index(StockAdjustmentDataTable $dataTable)
    {
        return $dataTable->render('backend.stock_adjustments.index');
    }

    public function create()
    {
        $outlets = Outlet::all();
        if ($outlets->isEmpty()) {
            $outlets = User::role(['Outlet User', 'User'])->get();
        }

        $products = Product::where('status', 1)
            ->with(['variants.color', 'variants.size', 'unit'])
            ->orderBy('name')
            ->get();

        return view('backend.stock_adjustments.create', compact('outlets', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'required',
            'adjustment_type' => 'required|in:increase,decrease,reconciliation',
            'reason_code' => 'required|in:damage,physical_count,expired,sample_marketing,theft_loss,internal_use,other',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable',
            'items.*.adjusted_qty' => 'required|numeric|gt:0',
            'items.*.counted_qty' => 'nullable|numeric',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.item_note' => 'nullable|string|max:255',
        ]);

        try {
            $adjustment = $this->adjustmentService->createAdjustment($validated, $request->items);
            Toastr::success('Stock Adjustment created successfully in Draft state.');
            return redirect()->route('admin.stock-adjustments.show', $adjustment->id);
        } catch (\Exception $e) {
            Toastr::error('Failed to create Stock Adjustment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['items.product.unit', 'items.variant.color', 'items.variant.size', 'outlet', 'requestedByUser', 'approvedByUser']);
        return view('backend.stock_adjustments.show', compact('stockAdjustment'));
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        try {
            $this->adjustmentService->approveAdjustment($stockAdjustment);
            Toastr::success('Stock Adjustment approved and inventory updated successfully!');
        } catch (\Exception $e) {
            Toastr::error('Approval Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-adjustments.show', $stockAdjustment->id);
    }

    public function cancel(StockAdjustment $stockAdjustment)
    {
        try {
            $this->adjustmentService->cancelAdjustment($stockAdjustment);
            Toastr::info('Stock Adjustment has been cancelled.');
        } catch (\Exception $e) {
            Toastr::error('Cancellation Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-adjustments.show', $stockAdjustment->id);
    }

    public function getItemStock(Request $request)
    {
        $productId = $request->get('product_id');
        $variantId = $request->get('variant_id') ?: null;
        $outletId = $request->get('outlet_id') ?: 1;

        if (!$productId) {
            return response()->json(['success' => false, 'message' => 'Product ID is required']);
        }

        $stock = InventoryStock::where([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'outlet_id' => $outletId,
        ])->first();

        $product = Product::find($productId);
        $systemQty = $stock ? (float) $stock->quantity : 0.00;
        $unitCost = $product ? (float) ($product->purchase_price ?? $product->price ?? 0.00) : 0.00;

        return response()->json([
            'success' => true,
            'system_qty' => $systemQty,
            'unit_cost' => $unitCost,
        ]);
    }
}
