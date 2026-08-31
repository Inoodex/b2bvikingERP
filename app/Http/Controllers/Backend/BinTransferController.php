<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\WarehouseBin;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class BinTransferController extends Controller
{
    public function create()
    {
        $outlets = Outlet::all();
        $bins = WarehouseBin::with('zone.outlet')->where('status', 1)->get();

        return view('backend.bin_transfers.create', compact('outlets', 'bins'));
    }

    public function getBinProducts(Request $request)
    {
        $binId = $request->get('bin_id');
        $outletId = $request->get('outlet_id');

        $stocksQuery = InventoryStock::with(['product', 'variant'])
            ->where('quantity', '>', 0);

        if ($outletId) {
            $stocksQuery->where('outlet_id', $outletId);
        }

        if (!empty($binId)) {
            $stocksQuery->where('bin_id', $binId);
        } else {
            $stocksQuery->whereNull('bin_id');
        }

        $stocks = $stocksQuery->get();

        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        $transferMode = $request->input('transfer_mode', 'single');

        if ($transferMode === 'full') {
            $validated = $request->validate([
                'outlet_id' => 'required|exists:outlets,id',
                'source_bin_id' => 'required|exists:warehouse_bins,id',
                'destination_bin_id' => 'required|exists:warehouse_bins,id',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validated['source_bin_id'] == $validated['destination_bin_id']) {
                Toastr::error('Source Bin and Destination Bin cannot be the same location!', 'Validation Error');
                return redirect()->back()->withInput();
            }

            try {
                $movedItemsCount = 0;
                $totalUnitsMoved = 0;

                DB::transaction(function () use ($validated, &$movedItemsCount, &$totalUnitsMoved) {
                    $outletId = $validated['outlet_id'];
                    $sourceBinId = $validated['source_bin_id'];
                    $destBinId = $validated['destination_bin_id'];

                    // Get all inventory stocks in source bin with quantity > 0
                    $sourceStocks = InventoryStock::where('outlet_id', $outletId)
                        ->where('bin_id', $sourceBinId)
                        ->where('quantity', '>', 0)
                        ->lockForUpdate()
                        ->get();

                    if ($sourceStocks->isEmpty()) {
                        throw new Exception("No active stock found in the selected Source Bin!");
                    }

                    foreach ($sourceStocks as $sourceStock) {
                        $moveQty = (float) $sourceStock->quantity;
                        $productId = $sourceStock->product_id;
                        $variantId = $sourceStock->variant_id;

                        // 1. Clear Source Stock
                        $sourceStock->quantity = 0;
                        $sourceStock->save();

                        // 2. Add to Destination Stock
                        $destStock = InventoryStock::firstOrCreate(
                            [
                                'outlet_id' => $outletId,
                                'bin_id' => $destBinId,
                                'product_id' => $productId,
                                'variant_id' => $variantId,
                            ],
                            [
                                'quantity' => 0,
                            ]
                        );

                        $destStock->quantity += $moveQty;
                        $destStock->save();

                        // 3. Move all StockBatches in this bin
                        StockBatch::where('outlet_id', $outletId)
                            ->where('bin_id', $sourceBinId)
                            ->where('product_id', $productId)
                            ->where('variant_id', $variantId)
                            ->where('qty_remaining', '>', 0)
                            ->update(['bin_id' => $destBinId]);

                        // 4. Create Audit Ledgers
                        StockLedger::create([
                            'outlet_id' => $outletId,
                            'bin_id' => $sourceBinId,
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'reference_type' => 'bin_relocation_out',
                            'reference_id' => $destBinId,
                            'in_qty' => 0,
                            'out_qty' => $moveQty,
                            'balance_qty' => 0,
                            'date' => now()->format('Y-m-d'),
                        ]);

                        StockLedger::create([
                            'outlet_id' => $outletId,
                            'bin_id' => $destBinId,
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'reference_type' => 'bin_relocation_in',
                            'reference_id' => $sourceBinId,
                            'in_qty' => $moveQty,
                            'out_qty' => 0,
                            'balance_qty' => $destStock->quantity,
                            'date' => now()->format('Y-m-d'),
                        ]);

                        $movedItemsCount++;
                        $totalUnitsMoved += $moveQty;
                    }
                });

                $destBinName = WarehouseBin::find($validated['destination_bin_id'])?->name ?? 'Destination Bin';
                Toastr::success("Full Bin Transfer Complete! Relocated {$totalUnitsMoved} units ({$movedItemsCount} SKUs) to {$destBinName}.", 'Full Bin Transfer Complete');
                return redirect()->route('admin.warehouse-bins.stocks', $validated['destination_bin_id']);
            } catch (Exception $e) {
                Toastr::error($e->getMessage(), 'Relocation Error');
                return redirect()->back()->withInput();
            }
        }

        // Single Product Item Transfer Mode
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'source_bin_id' => 'nullable|exists:warehouse_bins,id',
            'destination_bin_id' => 'required|exists:warehouse_bins,id',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['source_bin_id'] && $validated['source_bin_id'] == $validated['destination_bin_id']) {
            Toastr::error('Source Bin and Destination Bin cannot be the same location!', 'Validation Error');
            return redirect()->back()->withInput();
        }

        try {
            DB::transaction(function () use ($validated) {
                $outletId = $validated['outlet_id'];
                $sourceBinId = $validated['source_bin_id'] ?? null;
                $destBinId = $validated['destination_bin_id'];
                $productId = $validated['product_id'];
                $variantId = $validated['variant_id'] ?? null;
                $moveQty = (float) $validated['quantity'];

                // 1. Check Source Bin Stock
                $sourceQuery = InventoryStock::where('outlet_id', $outletId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId);

                if ($sourceBinId) {
                    $sourceQuery->where('bin_id', $sourceBinId);
                } else {
                    $sourceQuery->whereNull('bin_id');
                }

                $sourceStock = $sourceQuery->lockForUpdate()->first();

                if (!$sourceStock || $sourceStock->quantity < $moveQty) {
                    $srcName = $sourceBinId ? WarehouseBin::find($sourceBinId)?->name : 'Unassigned Bin';
                    throw new Exception("Insufficient stock in source bin [{$srcName}]. Available: " . ($sourceStock?->quantity ?? 0) . " Pcs");
                }

                // 2. Deduct from Source Stock
                $sourceStock->quantity -= $moveQty;
                $sourceStock->save();

                // 3. Add to Destination Stock
                $destStock = InventoryStock::firstOrCreate(
                    [
                        'outlet_id' => $outletId,
                        'bin_id' => $destBinId,
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $destStock->quantity += $moveQty;
                $destStock->save();

                // 4. Shift StockBatches to new Bin
                $batches = StockBatch::where('outlet_id', $outletId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->where('qty_remaining', '>', 0);

                if ($sourceBinId) {
                    $batches->where('bin_id', $sourceBinId);
                } else {
                    $batches->whereNull('bin_id');
                }

                $activeBatches = $batches->orderBy('received_date', 'asc')->get();

                $remainingToMove = $moveQty;

                foreach ($activeBatches as $batch) {
                    if ($remainingToMove <= 0) break;

                    if ($batch->qty_remaining <= $remainingToMove) {
                        // Move full batch to destination bin
                        $batch->bin_id = $destBinId;
                        $batch->save();
                        $remainingToMove -= $batch->qty_remaining;
                    } else {
                        // Split batch: reduce source batch & create new batch at destination bin
                        $takeQty = $remainingToMove;
                        $batch->qty_remaining -= $takeQty;
                        $batch->save();

                        StockBatch::create([
                            'product_id' => $batch->product_id,
                            'variant_id' => $batch->variant_id,
                            'outlet_id' => $batch->outlet_id,
                            'bin_id' => $destBinId,
                            'goods_receipt_id' => $batch->goods_receipt_id,
                            'purchase_detail_id' => $batch->purchase_detail_id,
                            'batch_no' => $batch->batch_no . '-REL',
                            'barcode' => $batch->barcode,
                            'qty_received' => $takeQty,
                            'qty_remaining' => $takeQty,
                            'unit_cost' => $batch->unit_cost,
                            'received_date' => $batch->received_date,
                        ]);

                        $remainingToMove = 0;
                    }
                }

                // 5. Create Audit Ledger Entries
                StockLedger::create([
                    'outlet_id' => $outletId,
                    'bin_id' => $sourceBinId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'reference_type' => 'bin_relocation_out',
                    'reference_id' => $destBinId,
                    'in_qty' => 0,
                    'out_qty' => $moveQty,
                    'balance_qty' => $sourceStock->quantity,
                    'date' => now()->format('Y-m-d'),
                ]);

                StockLedger::create([
                    'outlet_id' => $outletId,
                    'bin_id' => $destBinId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'reference_type' => 'bin_relocation_in',
                    'reference_id' => $sourceBinId ?? 0,
                    'in_qty' => $moveQty,
                    'out_qty' => 0,
                    'balance_qty' => $destStock->quantity,
                    'date' => now()->format('Y-m-d'),
                ]);
            });

            $destBinName = WarehouseBin::find($validated['destination_bin_id'])?->name ?? 'Destination Bin';
            Toastr::success("Successfully relocated {$validated['quantity']} units to {$destBinName}.", 'Relocation Complete');
            return redirect()->route('admin.warehouse-bins.stocks', $validated['destination_bin_id']);
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), 'Relocation Error');
            return redirect()->back()->withInput();
        }
    }
}
