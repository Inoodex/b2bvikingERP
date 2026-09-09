<?php

namespace Tests\Feature\Phase6;

use App\Models\ChartOfAccount;
use App\Models\CodCollection;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\PaymentTransaction;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\Payment\CodService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminUser = User::firstOrCreate(
            ['email' => 'phase6admin@b2bviking.com'],
            [
                'name'     => 'Phase 6 Admin',
                'password' => bcrypt('password123'),
            ]
        );
        if (!$this->adminUser->hasRole('Admin')) {
            $this->adminUser->assignRole($role);
        }
    }

    #[Test]
    public function it_can_load_payment_settings_screen_with_vertical_tabs()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.payment-settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Paypal');
        $response->assertDontSee('id="v-pills-payoneer-tab"', false);
        $response->assertDontSee('id="v-pills-mobilepay-tab"', false);
        $response->assertSee('COD');
        $response->assertSee('Paypal Status');
        $response->assertSee('Account Mode');
    }

    #[Test]
    public function it_can_update_paypal_settings_in_dedicated_table()
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.payment-settings.paypal.update'), [
                'status'        => 'enable',
                'mode'          => 'live',
                'country_name'  => 'Denmark',
                'currency_name' => 'Danish Krone',
                'client_id'     => 'TEST_PAYPAL_CLIENT_ID_NEW_TABLE',
                'client_secret' => 'TEST_PAYPAL_SECRET_KEY_NEW_TABLE',
                'currency_rate' => 1.0000,
            ]);

        $response->assertRedirect(route('admin.payment-settings.index', ['tab' => 'paypal']));

        $this->assertDatabaseHas('payment_settings', [
            'key'           => 'paypal',
            'status'        => 'enable',
            'mode'          => 'live',
            'client_id'     => 'TEST_PAYPAL_CLIENT_ID_NEW_TABLE',
            'client_secret' => 'TEST_PAYPAL_SECRET_KEY_NEW_TABLE',
        ]);
    }

    #[Test]
    public function it_can_update_cod_settings_in_dedicated_table()
    {
        $account = ChartOfAccount::firstOrCreate(
            ['account_code' => '1010'],
            ['account_name' => 'Petty Cash / Cash in Hand', 'account_type' => 'asset']
        );

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.payment-settings.cod.update'), [
                'status'             => 'enable',
                'country_name'       => 'Denmark',
                'currency_name'      => 'Danish Krone',
                'currency_rate'      => 1.0000,
                'deposit_account_id' => $account->id,
                'instructions'       => 'Hand cash to delivery courier upon delivery.',
            ]);

        $response->assertRedirect(route('admin.payment-settings.index', ['tab' => 'cod']));

        $this->assertDatabaseHas('payment_settings', [
            'key'                => 'cod',
            'status'             => 'enable',
            'deposit_account_id' => $account->id,
        ]);
    }

    #[Test]
    public function it_can_load_cod_collections_and_transactions_screens()
    {
        $responseCod = $this->actingAs($this->adminUser)
            ->get(route('admin.payments.cod.index'));
        $responseCod->assertStatus(200);
        $responseCod->assertSee('Cash On Delivery (COD) Collections');

        $responseTxn = $this->actingAs($this->adminUser)
            ->get(route('admin.payments.transactions.index'));
        $responseTxn->assertStatus(200);
        $responseTxn->assertSee('Payment Transactions Audit Trail');
    }

    #[Test]
    public function cod_cashier_handover_settles_invoice_and_posts_gl_entry()
    {
        $accountCash = ChartOfAccount::firstOrCreate(
            ['account_code' => '1010'],
            ['account_name' => 'Petty Cash / Cash in Hand', 'account_type' => 'asset']
        );
        $accountAR = ChartOfAccount::firstOrCreate(
            ['account_code' => '1030'],
            ['account_name' => 'Accounts Receivable', 'account_type' => 'asset']
        );

        PaymentSetting::updateOrCreate(
            ['key' => 'cod'],
            [
                'name'               => 'Cash On Delivery',
                'status'             => 'enable',
                'mode'               => 'live',
                'deposit_account_id' => $accountCash->id,
            ]
        );

        $customer = User::first() ?? User::create([
            'name'     => 'Phase6 Customer',
            'email'    => 'p6cust@b2bviking.dk',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'order_no'        => 'ORD-P6-' . uniqid(),
            'user_id'         => $customer->id,
            'total_amount'    => 1200.00,
            'paid_amount'     => 0.00,
            'due_amount'      => 1200.00,
            'status'          => 'pending',
            'payment_status'  => 'pending',
            'billing_name'    => 'Phase6 Customer',
            'billing_email'   => 'p6cust@b2bviking.dk',
            'billing_phone'   => '45123456',
            'billing_address' => 'Strøget 10, Copenhagen',
        ]);

        $invoice = SalesInvoice::create([
            'invoice_no'     => 'INV-P6-' . uniqid(),
            'order_id'       => $order->id,
            'customer_id'    => $customer->id,
            'customer_name'  => 'Phase6 Customer',
            'customer_email' => 'p6cust@b2bviking.dk',
            'total_amount'   => 1200.00,
            'paid_amount'    => 0.00,
            'due_amount'     => 1200.00,
            'status'         => 'unpaid',
            'date'           => now()->toDateString(),
            'due_date'       => now()->addDays(30),
        ]);

        $codService = app(CodService::class);
        $collection = $codService->createCodCollection($order, $invoice);

        $this->assertNotNull($collection);
        $this->assertEquals('pending_dispatch', $collection->status);

        $settleResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.payments.cod.settle', $collection->id), [
                'cash_received'      => 1200.00,
                'deposit_account_id' => $accountCash->id,
                'notes'              => 'Cashier physical handover verified in safe box.',
            ]);

        $settleResponse->assertRedirect();

        $collection->refresh();
        $this->assertEquals('handed_over', $collection->status);
        $this->assertNotNull($collection->handed_over_at);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(1200.00, (float)$invoice->paid_amount);

        $this->assertDatabaseHas('payment_transactions', [
            'gateway'  => 'cod',
            'order_id' => $order->id,
            'status'   => 'captured',
        ]);
    }
}
