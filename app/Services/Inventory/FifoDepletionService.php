<?php

namespace App\Services\Inventory;

use App\Models\InventoryStock;
use App\Models\StockBatch;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;
use Exception;

class FifoDepletionService
{
    /**
     * Deplete stock using FIFO method based on exact batch Landed Costs.
     * 
     * @param int $outletId
     * @param int $productId
     * @param int|null $variantId
     * @param float $requiredQty
     * @param string $referenceType
     * @param int $referenceId
     * @return array Returns array containing total_cogs and depleted log.
     * @throws Exception
     */
    public function depleteStock(
        int $outletId, 
        ?int $binId,
        int $productId, 
        ?int $variantId, 
        float $requiredQty, 
        string $referenceType, 
        int $referenceId
    ): array {
        return DB::transaction(function () use ($outletId, $binId, $productId, $variantId, $requiredQty, $referenceType, $referenceId) {
            
            // 1. Lock the global physical stock
            $stockQuery = InventoryStock::where('outlet_id', $outletId)
                ->where('product_id', $productId)
                ->where('variant_id', $variantId);
                
            if ($binId) {
                $stockQuery->where('bin_id', $binId);
            }

            $stock = $stockQuery->lockForUpdate()->first();

            if (!$stock || $stock->quantity < $requiredQty) {
                $binStr = $binId ? " Bin ID: {$binId}" : " General Warehouse";
                throw new Exception("Insufficient stock in{$binStr} for Product ID: {$productId}");
            }

            // 2. Fetch active batches in FIFO order
            $batches = StockBatch::where('outlet_id', $outletId)
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->where('qty_remaining', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $remainingToDeplete = $requiredQty;
            $totalCOGS = 0.00;
            $depletionLog = [];

            // 3. Loop and deplete
            foreach ($batches as $batch) {
                if ($remainingToDeplete <= 0) {
                    break;
                }

                $qtyToTake = min($batch->qty_remaining, $remainingToDeplete);
                
                // Deduct from batch
                $batch->qty_remaining -= $qtyToTake;
                $batch->save();

                $costForThisTake = $qtyToTake * $batch->unit_cost;
                $totalCOGS += $costForThisTake;
                $remainingToDeplete -= $qtyToTake;

                $depletionLog[] = [
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'bin_id' => $batch->bin_id,
                    'bin_name' => $batch->bin?->name ?? 'Default Staging Bin',
                    'bin_barcode' => $batch->bin?->barcode ?? 'N/A',
                    'qty_taken' => $qtyToTake,
                    'unit_cost' => $batch->unit_cost,
                    'total_cost' => $costForThisTake
                ];

                // Write detailed StockLedger entry for this specific batch
                $stock->quantity -= $qtyToTake;
                $stock->save();

                StockLedger::create([
                    'outlet_id' => $outletId,
                    'bin_id' => $batch->bin_id ?? $binId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'batch_id' => $batch->id,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'in_qty' => 0,
                    'out_qty' => $qtyToTake,
                    'balance_qty' => $stock->quantity,
                    'date' => now()->format('Y-m-d'),
                ]);
            }

            if ($remainingToDeplete > 0) {
                throw new Exception("FIFO Corruption: Total active batches do not have enough quantity for Product ID: {$productId}");
            }

            return [
                'total_cogs' => $totalCOGS,
                'avg_unit_cost' => $requiredQty > 0 ? ($totalCOGS / $requiredQty) : 0,
                'log' => $depletionLog
            ];
        });
    }
}
