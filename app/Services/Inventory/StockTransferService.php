<?php

namespace App\Services\Inventory;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\OrderNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    /**
     * Create a new Stock Transfer in Draft state.
     */
    public function createTransfer(array $data, array $items): StockTransfer
    {
        return DB::transaction(function () use ($data, $items) {
            $transferNo = OrderNumberService::generate('TRN', StockTransfer::class, 'stock_transfers');

            $transfer = StockTransfer::create([
                'transfer_no' => $transferNo,
                'from_outlet_id' => $data['from_outlet_id'] ?? 1,
                'to_outlet_id' => $data['to_outlet_id'],
                'requested_by' => Auth::id() ?? 1,
                'status' => 'draft',
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'challan_no' => $data['challan_no'] ?? null,
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'driver_phone' => $data['driver_phone'] ?? null,
                'note' => $data['note'] ?? null,
                'total_items_count' => count($items),
            ]);

            foreach ($items as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $productId = $item['product_id'];
                $variantId = !empty($item['variant_id']) ? $item['variant_id'] : null;

                $product = Product::find($productId);
                $unitCost = (float) ($item['unit_cost'] ?? ($product->purchase_price ?? $product->price ?? 0.00));

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'item_note' => $item['item_note'] ?? null,
                ]);
            }

            return $transfer->fresh(['items.product', 'items.variant', 'fromOutlet', 'toOutlet', 'requestedByUser']);
        });
    }

    /**
     * Dispatch a Stock Transfer (Deducts stock from Source Outlet and puts in-transit).
     */
    public function dispatchTransfer(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== 'draft') {
            throw new \Exception("Only draft transfers can be dispatched.");
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->loadMissing('items');

            foreach ($transfer->items as $item) {
                $stock = InventoryStock::where([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'outlet_id' => $transfer->from_outlet_id,
                ])->first();

                $available = $stock ? (float) $stock->quantity : 0.00;
                if ($available < (float) $item->qty) {
                    $productName = $item->product ? $item->product->name : "Product #{$item->product_id}";
                    throw new \Exception("Insufficient stock at source warehouse for '{$productName}'. Available: {$available}, Required: {$item->qty}");
                }

                $stock->decrement('quantity', $item->qty);

                StockLedger::create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'outlet_id' => $transfer->from_outlet_id,
                    'reference_type' => 'stock_transfer_out',
                    'reference_id' => $transfer->id,
                    'in_qty' => 0,
                    'out_qty' => $item->qty,
                    'balance_qty' => $stock->quantity,
                    'date' => now(),
                ]);
            }

            $transfer->update([
                'status' => 'dispatched',
                'dispatched_by' => Auth::id() ?? 1,
                'dispatched_at' => now(),
            ]);

            return $transfer->fresh();
        });
    }

    /**
     * Receive a Stock Transfer at Destination Outlet.
     */
    public function receiveTransfer(StockTransfer $transfer, array $receivedItems = []): StockTransfer
    {
        if ($transfer->status !== 'dispatched') {
            throw new \Exception("Only dispatched transfers in transit can be received.");
        }

        return DB::transaction(function () use ($transfer, $receivedItems) {
            $transfer->loadMissing('items');

            foreach ($transfer->items as $item) {
                // If specific received quantities were passed, use them; otherwise use dispatched qty
                $receivedQty = isset($receivedItems[$item->id])
                    ? (float) $receivedItems[$item->id]
                    : (float) $item->qty;

                $item->update(['received_qty' => $receivedQty]);

                $destStock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'outlet_id' => $transfer->to_outlet_id,
                    ],
                    ['quantity' => 0]
                );

                $destStock->increment('quantity', $receivedQty);

                StockLedger::create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'outlet_id' => $transfer->to_outlet_id,
                    'reference_type' => 'stock_transfer_in',
                    'reference_id' => $transfer->id,
                    'in_qty' => $receivedQty,
                    'out_qty' => 0,
                    'balance_qty' => $destStock->quantity,
                    'date' => now(),
                ]);
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => Auth::id() ?? 1,
                'received_at' => now(),
            ]);

            return $transfer->fresh();
        });
    }

    /**
     * Cancel a draft transfer.
     */
    public function cancelTransfer(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== 'draft') {
            throw new \Exception("Only draft transfers can be cancelled.");
        }

        $transfer->update([
            'status' => 'cancelled',
        ]);

        return $transfer->fresh();
    }
}
