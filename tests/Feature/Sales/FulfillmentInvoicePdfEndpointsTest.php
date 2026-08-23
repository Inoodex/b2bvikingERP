<?php

namespace Tests\Feature\Sales;

use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Models\SalesInvoice;
use App\Models\CustomerPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FulfillmentInvoicePdfEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Admin');
        $this->admin = User::factory()->create([
            'email' => 'admin_test_' . uniqid() . '@example.com',
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_sales_invoice_create_with_delivery_order_id_renders_cleanly(): void
    {
        $order = Order::create([
            'order_no' => 'ORD-' . uniqid(),
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'billing_name' => 'Test Customer',
            'billing_email' => 'test@example.com',
            'billing_phone' => '12345678',
            'billing_address' => 'Test Street 1',
            'subtotal_amount' => 100,
            'tax_amount' => 10,
            'discount_amount' => 0,
            'total_amount' => 110,
            'paid_amount' => 0,
            'due_amount' => 110,
            'payment_status' => 'pending',
        ]);
        $do = DeliveryOrder::create([
            'delivery_no' => 'DO-' . uniqid(),
            'order_id' => $order->id,
            'status' => 'dispatched',
            'date' => date('Y-m-d'),
            'carrier_name' => 'Local Truck',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.sales-invoices.create', [
            'delivery_order_id' => $do->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('backend.sales_invoices.create');
        $response->assertViewHas('selectedDeliveryOrderId', (string)$do->id);
    }

    public function test_delivery_order_pdf_download_endpoint_returns_ok(): void
    {
        $order = Order::create([
            'order_no' => 'ORD-' . uniqid(),
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'billing_name' => 'Test Customer',
            'billing_email' => 'test@example.com',
            'billing_phone' => '12345678',
            'billing_address' => 'Test Street 1',
            'subtotal_amount' => 100,
            'tax_amount' => 10,
            'discount_amount' => 0,
            'total_amount' => 110,
            'paid_amount' => 0,
            'due_amount' => 110,
            'payment_status' => 'pending',
        ]);
        $do = DeliveryOrder::create([
            'delivery_no' => 'DO-' . uniqid(),
            'order_id' => $order->id,
            'status' => 'dispatched',
            'date' => date('Y-m-d'),
            'carrier_name' => 'Local Truck',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.delivery-orders.pdf', $do->id));
        $response->assertStatus(200);
    }

    public function test_sales_invoice_store_show_and_pdf_download(): void
    {
        $order = Order::create([
            'order_no' => 'ORD-' . uniqid(),
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'billing_name' => 'Test Customer',
            'billing_email' => 'test@example.com',
            'billing_phone' => '12345678',
            'billing_address' => 'Test Street 1',
            'subtotal_amount' => 500,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500,
            'paid_amount' => 0,
            'due_amount' => 500,
            'payment_status' => 'pending',
        ]);
        $product = \App\Models\Product::first();
        if (!$product) {
            $product = \App\Models\Product::create([
                'name' => 'Test Product',
                'slug' => 'test-product-' . uniqid(),
                'sku' => 'SKU-' . uniqid(),
                'price' => 100,
                'offer_price' => 100,
                'stock_quantity' => 100,
                'status' => 1,
                'is_approved' => 1,
            ]);
        }

        $postData = [
            'order_id' => $order->id,
            'date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'discount_amount' => 0,
            'tax_rate' => 0,
            'status' => 'draft',
            'notes' => 'Commercial Sales Invoice Test Note',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 5,
                    'price' => 100,
                ]
            ]
        ];

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.sales-invoices.store'), $postData);
        $storeResponse->assertRedirect();

        $invoice = SalesInvoice::where('order_id', $order->id)->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(500, (float)$invoice->total_amount);
        $this->assertCount(1, $invoice->items);

        $showResponse = $this->actingAs($this->admin)->get(route('admin.sales-invoices.show', $invoice->id));
        $showResponse->assertStatus(200);

        $pdfResponse = $this->actingAs($this->admin)->get(route('admin.sales-invoices.pdf', $invoice->id));
        $pdfResponse->assertStatus(200);
    }
}
