<?php

namespace Tests\Feature\Phase6;

use App\Mail\InvoicePaymentLinkMail;
use App\Models\ChartOfAccount;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnterpriseInvoicePaymentTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $customer;
    protected Order $order;
    protected SalesInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::first() ?? User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);
        $this->customer = User::create([
            'name'     => 'Enterprise B2B Outlet',
            'email'    => 'b2boutlet@vikingtest.dk',
            'password' => bcrypt('secret123'),
        ]);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $this->customer->assignRole($role);

        $this->order = Order::create([
            'order_no'        => 'ORD-ENT-' . uniqid(),
            'user_id'         => $this->customer->id,
            'billing_name'    => 'Enterprise B2B Outlet',
            'billing_email'   => 'b2boutlet@vikingtest.dk',
            'billing_phone'   => '+45 11 22 33 44',
            'billing_address' => 'Viking Street 1, Copenhagen',
            'total_amount'    => 2500.00,
            'paid_amount'     => 0.00,
            'due_amount'      => 2500.00,
            'status'          => 'pending',
            'payment_status'  => 'pending',
            'shipping_method' => 'standard',
            'payment_method'  => 'paypal',
        ]);

        $this->invoice = SalesInvoice::create([
            'invoice_no'      => 'INV-ENT-' . uniqid(),
            'order_id'        => $this->order->id,
            'total_amount'    => 2500.00,
            'paid_amount'     => 0.00,
            'due_amount'      => 2500.00,
            'status'          => 'posted',
            'date'            => now()->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
        ]);

        // Ensure active PayPal settings
        PaymentSetting::updateOrCreate(
            ['key' => 'paypal'],
            [
                'name'          => 'PayPal Express',
                'status'        => 'enable',
                'mode'          => 'sandbox',
                'client_id'     => 'test_client_id',
                'client_secret' => 'test_client_secret',
                'currency_name' => 'DKK',
            ]
        );
    }

    #[Test]
    public function sales_invoice_automatically_generates_payment_token_and_public_url()
    {
        $this->assertNotEmpty($this->invoice->payment_token);
        $this->assertStringContainsString('/invoices/pay/' . $this->invoice->payment_token, $this->invoice->public_payment_url);
    }

    #[Test]
    public function public_customer_invoice_payment_portal_loads_with_details_and_gateways()
    {
        $response = $this->get(route('invoices.pay', $this->invoice->payment_token));

        $response->assertStatus(200);
        $response->assertSee($this->invoice->invoice_no);
        $response->assertSee('kr. 2,500.00');
        $response->assertSee('PayPal Express');
        $response->assertDontSee('Danske Bank A/S');
    }

    #[Test]
    public function admin_sales_invoice_view_shows_enterprise_payment_actions_and_no_direct_paypal_button()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.sales-invoices.show', $this->invoice->id));

        $response->assertStatus(200);
        $response->assertSee('Customer Payment Actions');
        $response->assertSee('Preview Customer View');
        $response->assertSee('Copy Payment Link');
        $response->assertSee('Email Payment Link');
        $response->assertSee('Record Offline/Bank Receipt');
        // Old anti-pattern button must NOT be present
        $response->assertDontSee('Pay with PayPal Express');
    }

    #[Test]
    public function admin_can_send_invoice_payment_link_to_customer_via_email()
    {
        Mail::fake();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.sales-invoices.send-payment-link', $this->invoice->id));

        $response->assertRedirect();
        Mail::assertSent(InvoicePaymentLinkMail::class, function ($mail) {
            return $mail->hasTo($this->customer->email) &&
                   $mail->invoice->id === $this->invoice->id &&
                   !empty($mail->paymentUrl);
        });
    }

    #[Test]
    public function customer_orders_screen_shows_pay_invoice_button_for_unpaid_invoices()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('Pay Invoice');
        $response->assertSee(number_format($this->invoice->due_amount, 2));
    }

    #[Test]
    public function customer_orders_screen_shows_paypal_retry_button_for_unpaid_paypal_order()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('Pay Now with PayPal');
        $response->assertSee('Payment is pending. Complete checkout now:');
    }

    #[Test]
    public function admin_can_access_paypal_test_connection_endpoint()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.payment-settings.paypal.test'), [
                'client_id' => 'mock_test_client_id',
                'client_secret' => 'mock_test_secret',
                'mode' => 'sandbox',
            ]);

        // Returns JSON (even with mock creds failing PayPal auth, it returns valid 422 JSON response without crash)
        $this->assertTrue(in_array($response->status(), [200, 422]));
        $this->assertArrayHasKey('success', $response->json());
    }

    #[Test]
    public function paypal_cancel_on_fresh_checkout_preserves_cart_and_creates_no_order()
    {
        $initialOrderCount = Order::count();

        // Simulate customer cancelling PayPal during fresh checkout
        $response = $this->actingAs($this->customer)
            ->withSession([
                'pending_paypal_checkout' => [
                    'user_id' => $this->customer->id,
                    'validated' => ['payment_method' => 'paypal'],
                ]
            ])
            ->get(route('checkout.paypal.cancel'));

        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('warning');
        $this->assertFalse(session()->has('pending_paypal_checkout'));

        // Crucial Enterprise Guarantee: NO ghost order was created in DB!
        $this->assertEquals($initialOrderCount, Order::count());
    }

    #[Test]
    public function customer_account_orders_panel_shows_payment_status_badge()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('account.index', ['panel' => 'orders']));

        $response->assertStatus(200);
        $response->assertSee('Payment');
        $response->assertSee('PayPal');
    }

    #[Test]
    public function admin_sales_invoice_view_hides_payment_actions_when_invoice_is_fully_paid()
    {
        $paidInvoice = SalesInvoice::create([
            'invoice_no'      => 'INV-PAID-' . uniqid(),
            'order_id'        => $this->order->id,
            'total_amount'    => 2500.00,
            'paid_amount'     => 2500.00,
            'due_amount'      => 0.00,
            'status'          => 'paid',
            'date'            => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.sales-invoices.show', $paidInvoice->id));

        $response->assertStatus(200);
        $response->assertSee('Payment Settled in Full');
        $response->assertSee('Download Paid Tax Invoice (PDF)');
        $response->assertDontSee('Customer Payment Actions');
        $response->assertDontSee('Copy Payment Link');
        $response->assertDontSee('Email Payment Link');
    }
}
