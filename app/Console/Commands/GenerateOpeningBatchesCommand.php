<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryStock;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;

class GenerateOpeningBatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-opening-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates opening FIFO StockBatches for all existing inventory stock records that lack batches.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning inventory stocks without batches...');

        $stocks = InventoryStock::with(['product', 'variant'])
            ->where('quantity', '>', 0)
            ->get();

        $count = 0;
        $now = now();

        DB::transaction(function () use ($stocks, &$count, $now) {
            foreach ($stocks as $stock) {
                // Check if a batch already exists for this stock
                $hasBatch = StockBatch::where('outlet_id', $stock->outlet_id)
                    ->where('product_id', $stock->product_id)
                    ->where('variant_id', $stock->variant_id)
                    ->exists();

                if (!$hasBatch) {
                    $unitCost = (float) ($stock->product->purchase_price ?? 0);
                    $batchCode = 'BATCH-OPENING-' . ($stock->outlet_id ?? 1) . '-' . $stock->product_id . ($stock->variant_id ? '-' . $stock->variant_id : '');

                    StockBatch::create([
                        'outlet_id' => $stock->outlet_id,
                        'product_id' => $stock->product_id,
                        'variant_id' => $stock->variant_id,
                        'location_bin_id' => $stock->bin_id,
                        'batch_no' => $batchCode,
                        'qty_received' => $stock->quantity,
                        'qty_remaining' => $stock->quantity,
                        'unit_cost' => $unitCost,
                        'received_date' => $now->toDateString(),
                    ]);

                    $count++;
                }
            }
        });

        $this->info("Successfully generated {$count} opening FIFO stock batches!");

        return Command::SUCCESS;
    }
}
