<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryStock;
use App\Models\StockBatch;
use App\Models\MonthEndSnapshot;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

class TakeInventorySnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:take-snapshot {--period= : Force a specific period like 2026-08}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Takes a frozen snapshot of all inventory physical stock and FIFO valuations for month-end closing.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $period = $this->option('period') ?: now()->format('Y-m');
        $this->info("Taking Inventory Snapshot for Period: {$period}");

        try {
            DB::transaction(function () use ($period) {
                // To prevent duplicates, clear any existing snapshots for this exact period
                MonthEndSnapshot::where('period', $period)->delete();

                // Group active stock batches by outlet, product, and variant to get the exact FIFO valuation
                $batchValuations = StockBatch::where('qty_remaining', '>', 0)
                    ->select(
                        'outlet_id',
                        'product_id',
                        'variant_id',
                        DB::raw('SUM(qty_remaining) as total_batch_qty'),
                        DB::raw('SUM(qty_remaining * unit_cost) as total_valuation')
                    )
                    ->groupBy('outlet_id', 'product_id', 'variant_id')
                    ->get()
                    ->keyBy(function($item) {
                        return $item->outlet_id . '_' . $item->product_id . '_' . ($item->variant_id ?: '0');
                    });

                // Fetch physical inventory stocks
                $stocks = InventoryStock::with('product')->where('quantity', '>', 0)->get();

                $snapshotData = [];
                $now = now();

                foreach ($stocks as $stock) {
                    $key = $stock->outlet_id . '_' . $stock->product_id . '_' . ($stock->variant_id ?: '0');
                    
                    $valuation = 0.00;
                    if ($batchValuations->has($key)) {
                        $batchData = $batchValuations->get($key);
                        
                        $avgUnitCost = $batchData->total_batch_qty > 0 
                            ? ($batchData->total_valuation / $batchData->total_batch_qty) 
                            : 0;

                        $valuation = $stock->quantity * $avgUnitCost;
                    } else {
                        // Fallback to product purchase price for legacy opening stock
                        $purchasePrice = (float) ($stock->product->purchase_price ?? 0);
                        $valuation = $stock->quantity * $purchasePrice;
                    }

                    $snapshotData[] = [
                        'period' => $period,
                        'outlet_id' => $stock->outlet_id,
                        'product_id' => $stock->product_id,
                        'variant_id' => $stock->variant_id,
                        'closing_qty' => $stock->quantity,
                        'closing_value' => $valuation,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($snapshotData)) {
                    // Bulk insert chunked to save memory
                    foreach (array_chunk($snapshotData, 1000) as $chunk) {
                        MonthEndSnapshot::insert($chunk);
                    }
                }
            });

            $this->info("Successfully created inventory snapshots for {$period}.");
            return Command::SUCCESS;

        } catch (Exception $e) {
            Log::error("Failed to take inventory snapshot: " . $e->getMessage());
            $this->error("Failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
