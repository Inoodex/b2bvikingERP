<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Currency;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    /**
     * Display Payment Settings Screen with Vertical Tabs (matching Enterprise UI spec).
     */
    public function index(): View
    {
        $gateways = PaymentSetting::all()->keyBy('key');

        $paypal = $gateways->get('paypal') ?? PaymentSetting::create([
            'key'           => 'paypal',
            'name'          => 'PayPal Express',
            'status'        => 'disable',
            'mode'          => 'sandbox',
            'country_name'  => 'Denmark',
            'currency_name' => 'USD',
            'currency_rate' => 1.0000,
        ]);

        $cod = $gateways->get('cod') ?? PaymentSetting::create([
            'key'           => 'cod',
            'name'          => 'Cash On Delivery',
            'status'        => 'enable',
            'mode'          => 'live',
            'country_name'  => 'Denmark',
            'currency_name' => 'DKK',
            'currency_rate' => 1.0000,
        ]);

        $payoneer = $gateways->get('payoneer') ?? PaymentSetting::create([
            'key'           => 'payoneer',
            'name'          => 'Payoneer',
            'status'        => 'disable',
            'mode'          => 'sandbox',
            'country_name'  => 'United States',
            'currency_name' => 'USD',
            'currency_rate' => 1.0000,
        ]);

        $mobilePay = $gateways->get('mobile_pay') ?? PaymentSetting::create([
            'key'           => 'mobile_pay',
            'name'          => 'Mobile Pay',
            'status'        => 'disable',
            'mode'          => 'sandbox',
            'country_name'  => 'Denmark',
            'currency_name' => 'DKK',
            'currency_rate' => 1.0000,
        ]);

        $cashAccounts = ChartOfAccount::where('account_type', 'asset')
            ->orderBy('account_code')
            ->get();

        $currencies = Currency::where('status', true)->orderBy('is_base', 'desc')->orderBy('code')->get();

        // Currency-to-Country mapping for ERP supported currencies
        $currencyCountryMap = [
            'DKK' => 'Denmark',
            'USD' => 'United States',
            'EUR' => 'Germany',
            'GBP' => 'United Kingdom',
            'SEK' => 'Sweden',
            'NOK' => 'Norway',
            'BDT' => 'Bangladesh',
            'CNY' => 'China',
            'INR' => 'India',
            'AUD' => 'Australia',
        ];

        // Strictly show countries corresponding to the active currencies (currency-wise)
        $countries = [];
        foreach ($currencies as $curr) {
            $code = strtoupper(trim($curr->code));
            if (isset($currencyCountryMap[$code])) {
                $countries[] = $currencyCountryMap[$code];
            }
        }
        $countries = array_values(array_unique($countries));

        return view('backend.settings.payment_settings', compact('paypal', 'cod', 'payoneer', 'mobilePay', 'cashAccounts', 'countries', 'currencies', 'currencyCountryMap'));
    }

    /**
     * Update PayPal Settings.
     */
    public function updatePaypal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status'        => 'required|in:enable,disable',
            'mode'          => 'required|in:sandbox,live',
            'country_name'  => 'nullable|string|max:100',
            'currency_name' => 'nullable|string|max:100',
            'client_id'     => 'nullable|string|max:500',
            'client_secret' => 'nullable|string|max:500',
            'currency_rate' => 'nullable|numeric|min:0',
        ]);

        PaymentSetting::updateOrCreate(
            ['key' => 'paypal'],
            [
                'name'          => 'PayPal Express',
                'status'        => $validated['status'],
                'mode'          => $validated['mode'],
                'country_name'  => $validated['country_name'] ?? 'United States',
                'currency_name' => $validated['currency_name'] ?? 'United States Dollar',
                'client_id'     => $validated['client_id'] ?? null,
                'client_secret' => $validated['client_secret'] ?? null,
                'currency_rate' => $validated['currency_rate'] ?? 1.0000,
            ]
        );

        toastr()->success('PayPal Express settings updated successfully.');

        return redirect()->route('admin.payment-settings.index', ['tab' => 'paypal']);
    }

    /**
     * Test PayPal REST API Connection.
     */
    public function testPaypal(Request $request): \Illuminate\Http\JsonResponse
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');
        $mode = $request->input('mode', 'sandbox');

        $paypalService = app(\App\Services\Payment\PayPalService::class);

        $customCreds = null;
        if (!empty($clientId) && !empty($clientSecret)) {
            $customCreds = [
                'client_id'     => trim($clientId),
                'client_secret' => trim($clientSecret),
                'mode'          => $mode === 'live' ? 'live' : 'sandbox',
            ];
        }

        try {
            $token = $paypalService->getAccessToken($customCreds);
            if ($token) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection verified successfully! Authenticated with PayPal REST API in ' . strtoupper($mode) . ' mode.',
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to obtain access token from PayPal.',
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update Cash On Delivery (COD) Settings.
     */
    public function updateCod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status'             => 'required|in:enable,disable',
            'country_name'       => 'nullable|string|max:100',
            'currency_name'      => 'nullable|string|max:100',
            'currency_rate'      => 'nullable|numeric|min:0',
            'deposit_account_id' => 'nullable|exists:chart_of_accounts,id',
            'instructions'       => 'nullable|string|max:1000',
        ]);

        PaymentSetting::updateOrCreate(
            ['key' => 'cod'],
            [
                'name'               => 'Cash On Delivery',
                'status'             => $validated['status'],
                'mode'               => 'live',
                'country_name'       => $validated['country_name'] ?? 'Denmark',
                'currency_name'      => $validated['currency_name'] ?? 'Danish Krone',
                'currency_rate'      => $validated['currency_rate'] ?? 1.0000,
                'deposit_account_id' => $validated['deposit_account_id'] ?? null,
                'instructions'       => $validated['instructions'] ?? null,
            ]
        );

        toastr()->success('Cash On Delivery (COD) settings updated successfully.');

        return redirect()->route('admin.payment-settings.index', ['tab' => 'cod']);
    }

    /**
     * Update Payoneer Settings.
     */
    public function updatePayoneer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status'        => 'required|in:enable,disable',
            'mode'          => 'required|in:sandbox,live',
            'country_name'  => 'nullable|string|max:100',
            'currency_name' => 'nullable|string|max:100',
            'client_id'     => 'nullable|string|max:500',
            'client_secret' => 'nullable|string|max:500',
            'currency_rate' => 'nullable|numeric|min:0',
        ]);

        PaymentSetting::updateOrCreate(
            ['key' => 'payoneer'],
            [
                'name'          => 'Payoneer',
                'status'        => $validated['status'],
                'mode'          => $validated['mode'],
                'country_name'  => $validated['country_name'] ?? 'United States',
                'currency_name' => $validated['currency_name'] ?? 'United States Dollar',
                'client_id'     => $validated['client_id'] ?? null,
                'client_secret' => $validated['client_secret'] ?? null,
                'currency_rate' => $validated['currency_rate'] ?? 1.0000,
            ]
        );

        toastr()->success('Payoneer settings updated successfully.');

        return redirect()->route('admin.payment-settings.index', ['tab' => 'payoneer']);
    }

    /**
     * Update Mobile Pay Settings.
     */
    public function updateMobilePay(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status'        => 'required|in:enable,disable',
            'mode'          => 'required|in:sandbox,live',
            'country_name'  => 'nullable|string|max:100',
            'currency_name' => 'nullable|string|max:100',
            'client_id'     => 'nullable|string|max:500',
            'currency_rate' => 'nullable|numeric|min:0',
        ]);

        PaymentSetting::updateOrCreate(
            ['key' => 'mobile_pay'],
            [
                'name'          => 'Mobile Pay',
                'status'        => $validated['status'],
                'mode'          => $validated['mode'],
                'country_name'  => $validated['country_name'] ?? 'Denmark',
                'currency_name' => $validated['currency_name'] ?? 'Danish Krone',
                'client_id'     => $validated['client_id'] ?? null,
                'currency_rate' => $validated['currency_rate'] ?? 1.0000,
            ]
        );

        toastr()->success('Mobile Pay settings updated successfully.');

        return redirect()->route('admin.payment-settings.index', ['tab' => 'mobile_pay']);
    }
}
