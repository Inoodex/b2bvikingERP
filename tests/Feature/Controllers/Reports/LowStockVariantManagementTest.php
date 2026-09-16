<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Reports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use Tests\Feature\Controllers\ControllerTestCase;

class LowStockVariantManagementTest extends ControllerTestCase
{
    public function test_low_stock_report_detects_product_with_out_of_stock_variant_despite_high_total_stock(): void
    {
        $category = Category::first() ?? Category::create(['name' => 'Variant Cat ' . uniqid(), 'slug' => 'cat-' . uniqid(), 'status' => 1]);
        $brand = Brand::first() ?? Brand::create(['name' => 'Variant Brand ' . uniqid(), 'slug' => 'brand-' . uniqid(), 'status' => 1]);

        // Product with min_inventory_qty = 10
        $product = Product::create([
            'name' => 'Enterprise Variant Test Hood ' . uniqid(),
            'slug' => 'variant-hood-' . uniqid(),
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 1,
            'min_inventory_qty' => 10,
            'price' => 199.00,
        ]);

        // Variant 1: Healthy (50 units)
        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Large Healthy',
            'qty' => 50,
            'price' => 199.00,
            'status' => 1,
        ]);
        InventoryStock::create([
            'product_id' => $product->id,
            'variant_id' => $variant1->id,
            'outlet_id' => 1,
            'quantity' => 50,
        ]);

        // Variant 2: Out of stock (0 units) - This should trigger enterprise low stock!
        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Small Empty',
            'qty' => 0,
            'price' => 199.00,
            'status' => 1,
        ]);
        InventoryStock::create([
            'product_id' => $product->id,
            'variant_id' => $variant2->id,
            'outlet_id' => 1,
            'quantity' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.reports.low-stock'));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('2 Variants');
        $response->assertSee('VARIANT DEFICIT');
        $response->assertSee('Variant Stock Breakdown');
        $response->assertSee('Small Empty');
        $response->assertSee('Large Healthy');
        $response->assertSee('OUT OF STOCK');
    }

    public function test_can_add_specific_variant_to_procurement_cart(): void
    {
        $product = Product::where('status', 1)->first() ?? Product::create([
            'name' => 'Cart Product ' . uniqid(),
            'slug' => 'cart-prod-' . uniqid(),
            'status' => 1,
            'price' => 100,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Small Variant ' . uniqid(),
            'price' => 150,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.cart.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'cart_type' => 'booking',
            'action' => 'add',
            'quantity' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'in_cart' => true,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->adminUser->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'cart_type' => 'booking',
            'quantity' => 3,
        ]);
    }

    public function test_can_bulk_add_multiple_variants_from_modal(): void
    {
        $product = Product::where('status', 1)->first() ?? Product::create([
            'name' => 'Bulk Product ' . uniqid(),
            'slug' => 'bulk-prod-' . uniqid(),
            'status' => 1,
            'price' => 100,
        ]);

        $v1 = ProductVariant::create(['product_id' => $product->id, 'name' => 'Red - S ' . uniqid(), 'status' => 1]);
        $v2 = ProductVariant::create(['product_id' => $product->id, 'name' => 'Red - M ' . uniqid(), 'status' => 1]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.cart.add'), [
            'product_id' => $product->id,
            'cart_type' => 'booking',
            'action' => 'bulk_variants',
            'is_bulk_variants' => 1,
            'variants' => [
                ['variant_id' => $v1->id, 'quantity' => 5],
                ['variant_id' => $v2->id, 'quantity' => 10],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'in_cart' => true,
            'count' => 2,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->adminUser->id,
            'product_id' => $product->id,
            'variant_id' => $v1->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->adminUser->id,
            'product_id' => $product->id,
            'variant_id' => $v2->id,
            'quantity' => 10,
        ]);
    }
}
