<?php

namespace Tests\Feature\Sales;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalesFulfillmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_delivery_order_and_dispatch_with_stock_deduction(): void
    {
        $this->withoutMiddleware();
        $user = User::first() ?? User::create([
            'name' => 'Sales Admin',
            'email' => 'sales_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role_id' => 1,
        ]);
        $this->actingAs($user);

        $product = Product::first() ?? Product::create([
            'name' => 'Dispatch Product ' . uniqid(),
            'slug' => 'dispatch-product-' . uniqid(),
            'price' => 25.00,
            'cost' => 10.00,
            'status' => 1,
        ]);

        // Ensure sufficient initial stock
        $stock = InventoryStock::firstOrCreate(
            ['product_id' => $product->id, 'variant_id' => null],
            ['quantity' => 100]
        );
        $stock->update(['quantity' => 100]);

        $order = Order::create([
            'order_no' => 'SO-TEST-001',
            'user_id' => $user->id,
            'billing_name' => $user->name ?? 'John Doe',
            'billing_email' => $user->email ?? 'john@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => '123 Copenhagen Street',
            'total_amount' => 500.00,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name ?? 'Sample Product',
            'quantity' => 20,
            'unit_price' => 25.00,
            'total_price' => 500.00,
        ]);

        // Create Delivery Order for 20 units
        $response = $this->post(route('admin.delivery-orders.store'), [
            'order_id' => $order->id,
            'carrier_name' => 'DHL Express',
            'shipping_method' => 'Express Courier',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'qty' => 20,
                ]
            ]
        ]);

        $deliveryOrder = DeliveryOrder::where('order_id', $order->id)->first();
        $this->assertNotNull($deliveryOrder);
        $this->assertEquals('pending', $deliveryOrder->status);

        // Dispatch Delivery Order
        $dispatchResponse = $this->post(route('admin.delivery-orders.dispatch', $deliveryOrder->id));

        $this->assertEquals('dispatched', $deliveryOrder->fresh()->status);
        $this->assertEquals(80, (float) $stock->fresh()->quantity);

        $this->assertDatabaseHas('stock_ledgers', [
            'product_id' => $product->id,
            'reference_type' => 'DeliveryOrder',
            'reference_id' => $deliveryOrder->id,
            'out_qty' => 20,
            'balance_qty' => 80,
        ]);

        $this->assertEquals('fully_delivered', $order->fresh()->fulfillment_status);
    }
}
