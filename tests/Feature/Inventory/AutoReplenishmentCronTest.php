<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\ProductVendor;
use App\Models\InventoryStock;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoReplenishmentCronTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);

        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $unit = Unit::create(['name' => 'Pieces', 'slug' => 'pieces']);

        $this->vendor = Vendor::create([
            'shop_name' => 'Test Vendor Shop',
            'email' => 'vendor@example.com',
            'phone' => '1234567890',
            'address' => '123 Vendor St',
            'country' => 'USA'
        ]);

        $this->product = Product::create([
            'name' => 'Low Stock Product',
            'slug' => 'low-stock-product',
            'sku' => 'SKU-LOW',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'minimum_order_qty' => 50,
            'status' => 1,
            'manage_stock' => 1,
        ]);

        ProductVendor::create([
            'product_id' => $this->product->id,
            'vendor_id' => $this->vendor->id,
            'purchase_price' => 10.00,
            'is_primary' => true,
        ]);
    }

    public function test_it_generates_draft_po_when_stock_is_below_moq(): void
    {
        // Add stock below MOQ (e.g., 20 <= 50)
        InventoryStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => 1, // Doesn't matter for this test
            'quantity' => 20,
        ]);

        $exitCode = Artisan::call('inventory:auto-replenish');

        $this->assertEquals(0, $exitCode);

        // Assert Draft PO is created
        $this->assertDatabaseHas('purchases', [
            'vendor_id' => $this->vendor->id,
            'approval_status' => 'pending',
        ]);

        $this->assertDatabaseHas('purchase_details', [
            'product_id' => $this->product->id,
            'qty' => 50, // Minimum order quantity
        ]);
    }
}
