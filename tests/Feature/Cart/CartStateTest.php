<?php

namespace Tests\Feature\Cart;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartStateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_toggle_cart_items_and_get_all_state_in_single_call(): void
    {
        $this->withoutMiddleware();

        $user = User::first() ?? User::create([
            'name' => 'Cart Admin',
            'email' => 'cart_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        // Clear existing cart for clean test
        Cart::where('user_id', $user->id)->delete();

        $category = Category::first() ?? Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-' . uniqid(),
            'status' => 1,
        ]);

        $product = Product::first() ?? Product::create([
            'name' => 'Test Cart Product',
            'slug' => 'test-cart-product-' . uniqid(),
            'thumb_image' => 'default.png',
            'category_id' => $category->id,
            'qty' => 100,
            'price' => 100,
            'purchase_price' => 70,
            'status' => 1,
        ]);

        // 1. Add to booking basket
        $response = $this->postJson(route('admin.cart.add'), [
            'product_id' => $product->id,
            'cart_type' => 'booking'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'added',
            'in_cart' => true,
            'count' => 1
        ]);

        // 2. Add to request basket
        $response = $this->postJson(route('admin.cart.add'), [
            'product_id' => $product->id,
            'cart_type' => 'request'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'added',
            'in_cart' => true,
            'count' => 1
        ]);

        // 3. Test getAllState returns both
        $stateResponse = $this->getJson(route('admin.cart.all-state'));
        $stateResponse->assertStatus(200);
        $stateResponse->assertJson([
            'booking' => [
                'ids' => [$product->id],
                'count' => 1
            ],
            'request' => [
                'ids' => [$product->id],
                'count' => 1
            ]
        ]);

        // 4. Toggle remove from booking basket
        $toggleResponse = $this->postJson(route('admin.cart.add'), [
            'product_id' => $product->id,
            'cart_type' => 'booking'
        ]);

        $toggleResponse->assertStatus(200);
        $toggleResponse->assertJson([
            'success' => true,
            'action' => 'removed',
            'in_cart' => false,
            'count' => 0
        ]);
    }

    public function test_can_add_and_update_variant_cart_items(): void
    {
        $this->withoutMiddleware();

        $user = User::first() ?? User::create([
            'name' => 'Cart Admin 2',
            'email' => 'cart_admin2_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        Cart::where('user_id', $user->id)->delete();

        $category = Category::first() ?? Category::create([
            'name' => 'Variant Cat',
            'slug' => 'variant-cat-' . uniqid(),
            'status' => 1,
        ]);

        $product = Product::first() ?? Product::create([
            'name' => 'Variant Product',
            'slug' => 'variant-prod-' . uniqid(),
            'thumb_image' => 'default.png',
            'category_id' => $category->id,
            'qty' => 100,
            'price' => 100,
            'purchase_price' => 70,
            'status' => 1,
        ]);

        $variant = ProductVariant::where('product_id', $product->id)->first() ?? ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Red - XL',
            'qty' => 50,
            'price' => 120,
            'status' => 1
        ]);

        // Add variant item
        $response = $this->postJson(route('admin.cart.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
            'cart_type' => 'booking'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'added',
            'in_cart' => true,
            'count' => 1
        ]);

        // Update quantity
        $qtyResponse = $this->postJson(route('admin.cart.update-quantity'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'cart_type' => 'booking',
            'quantity' => 10
        ]);

        $qtyResponse->assertStatus(200);
        $qtyResponse->assertJson([
            'success' => true,
            'quantity' => 10
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 10
        ]);
    }
}
