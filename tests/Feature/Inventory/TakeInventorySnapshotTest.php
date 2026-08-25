<?php

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\InventoryStock;
use App\Models\StockBatch;
use App\Models\MonthEndSnapshot;
use App\Models\Product;
use App\Models\Outlet;

class TakeInventorySnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_snapshot_command_stores_correct_data(): void
    {
        // Setup initial data
        $outlet = Outlet::factory()->create();
        $product = Product::factory()->create();

        // Create some stock batches
        StockBatch::factory()->create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'qty_remaining' => 50,
            'unit_cost' => 10, // Total = 500
        ]);

        StockBatch::factory()->create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'qty_remaining' => 20,
            'unit_cost' => 15, // Total = 300
        ]);

        // Total FIFO value should be 800 for 70 qty.
        // And physical inventory
        InventoryStock::factory()->create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'quantity' => 70, // Matches batch
        ]);

        // Run the command
        $this->artisan('inventory:snapshot', [
            '--month' => now()->format('Y-m')
        ])->assertExitCode(0);

        // Verify the snapshot
        $this->assertDatabaseHas('month_end_snapshots', [
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'total_physical_qty' => 70,
            'fifo_valuation' => 800, // 50*10 + 20*15
        ]);
    }
}
