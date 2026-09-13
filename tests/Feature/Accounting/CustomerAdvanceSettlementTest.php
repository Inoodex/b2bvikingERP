<?php

namespace Tests\Feature\Accounting;

use App\Models\AdvancePayment;
use App\Models\ChartOfAccount;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\PaymentAllocation;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\CustomerPaymentService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerAdvanceSettlementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $customer;
    protected CustomerPaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminUser = User::firstOrCreate(
            ['email' => 'advance_admin@b2bviking.dk'],
            ['name' => 'Advance Test Admin', 'password' => bcrypt('password123')]
        );
        if (!$this->adminUser->hasRole('Admin')) {
            $this->adminUser->assignRole($roleAdmin);
        }

        $this->customer = User::create([
            'name'     => 'Wholesale Advance Customer',
            'email'    => 'adv_cust_' . uniqid() . '@b2bviking.dk',
            'password' => bcrypt('password123'),
        ]);

        // Ensure Chart of Accounts heads exist
        ChartOfAccount::firstOrCreate(['account_code' => '1020'], ['account_name' => 'Bank Account', 'account_type' => 'asset']);
        ChartOfAccount::firstOrCreate(['account_code' => '1030'], ['account_name' => 'Accounts Receivable', 'account_type' => 'asset']);
        ChartOfAccount::firstOrCreate(['account_code' => '2040'], ['account_name' => 'Customer Advances & Deposits', 'account_type' => 'liability', 'normal_balance' => 'credit']);

        $this->paymentService = app(CustomerPaymentService::class);
    }

    #[Test]
    public function it_can_collect_customer_advance_deposit_and_posts_gl_2040()
    {
        // 1. Collect advance deposit of kr. 5,000 without any open invoices
        $payment = $this->paymentService->recordPayment([
            'user_id'        => $this->customer->id,
            'amount'         => 5000.00,
            'payment_method' => 'bank',
            'payment_date'   => now()->toDateString(),
            'notes'          => 'Initial seasonal order deposit',
        ], $this->adminUser->id);

        $this->assertNotNull($payment);
        $this->assertEquals(5000.00, (float)$payment->amount);
        $this->assertEquals(5000.00, (float)$payment->unallocated_amount);
        $this->assertTrue((bool)$payment->is_advance);

        // Verify available advance balance
        $balance = $this->paymentService->getCustomerAdvanceBalance($this->customer->id);
        $this->assertEquals(5000.00, $balance);

        // Verify advance_payments table sync
        $this->assertDatabaseHas('advance_payments', [
            'party_type' => 'customer',
            'party_id'   => $this->customer->id,
            'amount'     => 5000.00,
            'balance'    => 5000.00,
        ]);

        // Verify GL Journal Entry: DR 1020 (Bank) 5000 / CR 2040 (Customer Advances) 5000
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => CustomerPayment::class,
            'reference_id'   => $payment->id,
        ]);

        $entry = \App\Models\JournalEntry::where('reference_type', CustomerPayment::class)
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($entry);
        $drBank = $entry->lines()->whereHas('account', fn($q) => $q->where('account_code', '1020'))->first();
        $crAdvance = $entry->lines()->whereHas('account', fn($q) => $q->where('account_code', '2040'))->first();

        $this->assertNotNull($drBank);
        $this->assertEquals(5000.00, (float)$drBank->debit);
        $this->assertNotNull($crAdvance);
        $this->assertEquals(5000.00, (float)$crAdvance->credit);
    }

    #[Test]
    public function it_can_settle_invoice_using_advance_deposit_and_posts_dr_2040_cr_1030()
    {
        // Step 1: Collect kr. 5,000 advance
        $advPayment = $this->paymentService->recordPayment([
            'user_id'        => $this->customer->id,
            'amount'         => 5000.00,
            'payment_method' => 'bank',
            'payment_date'   => now()->toDateString(),
        ], $this->adminUser->id);

        $this->assertEquals(5000.00, $this->paymentService->getCustomerAdvanceBalance($this->customer->id));

        // Step 2: Create a Sales Invoice for kr. 3,200
        $order = Order::create([
            'order_no'        => 'ORD-ADV-' . uniqid(),
            'user_id'         => $this->customer->id,
            'total_amount'    => 3200.00,
            'paid_amount'     => 0.00,
            'due_amount'      => 3200.00,
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'billing_name'    => $this->customer->name,
            'billing_email'   => $this->customer->email,
            'billing_phone'   => '+4512345678',
            'billing_address' => 'Vestergade 12, Copenhagen',
        ]);

        $invoice = SalesInvoice::create([
            'invoice_no'   => 'INV-ADV-' . uniqid(),
            'order_id'     => $order->id,
            'customer_id'  => $this->customer->id,
            'total_amount' => 3200.00,
            'paid_amount'  => 0.00,
            'due_amount'   => 3200.00,
            'status'       => 'posted',
            'date'         => now()->toDateString(),
            'due_date'     => now()->addDays(30)->toDateString(),
        ]);

        // Step 3: Settle invoice via Advance Payment method
        $settlePayment = $this->paymentService->recordPayment([
            'user_id'          => $this->customer->id,
            'sales_invoice_id' => $invoice->id,
            'amount'           => 3200.00,
            'payment_method'   => 'advance',
            'payment_date'     => now()->toDateString(),
            'notes'            => 'Settled from Season Advance Deposit',
        ], $this->adminUser->id);

        $this->assertNotNull($settlePayment);

        // Assert Invoice is now Paid in Full
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(3200.00, (float)$invoice->paid_amount);
        $this->assertEquals(0.00, (float)$invoice->due_amount);

        // Assert Order is Paid
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals(3200.00, (float)$order->paid_amount);
        $this->assertEquals(0.00, (float)$order->due_amount);

        // Assert Remaining Customer Advance Balance is kr. 1,800 (5,000 - 3,200)
        $remainingBalance = $this->paymentService->getCustomerAdvanceBalance($this->customer->id);
        $this->assertEquals(1800.00, $remainingBalance);

        // Assert Source Payment was deducted
        $advPayment->refresh();
        $this->assertEquals(1800.00, (float)$advPayment->unallocated_amount);

        // Assert advance_payments ledger table updated
        $this->assertDatabaseHas('advance_payments', [
            'party_id'       => $this->customer->id,
            'applied_amount' => 3200.00,
            'balance'        => 1800.00,
        ]);

        // Assert PaymentAllocation record was generated
        $this->assertDatabaseHas('payment_allocations', [
            'payment_type'   => 'advance_payment',
            'payment_id'     => $settlePayment->id,
            'invoice_id'     => $invoice->id,
            'matched_amount' => 3200.00,
        ]);

        // Assert Settlement Journal: DR 2040 (3,200) / CR 1030 (3,200)
        $entry = \App\Models\JournalEntry::where('reference_type', CustomerPayment::class)
            ->where('reference_id', $settlePayment->id)
            ->first();

        $this->assertNotNull($entry);
        $drAdvance = $entry->lines()->whereHas('account', fn($q) => $q->where('account_code', '2040'))->first();
        $crAR = $entry->lines()->whereHas('account', fn($q) => $q->where('account_code', '1030'))->first();

        $this->assertNotNull($drAdvance, 'Debit line for Account 2040 must exist');
        $this->assertEquals(3200.00, (float)$drAdvance->debit);

        $this->assertNotNull($crAR, 'Credit line for Account 1030 must exist');
        $this->assertEquals(3200.00, (float)$crAR->credit);
    }

    #[Test]
    public function it_blocks_settlement_exceeding_customer_available_advance_deposit()
    {
        // Collect kr. 1,000 advance
        $this->paymentService->recordPayment([
            'user_id'        => $this->customer->id,
            'amount'         => 1000.00,
            'payment_method' => 'bank',
            'payment_date'   => now()->toDateString(),
        ], $this->adminUser->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('exceeds customer available advance deposit');

        // Try to settle kr. 2,500 using advance
        $this->paymentService->recordPayment([
            'user_id'        => $this->customer->id,
            'amount'         => 2500.00,
            'payment_method' => 'advance',
            'payment_date'   => now()->toDateString(),
        ], $this->adminUser->id);
    }
}
