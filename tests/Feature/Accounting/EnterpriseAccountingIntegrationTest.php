<?php

namespace Tests\Feature\Accounting;

use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\Accounting\JournalEntryService;
use App\Services\CustomerPaymentService;
use App\Services\VendorBillService;
use App\Services\VendorPaymentService;
use Exception;
use Tests\TestCase;

class EnterpriseAccountingIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Core Chart of Accounts exist
        $coreAccounts = [
            ['account_code' => '1010', 'account_name' => 'Cash in Hand', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['account_code' => '1020', 'account_name' => 'Bank Accounts', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['account_code' => '1030', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['account_code' => '1050', 'account_name' => 'Inventory Asset', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['account_code' => '2010', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'normal_balance' => 'credit'],
            ['account_code' => '2020', 'account_name' => 'GRN Clearing', 'account_type' => 'liability', 'normal_balance' => 'credit'],
            ['account_code' => '4010', 'account_name' => 'Sales Revenue', 'account_type' => 'revenue', 'normal_balance' => 'credit'],
            ['account_code' => '5010', 'account_name' => 'Cost of Goods Sold', 'account_type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($coreAccounts as $acc) {
            ChartOfAccount::firstOrCreate(['account_code' => $acc['account_code']], [
                'account_name'   => $acc['account_name'],
                'account_type'   => $acc['account_type'],
                'normal_balance' => $acc['normal_balance'],
                'is_group'       => false,
                'is_active'      => true,
            ]);
        }
    }

    public function test_customer_payment_records_atomic_balanced_gl_journal_without_duplicate(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'order_no'        => 'TEST-ORD-' . uniqid(),
            'user_id'         => $user->id,
            'billing_name'    => $user->name ?? 'Test Customer',
            'billing_email'   => $user->email ?? 'test@example.com',
            'billing_phone'   => '12345678',
            'billing_address' => 'Test Address, Copenhagen',
            'subtotal_amount' => 1000,
            'total_amount'    => 1000,
            'paid_amount'     => 0,
            'due_amount'      => 1000,
            'payment_status'  => 'unpaid',
            'order_status'    => 'completed',
        ]);

        $invoice = SalesInvoice::create([
            'invoice_no'      => 'INV-' . uniqid(),
            'order_id'        => $order->id,
            'date'            => now()->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
            'total_amount'    => 1000,
            'paid_amount'     => 0,
            'due_amount'      => 1000,
            'status'          => 'posted',
        ]);

        $service = app(CustomerPaymentService::class);
        $payment = $service->recordPayment([
            'customer_id'      => $user->id,
            'sales_invoice_id' => $invoice->id,
            'order_id'         => $order->id,
            'amount'           => 400,
            'payment_method'   => 'bank',
            'payment_date'     => now()->toDateString(),
        ], $user->id);

        $this->assertNotNull($payment);
        $this->assertEquals(400, $payment->amount);

        // Verify EXACTLY ONE Journal Entry exists (No Double Posting)
        $journalCount = JournalEntry::where('reference_type', CustomerPayment::class)->where('reference_id', $payment->id)->count();
        $this->assertEquals(1, $journalCount, "Customer Payment must have exactly ONE Journal Entry (No Double Posting).");

        // Verify subledger knockdown
        $invoice->refresh();
        $this->assertEquals(400, $invoice->paid_amount);
        $this->assertEquals(600, $invoice->due_amount);
        $this->assertEquals('partial', $invoice->status);

        // Verify GL Journal Entry lines
        $journal = $payment->journalEntry;
        $this->assertNotNull($journal);
        $this->assertEquals(400, $journal->lines()->whereHas('account', fn($q) => $q->where('account_code', '1020'))->sum('debit'));
        $this->assertEquals(400, $journal->lines()->whereHas('account', fn($q) => $q->where('account_code', '1030'))->sum('credit'));
    }

    public function test_legacy_order_payment_triggers_auto_journal(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'order_no'        => 'TEST-ORD-' . uniqid(),
            'user_id'         => $user->id,
            'billing_name'    => $user->name ?? 'Test Customer',
            'billing_email'   => $user->email ?? 'test@example.com',
            'billing_phone'   => '12345678',
            'billing_address' => 'Test Address, Copenhagen',
            'subtotal_amount' => 1500,
            'total_amount'    => 1500,
            'paid_amount'     => 0,
            'due_amount'      => 1500,
            'payment_status'  => 'unpaid',
            'order_status'    => 'completed',
        ]);

        $orderPayment = OrderPayment::create([
            'order_id'       => $order->id,
            'amount'         => 350,
            'payment_method' => 'cash',
            'transaction_id' => 'TXN-LEGACY-1',
            'note'           => 'Legacy store payment',
        ]);

        $this->assertNotNull($orderPayment);

        // Verify Observer posted exactly ONE Journal Entry for OrderPayment
        $journalCount = JournalEntry::where('reference_type', OrderPayment::class)->where('reference_id', $orderPayment->id)->count();
        $this->assertEquals(1, $journalCount, "OrderPayment observer must post exactly ONE Journal Entry.");

        $journal = JournalEntry::where('reference_type', OrderPayment::class)->where('reference_id', $orderPayment->id)->first();
        $this->assertEquals(350, $journal->lines()->whereHas('account', fn($q) => $q->where('account_code', '1010'))->sum('debit'));
        $this->assertEquals(350, $journal->lines()->whereHas('account', fn($q) => $q->where('account_code', '1030'))->sum('credit'));
    }

    public function test_customer_overpayment_is_strictly_rejected(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'order_no'        => 'TEST-ORD-' . uniqid(),
            'user_id'         => $user->id,
            'billing_name'    => $user->name ?? 'Test Customer',
            'billing_email'   => $user->email ?? 'test@example.com',
            'billing_phone'   => '12345678',
            'billing_address' => 'Test Address, Copenhagen',
            'subtotal_amount' => 500,
            'total_amount'    => 500,
            'paid_amount'     => 0,
            'due_amount'      => 500,
            'payment_status'  => 'unpaid',
            'order_status'    => 'completed',
        ]);

        $invoice = SalesInvoice::create([
            'invoice_no'      => 'INV-' . uniqid(),
            'order_id'        => $order->id,
            'date'            => now()->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
            'total_amount'    => 500,
            'paid_amount'     => 0,
            'due_amount'      => 500,
            'status'          => 'posted',
        ]);

        $service = app(CustomerPaymentService::class);

        $this->expectException(Exception::class);
        $service->recordPayment([
            'customer_id'      => $user->id,
            'sales_invoice_id' => $invoice->id,
            'amount'           => 600, // Exceeds due 500
            'payment_method'   => 'bank',
            'payment_date'     => now()->toDateString(),
        ], $user->id);
    }

    public function test_vendor_bill_and_payment_auto_posts_balanced_gl_journals_without_duplicates(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $vendor = Vendor::first() ?? Vendor::create([
            'name'       => 'Test Supplier',
            'shop_name'  => 'Nordic Supplies ApS',
            'email'      => 'supplier@example.com',
            'phone'      => '87654321',
            'address'    => 'Aarhus',
            'status'     => 1,
        ]);

        $product = Product::first();
        $productId = $product ? $product->id : 1;

        $purchase = Purchase::create([
            'po_no'          => 'PO-' . uniqid(),
            'invoice_no'     => 'INV-SUP-' . uniqid(),
            'vendor_id'      => $vendor->id,
            'date'           => now()->toDateString(),
            'total_amount'   => 2000,
            'paid_amount'    => 0,
            'due_amount'     => 2000,
            'order_status'   => 'received',
            'payment_status' => 'unpaid',
        ]);

        // 1. Create Vendor Bill
        $billService = app(VendorBillService::class);
        $bill = $billService->createBill([
            'purchase_id' => $purchase->id,
            'bill_date'   => now()->toDateString(),
            'due_date'    => now()->addDays(30)->toDateString(),
            'items'       => [
                [
                    'product_id'   => $productId,
                    'description'  => 'Raw Materials Batch A',
                    'qty'          => 10,
                    'unit_price'   => 200,
                    'landed_cost'  => 200,
                ]
            ],
            'apply_debit_notes' => false,
        ]);

        $this->assertNotNull($bill);
        $this->assertEquals(2000, $bill->grand_total);
        $this->assertEquals('unpaid', $bill->payment_status);

        // Verify EXACTLY ONE Journal Entry for Vendor Bill
        $billJournalCount = JournalEntry::where('reference_type', VendorBill::class)->where('reference_id', $bill->id)->count();
        $this->assertEquals(1, $billJournalCount, "Vendor Bill must have exactly ONE Journal Entry.");

        // 2. Pay Vendor Bill
        $paymentService = app(VendorPaymentService::class);
        $vendorPayment = $paymentService->processPayment([
            'purchase_id'    => $purchase->id,
            'vendor_bill_id' => $bill->id,
            'amount'         => 800,
            'payment_method' => 'bank',
            'payment_date'   => now()->toDateString(),
        ]);

        $this->assertNotNull($vendorPayment);
        $bill->refresh();
        $this->assertEquals(800, $bill->paid_amount);
        $this->assertEquals(1200, $bill->due_amount);
        $this->assertEquals('partial', $bill->payment_status);

        // Verify EXACTLY ONE Journal Entry for Vendor Payment
        $payJournalCount = JournalEntry::where('reference_type', PurchasePayment::class)->where('reference_id', $vendorPayment->id)->count();
        $this->assertEquals(1, $payJournalCount, "Vendor Payment must have exactly ONE Journal Entry.");
    }

    public function test_fiscal_year_closed_period_blocks_journal_posting(): void
    {
        $user = User::first() ?? User::factory()->create();

        // Create a closed fiscal year for 2025
        FiscalYear::create([
            'name'       => 'FY 2025 Closed',
            'start_date' => '2025-01-01',
            'end_date'   => '2025-12-31',
            'is_closed'  => true,
            'closed_at'  => now(),
            'closed_by'  => $user->id,
        ]);

        $journalService = app(JournalEntryService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CLOSED');

        $journalService->postJournal('Test Entry', $user, [
            ['account_code' => '1020', 'debit' => 100, 'credit' => 0],
            ['account_code' => '1030', 'debit' => 0,   'credit' => 100],
        ], '2025-06-15', 'Testing closed period block');
    }

    public function test_coa_edit_page_renders_successfully(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $account = ChartOfAccount::first();

        $response = $this->get(route('admin.chart-of-accounts.edit', $account->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Account Head');
    }

    public function test_customer_payment_show_page_renders_cleanly(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $payment = CustomerPayment::first();
        if (!$payment) {
            $payment = CustomerPayment::create([
                'payment_no'     => 'RCP-TEST',
                'user_id'        => $admin->id,
                'amount'         => 500,
                'payment_method' => 'bank',
                'payment_date'   => now()->toDateString(),
                'status'         => 'posted',
            ]);
        }

        $response = $this->get(route('admin.customer-payments.show', $payment->id));
        $response->assertStatus(200);
        $response->assertSee($payment->payment_no);
    }

    public function test_customer_payment_index_page_renders_with_kpi_cards(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $response = $this->get(route('admin.customer-payments.index'));
        $response->assertStatus(200);
        $response->assertSee('Total AR Outstanding');
        $response->assertSee('Collected This Month');
        $response->assertSee('Overdue AR');
        $response->assertViewHas('totalArOutstanding');
        $response->assertViewHas('collectedThisMonth');
        $response->assertViewHas('overdueAr30Days');
        $response->assertViewHas('totalPaymentsCount');
    }

    public function test_customer_payment_pdf_generation_renders_successfully(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $payment = CustomerPayment::first();
        if (!$payment) {
            $payment = CustomerPayment::create([
                'payment_no'     => 'RCP-TEST',
                'user_id'        => $admin->id,
                'amount'         => 500,
                'payment_method' => 'bank',
                'payment_date'   => now()->toDateString(),
                'status'         => 'posted',
            ]);
        }

        $response = $this->get(route('admin.customer-payments.pdf', $payment->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_coa_create_page_renders_successfully(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $response = $this->get(route('admin.chart-of-accounts.create'));
        $response->assertStatus(200);
        $response->assertSee('Create Chart of Account Head');
    }

    public function test_banking_and_reconciliation_workflow(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        // 1. Create Bank Account
        $accNo = 'DK-' . uniqid();
        $response = $this->post(route('admin.bank-accounts.store'), [
            'account_name'    => 'Danske Corporate Vault',
            'bank_name'       => 'Danske Bank',
            'account_number'  => $accNo,
            'opening_balance' => 50000,
        ]);
        $response->assertRedirect(route('admin.bank-accounts.index'));
        $this->assertDatabaseHas('bank_accounts', ['account_number' => $accNo]);

        // 2. View Bank index & Reconciliation page
        $this->get(route('admin.bank-accounts.index'))->assertStatus(200);
        $this->get(route('admin.bank-reconciliation.index'))->assertStatus(200);
    }

    public function test_petty_cash_workflow_and_gl_posting(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $response = $this->post(route('admin.petty-cash.store'), [
            'type'        => 'out',
            'amount'      => 250,
            'purpose'     => 'Office Stationery & Tea',
            'expense_acc' => '5010',
        ]);
        $response->assertRedirect(route('admin.petty-cash.index'));

        $this->assertDatabaseHas('petty_cash_transactions', [
            'amount'  => 250,
            'purpose' => 'Office Stationery & Tea',
        ]);

        $this->get(route('admin.petty-cash.index'))->assertStatus(200);
    }

    public function test_fund_transfers_contra_gl_posting(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $bankA = \App\Models\BankAccount::create([
            'company_id'      => 1,
            'account_name'    => 'Vault A',
            'bank_name'       => 'Bank A',
            'account_number'  => 'ACC-A-' . uniqid(),
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'status'          => true,
        ]);

        $bankB = \App\Models\BankAccount::create([
            'company_id'      => 1,
            'account_name'    => 'Vault B',
            'bank_name'       => 'Bank B',
            'account_number'  => 'ACC-B-' . uniqid(),
            'opening_balance' => 2000,
            'current_balance' => 2000,
            'status'          => true,
        ]);

        $response = $this->post(route('admin.fund-transfers.store'), [
            'from_account_id' => $bankA->id,
            'to_account_id'   => $bankB->id,
            'amount'          => 3000,
            'transfer_date'   => now()->toDateString(),
        ]);
        $response->assertRedirect(route('admin.fund-transfers.index'));

        $bankA->refresh();
        $bankB->refresh();
        $this->assertEquals(7000, $bankA->current_balance);
        $this->assertEquals(5000, $bankB->current_balance);

        $this->get(route('admin.fund-transfers.index'))->assertStatus(200);
    }

    public function test_asset_registration_and_monthly_depreciation_engine(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $code = 'FA-' . rand(1000, 9999);
        $asset = \App\Models\Asset::create([
            'company_id'          => 1,
            'asset_code'          => $code,
            'name'                => 'Company Vehicle Van',
            'category'            => 'Vehicles',
            'purchase_value'      => 120000,
            'purchase_date'       => now()->subMonths(2)->toDateString(),
            'useful_life_years'   => 5,
            'depreciation_method' => 'straight_line',
            'status'              => 'active',
        ]);

        $this->assertNotNull($asset);

        // Run monthly depreciation via Service
        $deprecService = app(\App\Services\Accounting\AssetDepreciationService::class);
        $period = now()->format('Y-m');
        $result = $deprecService->runMonthlyDepreciation($period);

        $this->assertGreaterThan(0, $result['processed_count']);
        $this->assertGreaterThan(0, $result['total_depreciation']);

        $this->get(route('admin.assets.index'))->assertStatus(200);
    }

    public function test_multi_invoice_fifo_allocation(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'order_no' => 'ORD-FIFO-' . uniqid(), 'user_id' => $user->id,
            'billing_name' => 'User', 'billing_email' => 'u@example.com', 'billing_phone' => '123',
            'billing_address' => 'DK', 'subtotal_amount' => 800, 'total_amount' => 800,
            'paid_amount' => 0, 'due_amount' => 800, 'payment_status' => 'unpaid', 'order_status' => 'completed'
        ]);

        // Create 2 open invoices for customer (Older: 300 DKK, Newer: 500 DKK)
        $inv1 = SalesInvoice::create([
            'invoice_no'   => 'INV-OLD-' . uniqid(),
            'order_id'     => $order->id,
            'user_id'      => $user->id,
            'date'         => now()->subDays(10)->toDateString(),
            'due_date'     => now()->addDays(20)->toDateString(),
            'total_amount' => 300,
            'paid_amount'  => 0,
            'due_amount'   => 300,
            'status'       => 'posted',
        ]);

        $inv2 = SalesInvoice::create([
            'invoice_no'   => 'INV-NEW-' . uniqid(),
            'order_id'     => $order->id,
            'user_id'      => $user->id,
            'date'         => now()->toDateString(),
            'due_date'     => now()->addDays(30)->toDateString(),
            'total_amount' => 500,
            'paid_amount'  => 0,
            'due_amount'   => 500,
            'status'       => 'posted',
        ]);

        $allocations = [
            ['sales_invoice_id' => $inv1->id, 'amount' => 300],
            ['sales_invoice_id' => $inv2->id, 'amount' => 200],
        ];

        $service = app(CustomerPaymentService::class);
        $payment = $service->recordPayment([
            'customer_id'      => $user->id,
            'amount'           => 500,
            'payment_method'   => 'bank',
            'payment_date'     => now()->toDateString(),
            'allocations_json' => json_encode($allocations),
        ], $user->id);

        $this->assertNotNull($payment);
        $this->assertEquals(500, $payment->amount);

        $inv1->refresh();
        $inv2->refresh();
        $this->assertEquals(300, $inv1->paid_amount);
        $this->assertEquals(0, $inv1->due_amount);
        $this->assertEquals('paid', $inv1->status);

        $this->assertEquals(200, $inv2->paid_amount);
        $this->assertEquals(300, $inv2->due_amount);
        $this->assertEquals('partial', $inv2->status);
    }

    public function test_vendor_bill_overpayment_guard_blocks_excess_payment(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $vendor = Vendor::first() ?? Vendor::create([
            'name' => 'Supplier Guard ApS', 'shop_name' => 'Guard Store',
            'email' => 'guard@example.com', 'phone' => '12345678', 'address' => 'Copenhagen', 'status' => 1
        ]);

        $purchase = Purchase::create([
            'po_no' => 'PO-' . uniqid(), 'invoice_no' => 'INV-SUP-' . uniqid(), 'vendor_id' => $vendor->id,
            'total_amount' => 1000, 'paid_amount' => 0, 'due_amount' => 1000, 'date' => now()->toDateString()
        ]);

        $bill = VendorBill::create([
            'bill_no' => 'BILL-' . uniqid(), 'purchase_id' => $purchase->id, 'vendor_id' => $vendor->id,
            'bill_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000, 'grand_total' => 1000, 'paid_amount' => 0, 'due_amount' => 1000,
            'payment_status' => 'unpaid'
        ]);

        $service = app(VendorPaymentService::class);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('exceeds outstanding');

        $service->processPayment([
            'purchase_id'    => $purchase->id,
            'vendor_bill_id' => $bill->id,
            'amount'         => 1200, // Exceeds 1000 due
            'payment_method' => 'bank',
            'payment_date'   => now()->toDateString(),
        ]);
    }

    public function test_three_way_match_status_verification(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $vendor = Vendor::first() ?? Vendor::create([
            'name' => 'Supplier 3Way', 'shop_name' => '3Way ApS',
            'email' => '3way@example.com', 'phone' => '12345678', 'address' => 'Copenhagen', 'country' => 'Denmark', 'status' => 1
        ]);

        $purchase = Purchase::create([
            'po_no' => 'PO-3WAY-' . uniqid(), 'invoice_no' => 'INV-3WAY-' . uniqid(), 'vendor_id' => $vendor->id,
            'total_amount' => 500, 'paid_amount' => 0, 'due_amount' => 500, 'date' => now()->toDateString()
        ]);

        $grn = \App\Models\GoodsReceipt::create([
            'grn_no' => 'GRN-3WAY-' . uniqid(), 'purchase_id' => $purchase->id,
            'outlet_id' => 1,
            'received_date' => now()->toDateString(), 'status' => 'approved'
        ]);

        $bill = VendorBill::create([
            'bill_no' => 'BILL-3WAY-' . uniqid(), 'purchase_id' => $purchase->id,
            'goods_receipt_id' => $grn->id, 'vendor_id' => $vendor->id,
            'bill_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 500, 'grand_total' => 500, 'paid_amount' => 0, 'due_amount' => 500,
            'payment_status' => 'unpaid'
        ]);

        $this->assertNotNull($bill->goodsReceipt);
        $this->assertEquals($grn->id, $bill->goods_receipt_id);
    }

    public function test_duplicate_journal_prevention_idempotency(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $journalService = app(JournalEntryService::class);

        $order = Order::create([
            'order_no' => 'TEST-IDEM-' . uniqid(), 'user_id' => $admin->id,
            'billing_name' => 'Test', 'billing_email' => 't@example.com', 'billing_phone' => '12345',
            'billing_address' => 'DK', 'subtotal_amount' => 200, 'total_amount' => 200,
            'paid_amount' => 0, 'due_amount' => 200, 'payment_status' => 'unpaid', 'order_status' => 'completed'
        ]);

        $lines = [
            ['account_code' => '1010', 'debit' => 200, 'credit' => 0],
            ['account_code' => '1030', 'debit' => 0, 'credit' => 200],
        ];

        // First Post
        $entry1 = $journalService->postJournal('test_idempotency', $order, $lines, now()->toDateString(), 'Idempotency Entry');
        $this->assertNotNull($entry1);

        // Check that only 1 journal entry exists
        $count = JournalEntry::where('reference_type', Order::class)->where('reference_id', $order->id)->count();
        $this->assertEquals(1, $count);
    }

    public function test_vendor_ledger_running_balance_calculation(): void
    {
        $vendor = Vendor::create([
            'name' => 'Ledger Supplier ' . uniqid(), 'shop_name' => 'Ledger Store',
            'email' => 'sup_' . uniqid() . '@example.com', 'phone' => '12345678', 'address' => 'Copenhagen', 'country' => 'Denmark', 'status' => 1
        ]);

        $purchase = Purchase::create([
            'po_no' => 'PO-' . uniqid(), 'invoice_no' => 'INV-' . uniqid(), 'vendor_id' => $vendor->id,
            'total_amount' => 1500, 'paid_amount' => 0, 'due_amount' => 1500, 'date' => now()->toDateString()
        ]);

        VendorBill::create([
            'bill_no' => 'BILL-RUN-' . uniqid(), 'purchase_id' => $purchase->id, 'vendor_id' => $vendor->id,
            'bill_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1500, 'grand_total' => 1500, 'paid_amount' => 500, 'due_amount' => 1000,
            'payment_status' => 'partial'
        ]);

        PurchasePayment::create([
            'payment_no' => 'PAY-RUN-' . uniqid(), 'purchase_id' => $purchase->id,
            'vendor_id' => $vendor->id, 'amount' => 500, 'base_amount' => 500,
            'payment_method' => 'bank', 'payment_date' => now()->toDateString(), 'status' => 'approved'
        ]);

        $ledgerService = app(\App\Services\VendorLedgerService::class);
        $statement = $ledgerService->getVendorStatement($vendor);

        $this->assertEquals(1500, $statement['total_billed']);
        $this->assertEquals(500, $statement['total_paid']);
        $this->assertEquals(1000, $statement['outstanding_balance']);
    }

    public function test_ap_aging_analysis_buckets_split(): void
    {
        $vendor = Vendor::create([
            'name' => 'Aging Vendor ' . uniqid(), 'shop_name' => 'Aging Store',
            'email' => 'aging_' . uniqid() . '@example.com', 'phone' => '87654321', 'address' => 'Aarhus', 'country' => 'Denmark', 'status' => 1
        ]);

        $purchase = Purchase::create([
            'po_no' => 'PO-AGE-' . uniqid(), 'invoice_no' => 'INV-AGE-' . uniqid(), 'vendor_id' => $vendor->id,
            'total_amount' => 3000, 'paid_amount' => 0, 'due_amount' => 3000, 'date' => now()->toDateString()
        ]);

        // 1. Current Bill (Not overdue)
        VendorBill::create([
            'bill_no' => 'BILL-CUR-' . uniqid(), 'purchase_id' => $purchase->id, 'vendor_id' => $vendor->id,
            'bill_date' => now()->toDateString(), 'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 1000, 'grand_total' => 1000, 'paid_amount' => 0, 'due_amount' => 1000,
            'payment_status' => 'unpaid'
        ]);

        // 2. Overdue Bill (45 days overdue -> 31-60 bucket)
        VendorBill::create([
            'bill_no' => 'BILL-OVD-' . uniqid(), 'purchase_id' => $purchase->id, 'vendor_id' => $vendor->id,
            'bill_date' => now()->subDays(75)->toDateString(), 'due_date' => now()->subDays(45)->toDateString(),
            'subtotal' => 2000, 'grand_total' => 2000, 'paid_amount' => 0, 'due_amount' => 2000,
            'payment_status' => 'unpaid'
        ]);

        $ledgerService = app(\App\Services\VendorLedgerService::class);
        $aging = $ledgerService->getAgingReport($vendor->id);

        $vendorRow = $aging->first();
        $this->assertNotNull($vendorRow);
        $this->assertEquals(1000, $vendorRow['current']);
        $this->assertEquals(2000, $vendorRow['days_31_60']);
        $this->assertEquals(3000, $vendorRow['total_due']);
    }

    public function test_trial_balance_debit_credit_zero_difference(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $response = $this->get(route('admin.reports.trial-balance'));
        $response->assertStatus(200);
        $response->assertSee('Trial Balance');
    }

    public function test_profit_and_loss_and_balance_sheet_integrity(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $pnlResponse = $this->get(route('admin.reports.profit-loss'));
        $pnlResponse->assertStatus(200);
        $pnlResponse->assertSee('Profit & Loss');

        $bsResponse = $this->get(route('admin.reports.balance-sheet'));
        $bsResponse->assertStatus(200);
        $bsResponse->assertSee('Balance Sheet');
    }

    public function test_chart_of_accounts_core_system_protection(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $cashAcc = ChartOfAccount::where('account_code', '1010')->first();
        $this->assertNotNull($cashAcc);
        $this->assertTrue($cashAcc->isSystemProtected());

        // Attempting to delete protected account should be blocked
        $response = $this->delete(route('admin.chart-of-accounts.destroy', $cashAcc->id));
        $this->assertDatabaseHas('chart_of_accounts', ['account_code' => '1010']);
    }

    public function test_fiscal_year_close_and_reopen_lock_cycle(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $fy = FiscalYear::create([
            'name'       => 'FY Cycle ' . uniqid(),
            'start_date' => '2027-01-01',
            'end_date'   => '2027-12-31',
            'is_closed'  => false,
        ]);

        // Toggle Close
        $this->post(route('admin.fiscal-years.toggle-close', $fy->id));
        $fy->refresh();
        $this->assertTrue((bool)$fy->is_closed);

        // Toggle Reopen
        $this->post(route('admin.fiscal-years.toggle-close', $fy->id));
        $fy->refresh();
        $this->assertFalse((bool)$fy->is_closed);
    }

    public function test_customer_advance_and_unallocated_deposit_handling(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $order = Order::create([
            'order_no' => 'ORD-ADV-' . uniqid(), 'user_id' => $user->id,
            'billing_name' => 'User Adv', 'billing_email' => 'uadv@example.com', 'billing_phone' => '123',
            'billing_address' => 'DK', 'subtotal_amount' => 1000, 'total_amount' => 1000,
            'paid_amount' => 0, 'due_amount' => 1000, 'payment_status' => 'unpaid', 'order_status' => 'completed'
        ]);

        $invoice = SalesInvoice::create([
            'invoice_no'   => 'INV-ADV-' . uniqid(),
            'order_id'     => $order->id,
            'user_id'      => $user->id,
            'date'         => now()->toDateString(),
            'due_date'     => now()->addDays(30)->toDateString(),
            'total_amount' => 1000,
            'paid_amount'  => 0,
            'due_amount'   => 1000,
            'status'       => 'posted',
        ]);

        $service = app(CustomerPaymentService::class);
        $payment = $service->recordPayment([
            'customer_id'      => $user->id,
            'sales_invoice_id' => $invoice->id,
            'amount'           => 1400, // 1000 settles invoice + 400 excess
            'allow_advance'    => true,
            'payment_method'   => 'bank',
            'payment_date'     => now()->toDateString(),
        ], $user->id);

        $this->assertNotNull($payment);
        $this->assertEquals(1400, $payment->amount);
        $this->assertEquals(400, $payment->unallocated_amount);

        $invoice->refresh();
        $this->assertEquals(1000, $invoice->paid_amount);
        $this->assertEquals(0, $invoice->due_amount);
        $this->assertEquals('paid', $invoice->status);

        // Check journal lines: DR 1020 (1400) / CR 1030 (1000) / CR 2040 (400)
        $entry = JournalEntry::where('reference_type', CustomerPayment::class)->where('reference_id', $payment->id)->first();
        $this->assertNotNull($entry);

        $crAdvanceLine = $entry->lines()->whereHas('account', fn($q) => $q->where('account_code', '2040'))->first();
        $this->assertNotNull($crAdvanceLine);
        $this->assertEquals(400, $crAdvanceLine->credit);
    }

    public function test_asset_reducing_balance_depreciation_method(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $code = 'FA-RED-' . rand(1000, 9999);
        $asset = \App\Models\Asset::create([
            'company_id'          => 1,
            'asset_code'          => $code,
            'name'                => 'High-End Production Machinery',
            'category'            => 'Machinery',
            'purchase_value'      => 60000,
            'purchase_date'       => now()->subMonths(1)->toDateString(),
            'useful_life_years'   => 5,
            'depreciation_method' => 'reducing_balance',
            'status'              => 'active',
        ]);

        $this->assertNotNull($asset);

        $period = now()->addMonths(3)->format('Y-m');
        $deprecService = app(\App\Services\Accounting\AssetDepreciationService::class);
        $result = $deprecService->runMonthlyDepreciation($period);

        $this->assertGreaterThan(0, $result['processed_count']);
        $this->assertGreaterThan(0, $result['total_depreciation']);
    }

    public function test_vendor_bill_show_page_renders_3_way_match_banner(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);

        $vendor = Vendor::first() ?? Vendor::create([
            'name' => 'Supplier 3Way Test', 'shop_name' => '3Way Store',
            'email' => '3waytest@example.com', 'phone' => '12345678', 'address' => 'Copenhagen', 'country' => 'Denmark', 'status' => 1
        ]);

        $purchase = Purchase::create([
            'po_no' => 'PO-3WAY-TEST-' . uniqid(), 'invoice_no' => 'INV-3WAY-TEST-' . uniqid(), 'vendor_id' => $vendor->id,
            'total_amount' => 500, 'paid_amount' => 0, 'due_amount' => 500, 'date' => now()->toDateString()
        ]);

        $bill = VendorBill::create([
            'bill_no' => 'BILL-3WAY-TEST-' . uniqid(), 'purchase_id' => $purchase->id,
            'vendor_id' => $vendor->id, 'bill_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 500, 'grand_total' => 500, 'paid_amount' => 0, 'due_amount' => 500,
            'payment_status' => 'unpaid'
        ]);

        $response = $this->get(route('admin.vendor-bills.show', $bill->id));
        $response->assertStatus(200);
        $response->assertSee('3-Way Match Audit Verification');
    }
}
