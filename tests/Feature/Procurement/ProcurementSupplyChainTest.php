<?php

namespace Tests\Feature\Procurement;

use App\Models\ComparisonStatement;
use App\Models\Currency;
use App\Models\Department;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\LetterOfCredit;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequestItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\ShipmentTracking;
use App\Models\StockLedger;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationItem;
use App\Services\StockReceiveService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProcurementSupplyChainTest extends TestCase
{
    use DatabaseTransactions;

    protected function getOrCreateUser(): User
    {
        return User::first() ?? User::create([
            'name' => 'Procurement Officer',
            'email' => 'proc_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function getOrCreateDepartment(User $user): Department
    {
        return Department::first() ?? Department::create([
            'name' => 'Procurement Dept ' . uniqid(),
            'code' => 'PRC-' . uniqid(),
            'manager_id' => $user->id,
            'status' => 1,
        ]);
    }

    protected function getOrCreateVendor(string $name = 'Vendor'): Vendor
    {
        return Vendor::create([
            'name' => $name . ' ' . uniqid(),
            'shop_name' => $name . ' Shop',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'phone' => '+45 22334455',
            'country' => 'Denmark',
            'address' => 'Aarhus',
            'status' => 1,
        ]);
    }

    protected function getOrCreateProduct(): Product
    {
        return Product::first() ?? Product::create([
            'name' => 'Heavy Duty Winter Parka ' . uniqid(),
            'slug' => 'winter-parka-' . uniqid(),
            'price' => 250.00,
            'cost' => 100.00,
            'status' => 1,
        ]);
    }

    public function test_01_create_purchase_requisition_and_generate_rfq(): void
    {
        $user = $this->getOrCreateUser();
        $dept = $this->getOrCreateDepartment($user);
        $product = $this->getOrCreateProduct();

        $pr = ProductRequest::create([
            'request_no' => 'PR-' . uniqid(),
            'department_id' => $dept->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        ProductRequestItem::create([
            'product_request_id' => $pr->id,
            'product_id' => $product->id,
            'qty' => 150,
        ]);

        $rfq = Rfq::create([
            'rfq_no' => 'RFQ-' . uniqid(),
            'source_type' => ProductRequest::class,
            'source_id' => $pr->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'open',
        ]);

        RfqItem::create([
            'rfq_id' => $rfq->id,
            'product_id' => $product->id,
            'qty' => 150,
            'target_price' => 95.00,
        ]);

        $this->assertDatabaseHas('rfqs', ['id' => $rfq->id, 'source_id' => $pr->id]);
        $this->assertEquals(150, (float) $pr->items()->first()->qty);
    }

    public function test_02_vendor_quotations_and_comparison_statement_matrix(): void
    {
        $user = $this->getOrCreateUser();
        $dept = $this->getOrCreateDepartment($user);
        $product = $this->getOrCreateProduct();

        $pr = ProductRequest::create([
            'request_no' => 'PR-' . uniqid(),
            'department_id' => $dept->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $rfq = Rfq::create([
            'rfq_no' => 'RFQ-COMP-' . uniqid(),
            'source_type' => ProductRequest::class,
            'source_id' => $pr->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'open',
        ]);

        $vendorA = $this->getOrCreateVendor('Nordic Supplies');
        $vendorB = $this->getOrCreateVendor('Baltic Global Trade');

        $quoteA = VendorQuotation::create([
            'quotation_no' => 'VQ-A-' . uniqid(),
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendorA->id,
            'validity_date' => now()->addDays(14)->toDateString(),
            'status' => 'received',
        ]);
        VendorQuotationItem::create([
            'vendor_quotation_id' => $quoteA->id,
            'product_id' => $product->id,
            'qty' => 150,
            'unit_price' => 95.00,
            'total_price' => 14250.00,
        ]);

        $quoteB = VendorQuotation::create([
            'quotation_no' => 'VQ-B-' . uniqid(),
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendorB->id,
            'validity_date' => now()->addDays(14)->toDateString(),
            'status' => 'received',
        ]);
        VendorQuotationItem::create([
            'vendor_quotation_id' => $quoteB->id,
            'product_id' => $product->id,
            'qty' => 150,
            'unit_price' => 90.00,
            'total_price' => 13500.00,
        ]);

        $cs = ComparisonStatement::create([
            'cs_no' => 'CS-' . uniqid(),
            'rfq_id' => $rfq->id,
            'recommended_vendor_id' => $vendorB->id,
            'approval_status' => 'approved',
        ]);

        $this->assertEquals($vendorB->id, $cs->recommended_vendor_id);
        $this->assertEquals(90.00, (float) $quoteB->items()->first()->unit_price);
    }

    public function test_03_foreign_po_lc_register_and_shipment_tracking(): void
    {
        $vendor = $this->getOrCreateVendor('Global Port Supplier');
        $currency = Currency::where('code', 'USD')->first() ?? Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 6.95,
            'is_base' => 0,
            'status' => 1,
        ]);

        $lc = LetterOfCredit::create([
            'lc_no' => 'LC-' . uniqid(),
            'vendor_id' => $vendor->id,
            'issuing_bank' => 'Danske Bank A/S',
            'amount' => 50000.00,
            'issue_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'status' => 'open',
        ]);

        $po = Purchase::create([
            'po_no' => 'PO-IMP-' . uniqid(),
            'invoice_no' => 'INV-IMP-' . uniqid(),
            'vendor_id' => $vendor->id,
            'outlet_id' => 1,
            'purchase_type' => 'foreign',
            'date' => now()->toDateString(),
            'currency_id' => $currency->id,
            'foreign_amount' => 10000.00,
            'exchange_rate_used' => 6.95,
            'total_amount' => 69500.00,
            'grand_total' => 69500.00,
            'lc_id' => $lc->id,
            'milestone_status' => 'lc_opened',
        ]);

        $shipment = \App\Models\Shipment::create([
            'purchase_id' => $po->id,
            'vessel_or_flight' => 'Emma Maersk',
            'container_no' => 'MSKU-9876543',
            'bl_awb_no' => 'BL-998877',
            'port_of_loading' => 'Shanghai Port',
            'port_of_discharge' => 'Port of Copenhagen',
            'status' => 'in_transit',
        ]);

        $this->assertDatabaseHas('purchases', ['id' => $po->id, 'lc_id' => $lc->id, 'purchase_type' => 'foreign']);
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'purchase_id' => $po->id]);
    }

    public function test_04_goods_receipt_qc_and_stock_ledger_integration(): void
    {
        $user = $this->getOrCreateUser();
        $vendor = $this->getOrCreateVendor('Copenhagen Textile Works');
        $product = $this->getOrCreateProduct();

        $company = \App\Models\Company::first() ?? \App\Models\Company::create([
            'name' => 'Copenhagen Holdings A/S',
            'code' => 'CPH-' . uniqid(),
            'email' => 'holdings@cph.dk',
            'phone' => '+45 11223344',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);

        $outlet = Outlet::first() ?? Outlet::create([
            'name' => 'Main Receiving Warehouse ' . uniqid(),
            'code' => 'MRW-' . uniqid(),
            'company_id' => $company->id,
            'status' => 1,
        ]);

        $po = Purchase::create([
            'po_no' => 'PO-GRN-' . uniqid(),
            'invoice_no' => 'INV-GRN-' . uniqid(),
            'vendor_id' => $vendor->id,
            'outlet_id' => $outlet->id,
            'purchase_type' => 'local',
            'date' => now()->toDateString(),
            'total_amount' => 2000.00,
            'grand_total' => 2000.00,
            'milestone_status' => 'po_sent',
        ]);

        PurchaseDetail::create([
            'purchase_id' => $po->id,
            'product_id' => $product->id,
            'qty' => 100,
            'unit_cost' => 20.00,
            'total' => 2000.00,
            'landed_cost' => 20.00,
        ]);

        $grn = GoodsReceipt::create([
            'grn_no' => 'GRN-QC-' . uniqid(),
            'purchase_id' => $po->id,
            'outlet_id' => $outlet->id,
            'received_by' => $user->id,
            'qc_status' => 'passed',
            'receipt_date' => now()->toDateString(),
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $grn->id,
            'product_id' => $product->id,
            'received_qty' => 100,
            'accepted_qty' => 100,
            'rejected_qty' => 0,
        ]);

        $service = app(StockReceiveService::class);
        $service->processStockReceive($grn);

        $stock = InventoryStock::where('outlet_id', $outlet->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($stock);
        $this->assertGreaterThanOrEqual(100, (float) $stock->quantity);

        $this->assertDatabaseHas('stock_ledgers', [
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'reference_type' => 'goods_receipt',
            'reference_id' => $grn->id,
            'in_qty' => 100,
        ]);
        $this->assertEquals('goods_received', $po->fresh()->milestone_status);
    }
}
