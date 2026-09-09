<?php

namespace App\Services\Payment;

use App\Models\GeneralSetting;
use App\Models\SalesInvoice;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    /**
     * Get PayPal credentials from GeneralSetting or config.
     */
    public function getCredentials(): array
    {
        $gateway = \App\Models\PaymentSetting::getGateway('paypal');
        if ($gateway) {
            $clientId = $gateway->client_id ?: config('services.paypal.client_id');
            $clientSecret = $gateway->client_secret ?: config('services.paypal.client_secret');
            $mode = $gateway->mode ?: (config('services.paypal.mode') ?: 'sandbox');
            $currency = $gateway->currency_name ?: 'USD';

            return [
                'client_id'     => trim((string)$clientId),
                'client_secret' => trim((string)$clientSecret),
                'mode'          => strtolower(trim((string)$mode)) === 'live' ? 'live' : 'sandbox',
                'currency'      => strtoupper(trim((string)$currency)) ?: 'USD',
                'currency_rate' => (float)($gateway->currency_rate ?: 1.0),
                'country_name'  => $gateway->country_name,
                'enabled'       => $gateway->isEnabled(),
            ];
        }

        $setting = GeneralSetting::first();

        $clientId = $setting?->paypal_client_id ?: config('services.paypal.client_id');
        $clientSecret = $setting?->paypal_client_secret ?: config('services.paypal.client_secret');
        $mode = $setting?->paypal_mode ?: (config('services.paypal.mode') ?: 'sandbox');
        $currency = $setting?->paypal_currency ?: 'DKK';

        return [
            'client_id'     => trim((string)$clientId),
            'client_secret' => trim((string)$clientSecret),
            'mode'          => strtolower(trim((string)$mode)) === 'live' ? 'live' : 'sandbox',
            'currency'      => strtoupper(trim((string)$currency)) ?: 'DKK',
            'currency_rate' => 1.0,
            'country_name'  => 'Denmark',
            'enabled'       => (bool)($setting?->paypal_enabled ?? true),
        ];
    }

    /**
     * Get API base URL based on mode.
     */
    public function getBaseUrl(?string $mode = null): string
    {
        $credentials = $this->getCredentials();
        $targetMode = $mode ?: $credentials['mode'];

        return $targetMode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Fetch or retrieve cached OAuth2 Bearer Access Token.
     */
    public function getAccessToken(?array $customCredentials = null): string
    {
        $creds = $customCredentials ?: $this->getCredentials();
        $clientId = $creds['client_id'];
        $clientSecret = $creds['client_secret'];
        $mode = $creds['mode'];

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("PayPal Client ID or Client Secret is missing in Gateway Settings.");
        }

        $cacheKey = 'paypal_oauth_token_' . md5($clientId . $mode);

        if (!$customCredentials && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $endpoint = $this->getBaseUrl($mode) . '/v1/oauth2/token';

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post($endpoint, [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            $err = $response->json()['error_description'] ?? ($response->json()['error'] ?? $response->body());
            Log::error("PayPal OAuth2 Error: " . $err);
            throw new Exception("PayPal Authentication Failed: " . $err);
        }

        $data = $response->json();
        $token = $data['access_token'] ?? null;
        $expiresIn = max(60, ($data['expires_in'] ?? 3600) - 120);

        if (!$token) {
            throw new Exception("PayPal OAuth2 returned an empty access token.");
        }

        if (!$customCredentials) {
            Cache::put($cacheKey, $token, $expiresIn);
        }

        return $token;
    }

    /**
     * Ping PayPal API to test client credentials live.
     */
    public function testConnection(?string $clientId = null, ?string $clientSecret = null, ?string $mode = null): array
    {
        try {
            $creds = $this->getCredentials();
            if ($clientId && $clientSecret) {
                $creds['client_id'] = $clientId;
                $creds['client_secret'] = $clientSecret;
                $creds['mode'] = $mode ?: 'sandbox';
            }

            $token = $this->getAccessToken($creds);

            return [
                'success' => true,
                'mode'    => $creds['mode'],
                'message' => "Successfully connected to PayPal ({$creds['mode']} environment)!",
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create PayPal REST v2 Order for a SalesInvoice or an Order.
     */
    public function createOrder(SalesInvoice|\App\Models\Order $subject, ?string $returnUrl = null, ?string $cancelUrl = null): array
    {
        $creds = $this->getCredentials();
        if (!$creds['enabled']) {
            throw new Exception("PayPal payment gateway is currently disabled in system settings.");
        }

        $token = $this->getAccessToken();
        $endpoint = $this->getBaseUrl() . '/v2/checkout/orders';

        $returnUrl = $returnUrl ?: route('admin.payments.paypal.success');
        $cancelUrl = $cancelUrl ?: route('admin.payments.paypal.cancel');

        $isOrder = ($subject instanceof \App\Models\Order);

        $dueAmount = $isOrder
            ? round((float)($subject->due_amount > 0 ? $subject->due_amount : $subject->total_amount), 2)
            : round((float)$subject->due_amount, 2);

        if ($dueAmount <= 0) {
            $identifier = $isOrder ? "Order #{$subject->order_no}" : "Invoice #{$subject->invoice_no}";
            throw new Exception("{$identifier} has no outstanding due balance.");
        }

        $refId = $isOrder ? 'ORD-' . $subject->id : 'INV-' . $subject->id;
        $desc = $isOrder
            ? "Order #{$subject->order_no} - Copenhagen Tourist Point"
            : "Invoice #{$subject->invoice_no} - Copenhagen Tourist Point";
        $customId = $isOrder ? 'ORD:' . $subject->id : 'INV:' . $subject->id;

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $refId,
                    'description'  => $desc,
                    'custom_id'    => $customId,
                    'amount'       => [
                        'currency_code' => $creds['currency'],
                        'value'         => number_format($dueAmount, 2, '.', ''),
                    ],
                ]
            ],
            'application_context' => [
                'brand_name'          => 'Copenhagen Tourist Point',
                'locale'              => 'da-DK',
                'landing_page'        => 'BILLING',
                'user_action'         => 'PAY_NOW',
                'return_url'          => $returnUrl,
                'cancel_url'          => $cancelUrl,
            ],
        ];

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        if ($response->failed()) {
            $err = $response->json()['message'] ?? $response->body();
            Log::error("PayPal Order Creation Failed: " . $err);
            throw new Exception("PayPal Order Creation Failed: " . $err);
        }

        $data = $response->json();
        $orderId = $data['id'] ?? null;
        $links = $data['links'] ?? [];
        $approveUrl = collect($links)->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$orderId || !$approveUrl) {
            throw new Exception("Invalid PayPal order creation response received.");
        }

        return [
            'id'           => $orderId,
            'approve_url'  => $approveUrl,
            'approval_url' => $approveUrl,
            'amount'       => $dueAmount,
            'currency'     => $creds['currency'],
        ];
    }

    /**
     * Create PayPal REST v2 Order directly from an amount and reference (e.g. fresh cart checkout).
     */
    public function createOrderFromAmount(float $amount, string $reference, string $description, ?string $returnUrl = null, ?string $cancelUrl = null): array
    {
        $creds = $this->getCredentials();
        if (!$creds['enabled']) {
            throw new Exception("PayPal payment gateway is currently disabled in system settings.");
        }

        if ($amount <= 0) {
            throw new Exception("Checkout amount must be greater than zero.");
        }

        $token = $this->getAccessToken();
        $endpoint = $this->getBaseUrl() . '/v2/checkout/orders';

        $returnUrl = $returnUrl ?: route('checkout.paypal.success');
        $cancelUrl = $cancelUrl ?: route('checkout.paypal.cancel');

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $reference,
                    'description'  => $description,
                    'custom_id'    => $reference,
                    'amount'       => [
                        'currency_code' => $creds['currency'],
                        'value'         => number_format($amount, 2, '.', ''),
                    ],
                ]
            ],
            'application_context' => [
                'brand_name'          => 'Copenhagen Tourist Point',
                'locale'              => 'da-DK',
                'landing_page'        => 'BILLING',
                'user_action'         => 'PAY_NOW',
                'return_url'          => $returnUrl,
                'cancel_url'          => $cancelUrl,
            ],
        ];

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        if ($response->failed()) {
            $err = $response->json()['message'] ?? $response->body();
            Log::error("PayPal Order Creation Failed: " . $err);
            throw new Exception("PayPal Order Creation Failed: " . $err);
        }

        $data = $response->json();
        $orderId = $data['id'] ?? null;
        $links = $data['links'] ?? [];
        $approveUrl = collect($links)->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$orderId || !$approveUrl) {
            throw new Exception("Invalid PayPal order creation response received.");
        }

        return [
            'id'           => $orderId,
            'approve_url'  => $approveUrl,
            'approval_url' => $approveUrl,
            'amount'       => $amount,
            'currency'     => $creds['currency'],
        ];
    }

    /**
     * Synchronously capture a PayPal v2 Order on Return URL.
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();
        $endpoint = $this->getBaseUrl() . "/v2/checkout/orders/{$orderId}/capture";

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, []);

        if ($response->failed()) {
            $err = $response->json()['message'] ?? $response->body();
            Log::error("PayPal Capture Request Failed for Order [{$orderId}]: " . $err);
            throw new Exception("PayPal Capture Failed: " . $err);
        }

        $data = $response->json();
        $status = $data['status'] ?? 'FAILED';

        if ($status !== 'COMPLETED') {
            $detail = $data['details'][0]['issue'] ?? 'Not Completed';
            throw new Exception("PayPal Capture was not completed (Status: {$status} - {$detail}).");
        }

        return $data;
    }
}
