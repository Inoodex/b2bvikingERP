<?php

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\InventoryStock;
use App\Models\StockBatch;
use App\Models\MonthEndSnapshot;
use App\Models\Product;
use App\Models\Outlet;
use App\Models\Company;

class TakeInventorySnapshotTest extends TestCase
{
    use DatabaseTransactions;

    public function test_inventory_snapshot_command_stores_correct_data(): void
    {
        $company = Company::first() ?? Company::create([
            'name' => 'Test Company',
            'code' => 'TC-' . uniqid(),
            'email' => 'company@example.com',
            'phone' => '+45 11223344',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);

        $outlet = Outlet::create([
            'name' => 'Snapshot Outlet ' . uniqid(),
            'code' => 'OUT-' . uniqid(),
            'company_id' => $company->id,
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Snapshot Product ' . uniqid(),
            'slug' => 'snapshot-prod-' . uniqid(),
            'price' => 50.00,
            'purchase_price' => 10.00,
            'status' => 1,
        ]);

        // Create some stock batches
        StockBatch::create([
            'batch_no' => 'BATCH-' . uniqid(),
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'qty_received' => 50,
            'qty_remaining' => 50,
            'unit_cost' => 10, // Total = 500
            'received_date' => now()->toDateString(),
        ]);

        StockBatch::create([
            'batch_no' => 'BATCH-' . uniqid(),
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'qty_received' => 20,
            'qty_remaining' => 20,
            'unit_cost' => 15, // Total = 300
            'received_date' => now()->toDateString(),
        ]);

        // Total FIFO value should be 800 for 70 qty.
        // And physical inventory
        InventoryStock::create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'quantity' => 70, // Matches batch
        ]);

        // Run the command
        $this->artisan('inventory:take-snapshot', [
            '--period' => now()->format('Y-m')
        ])->assertExitCode(0);

        // Verify the snapshot
        $this->assertDatabaseHas('month_end_snapshots', [
            'period' => now()->format('Y-m'),
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'closing_qty' => 70,
            'closing_value' => 800.00, // 50*10 + 20*15
        ]);
    }
}

