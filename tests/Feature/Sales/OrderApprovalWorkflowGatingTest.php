<?php

namespace Tests\Feature\Sales;

use App\Models\Approval;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;
use App\Models\DeliveryOrder;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderApprovalWorkflowGatingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->user = User::first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role_id' => 1,
        ]);
        $this->actingAs($this->user);

        $this->product = Product::first() ?? Product::create([
            'name' => 'Test Product ' . uniqid(),
            'slug' => 'test-product-' . uniqid(),
            'price' => 50.00,
            'cost' => 20.00,
            'status' => 1,
        ]);

        InventoryStock::firstOrCreate(
            ['product_id' => $this->product->id, 'variant_id' => null],
            ['quantity' => 100]
        );
    }

    public function test_cannot_create_delivery_or_invoice_when_order_pending_approval(): void
    {
        // 1. Create an order that is pending
        $order = Order::create([
            'order_no' => 'SO-PENDING-' . uniqid(),
            'user_id' => $this->user->id,
            'billing_name' => 'Customer',
            'billing_email' => 'customer@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => 'Customer Address',
            'total_amount' => 100.00,
            'status' => 'pending',
            'approval_status' => 'pending',
        ]);

        // Attach pending approval step
        $order->approvals()->create([
            'step_number' => 1,
            'role_id' => 1,
            'status' => 'pending',
        ]);

        $this->assertFalse($order->isFullyApproved());

        // Delivery Order Create should redirect to orders.show
        $responseDO = $this->get(route('admin.delivery-orders.create', ['order_id' => $order->id]));
        $responseDO->assertRedirect(route('admin.orders.show', $order->id));

        // Delivery Order Store should redirect to orders.show
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name ?? 'Sample Product',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
        ]);

        $responseDOStore = $this->post(route('admin.delivery-orders.store'), [
            'order_id' => $order->id,
            'items' => [
                ['order_item_id' => $orderItem->id, 'qty' => 2]
            ]
        ]);
        $responseDOStore->assertRedirect(route('admin.orders.show', $order->id));

        // Sales Invoice Create should redirect to orders.show
        $responseInv = $this->get(route('admin.sales-invoices.create', ['order_id' => $order->id]));
        $responseInv->assertRedirect(route('admin.orders.show', $order->id));

        // Sales Invoice Store should redirect to orders.show
        $responseInvStore = $this->post(route('admin.sales-invoices.store'), [
            'order_id' => $order->id,
            'date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 2, 'price' => 50.00]
            ]
        ]);
        $responseInvStore->assertRedirect(route('admin.orders.show', $order->id));
    }

    public function test_cannot_create_duplicate_commercial_invoice_for_same_order(): void
    {
        $order = Order::create([
            'order_no' => 'SO-DUP-' . uniqid(),
            'user_id' => $this->user->id,
            'billing_name' => 'Customer',
            'billing_email' => 'customer@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => 'Customer Address',
            'total_amount' => 100.00,
            'status' => 'approved',
            'approval_status' => 'approved',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name ?? 'Sample Product',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
        ]);

        // First invoice creation
        $storeResponse = $this->post(route('admin.sales-invoices.store'), [
            'order_id' => $order->id,
            'date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 2, 'price' => 50.00]
            ]
        ]);

        $invoice = SalesInvoice::where('order_id', $order->id)->first();
        $this->assertNotNull($invoice);
        $storeResponse->assertRedirect(route('admin.sales-invoices.show', $invoice->id));

        // Duplicate Invoice Create page should redirect to existing invoice
        $dupCreateResponse = $this->get(route('admin.sales-invoices.create', ['order_id' => $order->id]));
        $dupCreateResponse->assertRedirect(route('admin.sales-invoices.show', $invoice->id));

        // Duplicate Invoice Store POST should block and redirect to existing invoice
        $dupStoreResponse = $this->post(route('admin.sales-invoices.store'), [
            'order_id' => $order->id,
            'date' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 2, 'price' => 50.00]
            ]
        ]);
        $dupStoreResponse->assertRedirect(route('admin.sales-invoices.show', $invoice->id));

        // Verify only 1 invoice exists for this order
        $this->assertEquals(1, SalesInvoice::where('order_id', $order->id)->count());
    }

    public function test_cannot_create_delivery_order_when_order_is_fully_delivered(): void
    {
        $order = Order::create([
            'order_no' => 'SO-DELIV-' . uniqid(),
            'user_id' => $this->user->id,
            'billing_name' => 'Customer',
            'billing_email' => 'customer@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => 'Customer Address',
            'total_amount' => 100.00,
            'status' => 'approved',
            'approval_status' => 'approved',
            'fulfillment_status' => 'fully_delivered',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name ?? 'Sample Product',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
        ]);

        // Attempting to visit create DO should redirect to delivery-orders index
        $createResponse = $this->get(route('admin.delivery-orders.create', ['order_id' => $order->id]));
        $createResponse->assertRedirect(route('admin.delivery-orders.index', ['order_id' => $order->id]));

        // Attempting to post DO should redirect to delivery-orders index
        $storeResponse = $this->post(route('admin.delivery-orders.store'), [
            'order_id' => $order->id,
            'items' => [
                ['order_item_id' => $orderItem->id, 'qty' => 2]
            ]
        ]);
        $storeResponse->assertRedirect(route('admin.delivery-orders.index', ['order_id' => $order->id]));
    }

    public function test_order_datatable_action_button_enterprise_behavior(): void
    {
        $dataTable = new \App\DataTables\OrderDataTable();

        // 1. Pending order: no delivery order button
        $pendingOrder = Order::create([
            'order_no' => 'DT-PENDING-' . uniqid(),
            'user_id' => $this->user->id,
            'billing_name' => 'Customer',
            'billing_email' => 'customer@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => 'Customer Address',
            'total_amount' => 100.00,
            'status' => 'pending',
            'approval_status' => 'pending',
        ]);
        $pendingOrder->approvals()->create([
            'step_number' => 1,
            'role_id' => 1,
            'status' => 'pending',
        ]);

        $dataTableResponse = $this->getJson(route('admin.orders.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 25,
        ]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $dataTableResponse->assertOk();

        // 2. Completed order with NO delivery orders: should NOT have Create Delivery Challan
        $completedOrderNoDo = Order::create([
            'order_no' => 'DT-COMP-NODO-' . uniqid(),
            'user_id' => $this->user->id,
            'billing_name' => 'Customer',
            'billing_email' => 'customer@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => 'Customer Address',
            'total_amount' => 100.00,
            'status' => 'completed',
            'approval_status' => 'approved',
        ]);

        // 3. Completed order WITH delivery order: should have View Delivery Challan, NOT Create
        $completedOrderWithDo = Order::create([
            'order_no' => 'DT-COMP-WITHDO-' . uniqid(),
            'user_id' => $this->user->id,
            'billing_name' => 'Customer',
            'billing_email' => 'customer@example.com',
            'billing_phone' => '1234567890',
            'billing_address' => 'Customer Address',
            'total_amount' => 100.00,
            'status' => 'completed',
            'approval_status' => 'approved',
            'fulfillment_status' => 'fully_delivered',
        ]);
        $do = DeliveryOrder::create([
            'delivery_no' => 'DO-TEST-' . uniqid(),
            'order_id' => $completedOrderWithDo->id,
            'date' => now()->toDateString(),
            'status' => 'dispatched',
            'created_by' => $this->user->id,
        ]);

        // Test the datatable output
        $response = $this->getJson(route('admin.orders.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 100,
        ]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $response->assertOk();

        $data = $response->json('data') ?? [];
        $rowsByOrderNo = collect($data)->keyBy(function($item) {
            // Extract order_no or check raw text
            return strip_tags($item['order_no'] ?? '');
        });

        // Verify completed order with DO has View Delivery Challan
        $rowWithDo = collect($data)->first(function($item) use ($completedOrderWithDo) {
            return str_contains($item['action'] ?? '', route('admin.orders.show', $completedOrderWithDo->id));
        });
        $this->assertNotNull($rowWithDo);
        $this->assertStringContainsString('View Delivery Challan', $rowWithDo['action']);
        $this->assertStringNotContainsString('Create Delivery Challan', $rowWithDo['action']);

        // Verify completed order without DO has neither
        $rowNoDo = collect($data)->first(function($item) use ($completedOrderNoDo) {
            return str_contains($item['action'] ?? '', route('admin.orders.show', $completedOrderNoDo->id));
        });
        $this->assertNotNull($rowNoDo);
        $this->assertStringNotContainsString('Create Delivery Challan', $rowNoDo['action']);
        $this->assertStringNotContainsString('View Delivery Challan', $rowNoDo['action']);
    }
}
