<?php

namespace Tests\Feature\Procurement;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\LcExpense;
use App\Models\LetterOfCredit;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\StockLedger;
use App\Models\User;
use App\Models\Vendor;
use App\Services\LandedCostService;
use App\Services\StockReceiveService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProcurementLandedCostTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateVendor(): Vendor
    {
        return Vendor::first() ?? Vendor::create([
            'name' => 'Nordic Landed Cost Supplier',
            'shop_name' => 'Nordic Supplier Shop',
            'email' => 'landed_' . uniqid() . '@example.com',
            'phone' => '+45 11223344',
            'country' => 'Denmark',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);
    }

    protected function getOrCreateOutlet(): Outlet
    {
        $company = \App\Models\Company::first() ?? \App\Models\Company::create([
            'name' => 'Copenhagen Holdings A/S',
            'code' => 'CPH-' . uniqid(),
            'email' => 'holdings@cph.dk',
            'phone' => '+45 11223344',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);

        return Outlet::first() ?? Outlet::create([
            'name' => 'Main Outlet ' . uniqid(),
            'code' => 'OUT-' . uniqid(),
            'company_id' => $company->id,
            'status' => 1,
        ]);
    }

    protected function getOrCreateProduct(string $name = 'Product'): Product
    {
        return Product::create([
            'name' => $name . ' ' . uniqid(),
            'slug' => strtolower($name) . '-' . uniqid(),
            'price' => 100.00,
            'cost' => 50.00,
            'status' => 1,
        ]);
    }

    public function test_landed_cost_service_allocates_overheads_proportionally(): void
    {
        $vendor = $this->getOrCreateVendor();
        $outlet = $this->getOrCreateOutlet();
        $productA = $this->getOrCreateProduct('Product A');
        $productB = $this->getOrCreateProduct('Product B');

        $purchase = Purchase::create([
            'po_no' => 'PO-TEST-001',
            'invoice_no' => 'INV-TEST-001',
            'vendor_id' => $vendor->id,
            'company_id' => 1,
            'purchase_type' => 'foreign',
            'date' => now()->toDateString(),
            'total_amount' => 3000.00,
            'foreign_amount' => 3000.00,
            'exchange_rate' => 1.0,
            'approval_status' => 'approved',
            'milestone_status' => 'po_sent',
            'outlet_id' => $outlet->id,
            'grand_total' => 3000.00,
        ]);

        // Item 1: 100 qty @ 10 = 1000 (1/3 of total value)
        $detail1 = PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id' => $productA->id,
            'qty' => 100,
            'unit_cost' => 10.00,
            'subtotal' => 1000.00,
            'total' => 1000.00,
        ]);

        // Item 2: 100 qty @ 20 = 2000 (2/3 of total value)
        $detail2 = PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id' => $productB->id,
            'qty' => 100,
            'unit_cost' => 20.00,
            'subtotal' => 2000.00,
            'total' => 2000.00,
        ]);

        // LC with 600 DKK overhead
        $lc = LetterOfCredit::create([
            'lc_no' => 'LC-TEST-' . uniqid(),
            'vendor_id' => $vendor->id,
            'amount' => 3000.00,
            'issuing_bank' => 'Danske Bank',
            'issue_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(90)->toDateString(),
            'status' => 'open',
        ]);

        $purchase->update(['lc_id' => $lc->id]);

        LcExpense::create([
            'lc_id' => $lc->id,
            'cost_element' => 'Freight',
            'amount' => 600.00,
            'goes_to_unit_cost' => 1,
        ]);

        $service = app(LandedCostService::class);
        $matrix = $service->calculateLandedCosts($purchase);

        // Verification:
        // Item 1 gets 1/3 of 600 = 200 DKK overhead -> Unit Landed Cost = (1000 + 200) / 100 = 12.00 DKK
        // Item 2 gets 2/3 of 600 = 400 DKK overhead -> Unit Landed Cost = (2000 + 400) / 100 = 24.00 DKK
        $this->assertEquals(12.00, (float) $detail1->fresh()->landed_cost);
        $this->assertEquals(24.00, (float) $detail2->fresh()->landed_cost);
    }

    public function test_stock_receive_service_increments_inventory_and_logs_stock_ledger(): void
    {
        $vendor = $this->getOrCreateVendor();
        $outlet = $this->getOrCreateOutlet();
        $product = $this->getOrCreateProduct('Product GRN');

        $purchase = Purchase::create([
            'po_no' => 'PO-TEST-002',
            'invoice_no' => 'INV-TEST-002',
            'vendor_id' => $vendor->id,
            'company_id' => 1,
            'purchase_type' => 'local',
            'date' => now()->toDateString(),
            'total_amount' => 500.00,
            'grand_total' => 500.00,
            'outlet_id' => $outlet->id,
            'approval_status' => 'approved',
            'milestone_status' => 'po_sent',
        ]);

        PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'qty' => 50,
            'unit_cost' => 10.00,
            'subtotal' => 500.00,
            'total' => 500.00,
            'landed_cost' => 10.00,
        ]);

        $user = User::first() ?? User::create([
            'name' => 'GRN Officer',
            'email' => 'grn_officer_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Create Goods Receipt for 50 units
        $goodsReceipt = GoodsReceipt::create([
            'grn_no' => 'GRN-TEST-' . uniqid(),
            'purchase_id' => $purchase->id,
            'outlet_id' => $outlet->id,
            'received_by' => $user->id,
            'qc_status' => 'passed',
            'receipt_date' => now()->toDateString(),
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $goodsReceipt->id,
            'product_id' => $product->id,
            'received_qty' => 50,
            'accepted_qty' => 50,
            'rejected_qty' => 0,
        ]);

        $initialStock = (float) (InventoryStock::where('outlet_id', $outlet->id)
            ->where('product_id', $product->id)
            ->value('quantity') ?? 0);

        $receiveService = app(StockReceiveService::class);
        $receiveService->processStockReceive($goodsReceipt);

        $updatedStock = (float) InventoryStock::where('outlet_id', $outlet->id)
            ->where('product_id', $product->id)
            ->value('quantity');

        $this->assertEquals($initialStock + 50, $updatedStock);

        $this->assertDatabaseHas('stock_ledgers', [
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'reference_type' => 'goods_receipt',
            'reference_id' => $goodsReceipt->id,
            'in_qty' => 50,
        ]);

        $this->assertEquals('goods_received', $purchase->fresh()->milestone_status);
    }
}
