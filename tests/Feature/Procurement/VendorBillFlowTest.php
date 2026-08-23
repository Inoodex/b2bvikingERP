<?php

namespace Tests\Feature\Procurement;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorBillFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Vendor $vendor;
    protected Purchase $purchase;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Admin');
        $this->admin = User::factory()->create([
            'email' => 'admin_vbill_' . uniqid() . '@example.com',
        ]);
        $this->admin->assignRole('Admin');

        $this->vendor = Vendor::first() ?? Vendor::create([
            'shop_name' => 'Supplier ' . uniqid(),
            'user_id' => $this->admin->id,
            'email' => 'vendor_' . uniqid() . '@example.com',
            'phone' => '12345678',
            'address' => 'Factory Lane 1',
            'country' => 'Denmark',
            'city' => 'Copenhagen',
            'description' => 'Test Vendor',
            'status' => 1,
        ]);

        $this->product = Product::create([
            'name' => 'Raw Material ' . uniqid(),
            'slug' => 'raw-mat-' . uniqid(),
            'sku' => 'SKU-' . uniqid(),
            'price' => 50,
            'offer_price' => 50,
            'stock_quantity' => 100,
            'status' => 1,
            'is_approved' => 1,
        ]);

        $this->purchase = Purchase::create([
            'po_no' => 'PO-' . uniqid(),
            'invoice_no' => 'PINV-' . uniqid(),
            'date' => date('Y-m-d'),
            'vendor_id' => $this->vendor->id,
            'total_amount' => 500,
            'approval_status' => 'approved',
            'purchase_type' => 'local',
            'created_by' => $this->admin->id,
        ]);

        PurchaseDetail::create([
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'qty' => 10,
            'unit_cost' => 50,
            'total' => 500,
        ]);
    }

    public function test_vendor_bill_index_and_create_endpoints_load_cleanly(): void
    {
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.vendor-bills.index'));
        $indexResponse->assertStatus(200);

        $createBlankResponse = $this->actingAs($this->admin)->get(route('admin.vendor-bills.create'));
        $createBlankResponse->assertStatus(200);

        $createPoResponse = $this->actingAs($this->admin)->get(route('admin.vendor-bills.create', [
            'purchase_id' => $this->purchase->id,
        ]));
        $createPoResponse->assertStatus(200);
        $createPoResponse->assertViewHas('purchase');
    }

    public function test_vendor_bill_store_and_show_workflow(): void
    {
        $postData = [
            'purchase_id' => $this->purchase->id,
            'bill_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'notes' => 'Test Vendor Bill Note',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 10,
                    'unit_price' => 50,
                    'landed_cost' => 50,
                ]
            ]
        ];

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.vendor-bills.store'), $postData);
        $storeResponse->assertRedirect();

        $bill = VendorBill::where('purchase_id', $this->purchase->id)->latest()->first();
        $this->assertNotNull($bill);
        $this->assertEquals(500, (float)$bill->grand_total);
        $this->assertCount(1, $bill->items);

        $showResponse = $this->actingAs($this->admin)->get(route('admin.vendor-bills.show', $bill->id));
        $showResponse->assertStatus(200);
    }
}
