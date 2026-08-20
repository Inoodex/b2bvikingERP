<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\User;
use App\Services\Inventory\StockAdjustmentService;
use App\Services\Inventory\StockTransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StockAdjustmentAndTransferTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateUser(): User
    {
        return User::first() ?? User::create([
            'name' => 'Inventory Manager',
            'email' => 'inv_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function getOrCreateOutlet(string $name = 'Warehouse'): Outlet
    {
        $company = \App\Models\Company::first() ?? \App\Models\Company::create([
            'name' => 'Copenhagen Holdings A/S',
            'code' => 'CPH-' . uniqid(),
            'email' => 'holdings@cph.dk',
            'phone' => '+45 11223344',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);

        return Outlet::create([
            'name' => $name . ' ' . uniqid(),
            'code' => 'OUT-' . uniqid(),
            'company_id' => $company->id,
            'status' => 1,
        ]);
    }

    protected function getOrCreateProduct(): Product
    {
        return Product::first() ?? Product::create([
            'name' => 'Stock Test Item',
            'slug' => 'stock-item-' . uniqid(),
            'price' => 50.00,
            'cost' => 20.00,
            'status' => 1,
        ]);
    }

    public function test_stock_adjustment_approves_and_updates_physical_stock(): void
    {
        $user = $this->getOrCreateUser();
        $this->actingAs($user);

        $outlet = $this->getOrCreateOutlet('Adjustment WH');
        $product = $this->getOrCreateProduct();

        $stock = InventoryStock::firstOrCreate(
            ['outlet_id' => $outlet->id, 'product_id' => $product->id, 'variant_id' => null],
            ['quantity' => 50]
        );
        $stock->update(['quantity' => 50]);

        $service = app(StockAdjustmentService::class);

        // Adjust -10 due to physical damage
        $adjustment = $service->createAdjustment([
            'outlet_id' => $outlet->id,
            'adjustment_type' => 'decrease',
            'reason_code' => 'damage',
            'reason' => 'Water damage in warehouse',
        ], [
            [
                'product_id' => $product->id,
                'variant_id' => null,
                'adjusted_qty' => 10,
                'unit_cost' => 15.00,
            ]
        ]);

        $this->assertEquals('draft', $adjustment->status);

        // Approve Adjustment
        $service->approveAdjustment($adjustment);

        $this->assertEquals('approved', $adjustment->fresh()->status);
        $this->assertEquals(40, (float) $stock->fresh()->quantity);

        $this->assertDatabaseHas('stock_ledgers', [
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'out_qty' => 10,
            'balance_qty' => 40,
        ]);
    }

    public function test_3_stage_stock_transfer_moves_stock_between_outlets(): void
    {
        $user = $this->getOrCreateUser();
        $this->actingAs($user);

        $sourceOutlet = $this->getOrCreateOutlet('Source Outlet');
        $destOutlet = $this->getOrCreateOutlet('Dest Outlet');
        $product = $this->getOrCreateProduct();

        // Source stock: 100, Dest stock: 0
        $sourceStock = InventoryStock::firstOrCreate(
            ['outlet_id' => $sourceOutlet->id, 'product_id' => $product->id, 'variant_id' => null],
            ['quantity' => 100]
        );
        $sourceStock->update(['quantity' => 100]);

        $destStock = InventoryStock::firstOrCreate(
            ['outlet_id' => $destOutlet->id, 'product_id' => $product->id, 'variant_id' => null],
            ['quantity' => 0]
        );
        $destStock->update(['quantity' => 0]);

        $transferService = app(StockTransferService::class);

        // Stage 1: Create Draft Transfer for 30 units
        $transfer = $transferService->createTransfer([
            'from_outlet_id' => $sourceOutlet->id,
            'to_outlet_id' => $destOutlet->id,
            'vehicle_no' => 'COP-789',
            'driver_name' => 'Lars Nielsen',
        ], [
            [
                'product_id' => $product->id,
                'variant_id' => null,
                'qty' => 30,
                'unit_cost' => 20.00,
            ]
        ]);

        $this->assertEquals('draft', $transfer->status);

        // Stage 2: Dispatch Transfer (Source Stock Decrements to 70)
        $transferService->dispatchTransfer($transfer);

        $this->assertEquals('dispatched', $transfer->fresh()->status);
        $this->assertEquals(70, (float) $sourceStock->fresh()->quantity);
        $this->assertEquals(0, (float) $destStock->fresh()->quantity); // Dest not yet received

        // Stage 3: Receive Transfer (Dest Stock Increments to 30)
        $transferService->receiveTransfer($transfer);

        $this->assertEquals('received', $transfer->fresh()->status);
        $this->assertEquals(70, (float) $sourceStock->fresh()->quantity);
        $this->assertEquals(30, (float) $destStock->fresh()->quantity);

        $this->assertDatabaseHas('stock_ledgers', [
            'outlet_id' => $sourceOutlet->id,
            'product_id' => $product->id,
            'reference_type' => 'stock_transfer_out',
            'out_qty' => 30,
            'balance_qty' => 70,
        ]);

        $this->assertDatabaseHas('stock_ledgers', [
            'outlet_id' => $destOutlet->id,
            'product_id' => $product->id,
            'reference_type' => 'stock_transfer_in',
            'in_qty' => 30,
            'balance_qty' => 30,
        ]);
    }
}
