<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockTransferDataTable;
use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Http\Requests\Backend\StockTransfer\StoreRequest;
use App\Http\Requests\Backend\StockTransfer\ReceiveRequest;
use App\Models\User;
use App\Services\Inventory\StockTransferService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    protected StockTransferService $transferService;

    public function __construct(StockTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(StockTransferDataTable $dataTable)
    {
        return $dataTable->render('backend.stock_transfers.index');
    }

    public function create(Request $request)
    {
        $outlets = Outlet::all();
        if ($outlets->isEmpty()) {
            $outlets = User::role(['Outlet User', 'User'])->get();
        }

        $products = Product::where('status', 1)
            ->with(['variants.color', 'variants.size', 'unit'])
            ->orderBy('name')
            ->get();

        $cartItems = collect();
        if ($request->query('source') === 'cart') {
            $fromOutletId = $outlets->first()->id ?? 1;
            $cartItems = \App\Models\Cart::where('user_id', auth()->id())
                ->where('cart_type', 'request')
                ->with(['product.variants.color', 'product.variants.size', 'product.unit', 'variant.color', 'variant.size'])
                ->get();

            foreach ($cartItems as $ci) {
                $stock = InventoryStock::where('outlet_id', $fromOutletId)
                    ->where('product_id', $ci->product_id)
                    ->where('variant_id', $ci->variant_id)
                    ->value('quantity') ?? 0;
                $ci->source_stock = (float) $stock;
            }
        }

        return view('backend.stock_transfers.create', compact('outlets', 'products', 'cartItems'));
    }

    public function store(StoreRequest $request)
    {
        try {
            $transfer = $this->transferService->createTransfer($request->validated(), $request->items);

            // Auto-clear Stock Transfer basket for current user
            \App\Models\Cart::where('user_id', auth()->id())
                ->where('cart_type', 'request')
                ->delete();

            Toastr::success('Stock Transfer created successfully in Draft state.');
            return redirect()->route('admin.stock-transfers.show', $transfer->id);
        } catch (\Exception $e) {
            Toastr::error('Failed to create Stock Transfer: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'items.product.unit',
            'items.variant.color',
            'items.variant.size',
            'fromOutlet',
            'toOutlet',
            'requestedByUser',
            'dispatchedByUser',
            'receivedByUser'
        ]);

        $hasInsufficientStock = false;
        foreach ($stockTransfer->items as $item) {
            $currentStock = \App\Models\InventoryStock::where('outlet_id', $stockTransfer->from_outlet_id)
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->value('quantity') ?? 0;
            $item->source_current_stock = (float)$currentStock;
            if ($stockTransfer->status === 'draft' && (float)$currentStock < (float)$item->qty) {
                $hasInsufficientStock = true;
            }
        }

        return view('backend.stock_transfers.show', compact('stockTransfer', 'hasInsufficientStock'));
    }

    public function dispatchTransfer(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->dispatchTransfer($stockTransfer);
            Toastr::success('Stock Transfer dispatched! Stock has been deducted from source warehouse and is now in transit.');
        } catch (\Exception $e) {
            Toastr::error('Dispatch Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
    }

    public function receiveForm(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'dispatched') {
            Toastr::warning('Only dispatched transfers in transit can be received.');
            return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
        }

        $stockTransfer->load(['items.product.unit', 'items.variant.color', 'items.variant.size', 'fromOutlet', 'toOutlet']);
        return view('backend.stock_transfers.receive', compact('stockTransfer'));
    }

    public function receiveTransfer(ReceiveRequest $request, StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->receiveTransfer($stockTransfer, $request->validated('received_items'));
            Toastr::success('Stock Transfer received and verified! Stock has been added to destination warehouse.');
        } catch (\Exception $e) {
            Toastr::error('Receive Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
    }

    public function cancel(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->cancelTransfer($stockTransfer);
            Toastr::info('Stock Transfer has been cancelled.');
        } catch (\Exception $e) {
            Toastr::error('Cancellation Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
    }

    public function downloadPdf(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'items.product.unit',
            'items.variant.color',
            'items.variant.size',
            'fromOutlet',
            'toOutlet',
            'requestedByUser',
            'dispatchedByUser'
        ]);

        $settings = \App\Models\GeneralSetting::first();

        $pdf = Pdf::loadView('backend.stock_transfers.pdf', compact('stockTransfer', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Transfer_Challan_' . $stockTransfer->transfer_no . '.pdf');
    }

    public function removeItem(StockTransfer $stockTransfer, StockTransferItem $item)
    {
        if ($stockTransfer->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft transfers can be modified.'], 422);
        }

        if ($item->stock_transfer_id !== $stockTransfer->id) {
            return response()->json(['success' => false, 'message' => 'Item does not belong to this transfer.'], 404);
        }

        $item->delete();
        $remainingCount = $stockTransfer->items()->count();
        $stockTransfer->update(['total_items_count' => $remainingCount]);

        // Check if any deficient items remain
        $hasInsufficientStock = false;
        foreach ($stockTransfer->items as $it) {
            $stock = InventoryStock::where('outlet_id', $stockTransfer->from_outlet_id)
                ->where('product_id', $it->product_id)
                ->where('variant_id', $it->variant_id)
                ->value('quantity') ?? 0;
            if ((float)$stock < (float)$it->qty) {
                $hasInsufficientStock = true;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from transfer successfully.',
            'remaining_count' => $remainingCount,
            'has_insufficient_stock' => $hasInsufficientStock
        ]);
    }

    public function updateItemQty(Request $request, StockTransfer $stockTransfer, StockTransferItem $item)
    {
        if ($stockTransfer->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft transfers can be modified.'], 422);
        }

        if ($item->stock_transfer_id !== $stockTransfer->id) {
            return response()->json(['success' => false, 'message' => 'Item does not belong to this transfer.'], 404);
        }

        $validated = $request->validate([
            'qty' => 'required|numeric|min:0.01'
        ]);

        $item->update(['qty' => $validated['qty']]);

        // Check stock availability
        $sourceStock = InventoryStock::where('outlet_id', $stockTransfer->from_outlet_id)
            ->where('product_id', $item->product_id)
            ->where('variant_id', $item->variant_id)
            ->value('quantity') ?? 0;

        // Check if any deficient items remain in whole transfer
        $hasInsufficientStock = false;
        foreach ($stockTransfer->items as $it) {
            $stock = InventoryStock::where('outlet_id', $stockTransfer->from_outlet_id)
                ->where('product_id', $it->product_id)
                ->where('variant_id', $it->variant_id)
                ->value('quantity') ?? 0;
            if ((float)$stock < (float)$it->qty) {
                $hasInsufficientStock = true;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Transfer quantity updated successfully.',
            'qty' => (float)$item->qty,
            'source_stock' => (float)$sourceStock,
            'is_item_sufficient' => ((float)$sourceStock >= (float)$item->qty),
            'has_insufficient_stock' => $hasInsufficientStock
        ]);
    }

    public function getProductStock(Request $request)
    {
        $productId = $request->get('product_id');
        $outletId = $request->get('outlet_id') ?: 1;

        if (!$productId) {
            return response()->json(['success' => false, 'message' => 'Product ID is required']);
        }

        $product = Product::with(['variants.color', 'variants.size', 'unit', 'category'])->find($productId);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $isVariable = $product->variants->isNotEmpty();
        $variantsData = [];
        $totalStock = 0;

        if ($isVariable) {
            foreach ($product->variants as $variant) {
                $stock = InventoryStock::where([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'outlet_id' => $outletId,
                ])->value('quantity') ?? 0;

                $stockVal = (float)$stock;
                $totalStock += $stockVal;

                $vName = trim($variant->name ?? '');
                if (empty($vName)) {
                    $cName = $variant->color->name ?? $variant->color ?? '';
                    $sName = $variant->size->name ?? $variant->size ?? '';
                    $vName = trim($cName . ' ' . $sName);
                }
                if (empty($vName)) {
                    $vName = 'Variant #' . $variant->id;
                }

                $variantsData[] = [
                    'id' => $variant->id,
                    'name' => $vName,
                    'sku' => $variant->sku ?? '',
                    'stock' => $stockVal,
                    'unit_cost' => (float)($product->purchase_price ?? $product->price ?? 0),
                ];
            }
        } else {
            $stock = InventoryStock::where([
                'product_id' => $product->id,
                'variant_id' => null,
                'outlet_id' => $outletId,
            ])->value('quantity') ?? 0;
            $totalStock = (float)$stock;
        }

        $thumbImage = null;
        if (!empty($product->thumb_image)) {
            if (str_starts_with($product->thumb_image, 'http')) {
                $thumbImage = $product->thumb_image;
            } elseif (str_starts_with($product->thumb_image, 'uploads/')) {
                $thumbImage = asset($product->thumb_image);
            } else {
                $thumbImage = asset('storage/' . $product->thumb_image);
            }
        }

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'name' => $product->name,
            'category_name' => $product->category->name ?? 'General',
            'thumb_image' => $thumbImage,
            'unit' => $product->unit->name ?? 'pcs',
            'is_variable' => $isVariable,
            'total_stock' => $totalStock,
            'simple_stock' => $isVariable ? 0 : $totalStock,
            'unit_cost' => (float)($product->purchase_price ?? $product->price ?? 0),
            'variants' => $variantsData
        ]);
    }
}
