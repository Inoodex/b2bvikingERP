<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\ProductVendor;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;

class AutoReplenishmentCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:auto-replenish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate Draft POs for items below minimum order quantity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Auto-Replenishment Engine...');

        // 1. Fetch products with MOQ > 0
        $products = Product::where('minimum_order_qty', '>', 0)->get();
        $generatedPos = 0;

        foreach ($products as $product) {
            // 2. Check total physical stock
            $totalStock = InventoryStock::where('product_id', $product->id)->sum('quantity');

            if ($totalStock <= $product->minimum_order_qty) {
                
                // 3. Find primary vendor or cheapest vendor
                $vendorPivot = ProductVendor::where('product_id', $product->id)
                    ->orderByDesc('is_primary')
                    ->orderBy('purchase_price', 'asc')
                    ->first();

                if ($vendorPivot) {
                    $this->info("Product {$product->name} is below MOQ. Generating Draft PO for Vendor {$vendorPivot->vendor_id}...");
                    
                    // 4. Generate Draft Purchase Order
                    $poQty = max($product->minimum_order_qty, 1);
                    $poTotal = $vendorPivot->purchase_price * $poQty;

                    $draftPo = Purchase::create([
                        'invoice_no' => 'AUTO-PO-' . strtoupper(uniqid()),
                        'vendor_id' => $vendorPivot->vendor_id,
                        'approval_status' => 'pending',
                        'total_amount' => $poTotal,
                        'date' => now()->format('Y-m-d'),
                        'user_id' => User::first()->id ?? 1
                    ]);

                    PurchaseDetail::create([
                        'purchase_id' => $draftPo->id,
                        'product_id' => $product->id,
                        'qty' => $poQty,
                        'unit_cost' => $vendorPivot->purchase_price,
                        'total' => $poTotal
                    ]);

                    $generatedPos++;
                }
            }
        }

        $this->info("Auto-Replenishment Engine completed. Generated {$generatedPos} Draft POs.");
    }
}
