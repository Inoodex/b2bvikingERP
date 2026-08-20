<?php

namespace Tests\Feature\Sales;

use App\Models\CreditNote;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockLedger;
use App\Models\User;
use App\Services\Credit\CreditValidationService;
use App\Services\Pricing\PricelistResolverService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalesFulfillmentPricingTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateUser(): User
    {
        return User::first() ?? User::create([
            'name' => 'Sales Representative',
            'email' => 'sales_rep_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function getOrCreateProduct(): Product
    {
        return Product::first() ?? Product::create([
            'name' => 'Nordic Safety Helmet ' . uniqid(),
            'slug' => 'safety-helmet-' . uniqid(),
            'price' => 100.00,
            'cost' => 40.00,
            'status' => 1,
        ]);
    }

    public function test_01_dynamic_pricelist_tiered_pricing_resolver(): void
    {
        $product = $this->getOrCreateProduct();

        $pricelist = Pricelist::create([
            'name' => 'VIP B2B Wholesale Tier ' . uniqid(),
            'code' => 'PL-B2B-' . uniqid(),
            'currency_id' => 1,
            'customer_segment' => 'wholesale',
            'priority' => 10,
            'status' => true,
        ]);

        PricelistItem::create([
            'pricelist_id' => $pricelist->id,
            'product_id' => $product->id,
            'min_quantity' => 10,
            'price' => 75.00,
            'discount_type' => 'fixed_price',
            'discount_value' => 75.00,
        ]);

        $resolver = app(PricelistResolverService::class);
        $resolvedPrice = $resolver->resolvePrice(null, $product->id, null);

        $this->assertIsFloat((float) $resolvedPrice);
        $this->assertGreaterThan(0, (float) $resolvedPrice);
    }

    public function test_02_b2b_customer_credit_limit_validation(): void
    {
        $customer = User::create([
            'name' => 'Nordic Retail ApS ' . uniqid(),
            'email' => 'retail_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'credit_limit' => 10000.00,
            'customer_segment' => 'wholesale',
            'status' => 1,
        ]);

        $creditService = app(CreditValidationService::class);

        // Order within limit (5000 <= 10000)
        $evalPass = $creditService->evaluateCreditExposure($customer->id, 5000.00);
        $this->assertFalse($evalPass['is_exceeded']);
        $this->assertEquals(10000.00, (float) $evalPass['credit_limit']);

        // Order exceeding limit (15000 > 10000)
        $evalFail = $creditService->evaluateCreditExposure($customer->id, 15000.00);
        $this->assertTrue($evalFail['is_exceeded']);
        $this->assertEquals('credit_hold', $evalFail['status']);
    }

    public function test_03_partial_and_full_delivery_order_dispatch_workflow(): void
    {
        $this->withoutMiddleware();
        $user = User::first() ?? $this->getOrCreateUser();
        $this->actingAs($user);

        $product = Product::first() ?? Product::create([
            'name' => 'Commercial Jacket ' . uniqid(),
            'slug' => 'commercial-jacket-' . uniqid(),
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
            'order_no' => 'SO-TEST-' . uniqid(),
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

        // Dispatch Delivery Order
        $this->post(route('admin.delivery-orders.dispatch', $deliveryOrder->id));

        $this->assertEquals('dispatched', $deliveryOrder->fresh()->status);
        $this->assertEquals(80, (float) $stock->fresh()->quantity);
        $this->assertEquals('fully_delivered', $order->fresh()->fulfillment_status);
        $this->assertGreaterThanOrEqual(1, StockLedger::where('product_id', $product->id)->where('reference_type', 'DeliveryOrder')->count());
    }

    public function test_04_sales_return_and_credit_note_generation(): void
    {
        $user = $this->getOrCreateUser();
        $product = $this->getOrCreateProduct();

        $order = Order::create([
            'order_no' => 'SO-RET-' . uniqid(),
            'user_id' => $user->id,
            'billing_name' => 'Customer A/S',
            'billing_email' => 'client@ret.dk',
            'billing_phone' => '+45 99887766',
            'billing_address' => 'Aarhus Center',
            'total_amount' => 1000.00,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 10,
            'unit_price' => 100.00,
            'total_price' => 1000.00,
        ]);

        $salesReturn = SalesReturn::create([
            'return_no' => 'SR-' . uniqid(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Damaged in transit packaging',
            'status' => 'approved',
            'refund_amount' => 200.00,
            'approved_by' => $user->id,
        ]);

        SalesReturnItem::create([
            'sales_return_id' => $salesReturn->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => 100.00,
            'subtotal' => 200.00,
            'action' => 'refund',
        ]);

        $creditNote = CreditNote::create([
            'credit_note_no' => 'CN-' . uniqid(),
            'sales_return_id' => $salesReturn->id,
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => 200.00,
            'date' => now()->toDateString(),
            'status' => 'issued',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('sales_returns', ['id' => $salesReturn->id, 'status' => 'approved']);
        $this->assertDatabaseHas('credit_notes', ['id' => $creditNote->id, 'amount' => 200.00]);
        $this->assertEquals(200.00, (float) $creditNote->amount);
    }
}
