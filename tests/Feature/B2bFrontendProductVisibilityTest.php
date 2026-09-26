<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CustomerProductVisibility;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class B2bFrontendProductVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Outlet User', 'web');
        Role::findOrCreate('User', 'web');
    }

    private function createOutletUser(?int $outletId = null): User
    {
        $outlet = $outletId ? (Outlet::find($outletId) ?? Outlet::create([
            'id' => $outletId,
            'name' => 'Outlet ' . $outletId,
            'code' => 'OUT-' . $outletId . '-' . rand(100, 999),
            'status' => 1,
        ])) : (Outlet::first() ?? Outlet::create([
            'name' => 'Test Outlet ' . uniqid(),
            'code' => 'OUT-' . rand(1000, 9999),
            'status' => 1,
        ]));

        $user = User::create([
            'name' => 'Outlet Manager ' . uniqid(),
            'email' => 'outlet_user_' . uniqid() . '@b2bviking.test',
            'password' => bcrypt('password123'),
            'outlet_id' => $outlet->id,
            'phone' => '+47' . rand(10000000, 99999999),
            'status' => 1,
        ]);
        $user->assignRole('Outlet User');
        return $user;
    }

    private function createTestProduct(float $realStock = 0): Product
    {
        $category = Category::where('status', 1)->first() ?? Category::create([
            'name' => 'Nordic Gear ' . uniqid(),
            'slug' => 'nordic-gear-' . uniqid(),
            'status' => 1,
            'frontend_show' => 1,
        ]);

        $product = Product::create([
            'name' => 'Visibility Test Item ' . uniqid(),
            'slug' => 'visibility-test-item-' . uniqid(),
            'thumb_image' => 'default.png',
            'category_id' => $category->id,
            'qty' => $realStock,
            'price' => 120.00,
            'outlet_price' => 95.00,
            'status' => 1,
            'minimum_order_qty' => 1,
        ]);

        if ($realStock > 0) {
            $outlet = Outlet::first() ?? Outlet::create([
                'name' => 'Default Warehouse',
                'code' => 'WH-DEF-' . rand(100, 999),
                'status' => 1,
            ]);
            InventoryStock::create([
                'product_id' => $product->id,
                'outlet_id' => $outlet->id,
                'quantity' => $realStock,
            ]);
        }

        return $product;
    }

    public function test_outlet_user_sees_force_in_stock_even_when_inventory_is_zero_and_can_add_to_cart()
    {
        $user = $this->createOutletUser();
        $product = $this->createTestProduct(realStock: 0);

        // Apply override: Force in stock for user outlet
        CustomerProductVisibility::create([
            'product_id' => $product->id,
            'outlet_id' => $user->outlet_id,
            'visibility_mode' => 'force_in_stock',
            'reserved_qty' => 150,
            'notes' => 'Contract allocation',
        ]);

        $this->actingAs($user);

        // 1. Check Product details view
        $response = $this->get(route('product.details', $product->slug));
        $response->assertOk();
        $detailData = $response->viewData('detailProductData');
        $this->assertEquals(150, $detailData['stock']);

        // 2. Check Shop page (/shop) card stock
        $shopResponse = $this->get(route('shop'));
        $shopResponse->assertOk();
        $shopCards = $shopResponse->viewData('shopCards');
        $card = $shopCards->first(fn($c) => $c['product']['id'] === $product->id);
        $this->assertNotNull($card);
        $this->assertEquals(150, $card['product']['stock']);

        // 3. Check Add to Cart
        $cartResponse = $this->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $cartResponse->assertOk();
        $cartResponse->assertJson([
            'success' => true,
            'action' => 'added',
        ]);
    }

    public function test_outlet_user_blocked_by_force_out_of_stock_even_when_inventory_exists()
    {
        $user = $this->createOutletUser();
        $product = $this->createTestProduct(realStock: 80);

        // Override: Force out of stock for this specific user
        CustomerProductVisibility::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'visibility_mode' => 'force_out_of_stock',
            'notes' => 'Credit lock out of stock override',
        ]);

        $this->actingAs($user);

        // 1. Product details view shows stock 0
        $response = $this->get(route('product.details', $product->slug));
        $response->assertOk();
        $detailData = $response->viewData('detailProductData');
        $this->assertEquals(0, $detailData['stock']);

        // 2. Shop page (/shop) card shows stock 0
        $shopResponse = $this->get(route('shop'));
        $shopResponse->assertOk();
        $shopCards = $shopResponse->viewData('shopCards');
        $card = $shopCards->first(fn($c) => $c['product']['id'] === $product->id);
        $this->assertNotNull($card);
        $this->assertEquals(0, $card['product']['stock']);

        // 3. Add to cart is rejected with 422
        $cartResponse = $this->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartResponse->assertStatus(422);
        $cartResponse->assertJson([
            'success' => false,
            'message' => 'This item is out of stock.',
        ]);
    }

    public function test_outlet_user_cannot_see_or_access_hidden_product()
    {
        $user = $this->createOutletUser();
        $product = $this->createTestProduct(realStock: 50);

        // Override: Hide product for user outlet
        CustomerProductVisibility::create([
            'product_id' => $product->id,
            'outlet_id' => $user->outlet_id,
            'visibility_mode' => 'hide_product',
        ]);

        $this->actingAs($user);

        // 1. Direct page visit should 404
        $response = $this->get(route('product.details', $product->slug));
        $response->assertNotFound();

        // 2. Add to cart should fail with 422
        $cartResponse = $this->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $cartResponse->assertStatus(422);

        // 3. Shop page should not include product card
        $shopResponse = $this->get(route('shop'));
        $shopResponse->assertOk();
        $cards = $shopResponse->viewData('shopCards');
        $hasProductInShop = $cards->contains(fn($card) => $card['product']['id'] === $product->id);
        $this->assertFalse($hasProductInShop);
    }

    public function test_different_outlet_user_unaffected_by_other_outlet_override()
    {
        $outlet1 = Outlet::create(['name' => 'Outlet One ' . uniqid(), 'code' => 'OUT1-' . rand(100, 999), 'status' => 1]);
        $outlet2 = Outlet::create(['name' => 'Outlet Two ' . uniqid(), 'code' => 'OUT2-' . rand(100, 999), 'status' => 1]);

        $userOutlet1 = $this->createOutletUser($outlet1->id);
        $userOutlet2 = $this->createOutletUser($outlet2->id);
        $product = $this->createTestProduct(realStock: 0);

        // Override: Force in stock ONLY for outlet 1
        CustomerProductVisibility::create([
            'product_id' => $product->id,
            'outlet_id' => $userOutlet1->outlet_id,
            'visibility_mode' => 'force_in_stock',
            'reserved_qty' => 50,
        ]);

        // Outlet 2 user has no override and real stock is 0
        $this->actingAs($userOutlet2);

        $cartResponse = $this->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartResponse->assertStatus(422);
        $cartResponse->assertJson([
            'success' => false,
            'message' => 'This item is out of stock.',
        ]);
    }
}
