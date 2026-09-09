<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeatureToggleController extends Controller
{
    /**
     * Standard definitions and default values for all enterprise feature toggles.
     */
    public const AVAILABLE_TOGGLES = [
        'feature_auto_replenish' => [
            'title'       => 'Inventory Auto-Replenishment',
            'category'    => 'Procurement & Inventory',
            'icon'        => 'fas fa-boxes',
            'description' => 'Automatically create draft Purchase Orders when warehouse inventory dips below reorder threshold.',
            'default'     => true,
        ],
        'feature_vendor_notification_emails' => [
            'title'       => 'Vendor Notification Emails',
            'category'    => 'Communications',
            'icon'        => 'fas fa-envelope-open-text',
            'description' => 'Automatically dispatch RFQ and Purchase Order PDFs to vendor primary email upon approval.',
            'default'     => true,
        ],
        'feature_customer_credit_limit_lock' => [
            'title'       => 'B2B Customer Credit Limit Lock',
            'category'    => 'Sales & Credit Control',
            'icon'        => 'fas fa-hand-holding-usd',
            'description' => 'Halt new sales orders and dispatches if a customer exceeds their sanctioned credit limit.',
            'default'     => true,
        ],
        'feature_auto_gl_posting' => [
            'title'       => 'Automated General Ledger Posting',
            'category'    => 'Finance & Accounting',
            'icon'        => 'fas fa-book',
            'description' => 'Instantly generate double-entry Journal Vouchers upon invoice posting and payment receipt.',
            'default'     => true,
        ],
        'feature_strict_fiscal_period_lock' => [
            'title'       => 'Strict Fiscal Year & Period Lock',
            'category'    => 'Finance & Accounting',
            'icon'        => 'fas fa-calendar-check',
            'description' => 'Block any transactions or backdated postings into closed fiscal quarters and years.',
            'default'     => true,
        ],
        'feature_stock_batch_tracking' => [
            'title'       => 'Batch & Expiry Date Tracking',
            'category'    => 'Procurement & Inventory',
            'icon'        => 'fas fa-calendar-alt',
            'description' => 'Enforce lot/batch numbers and expiry validation during Goods Receipt (GRN) and picking.',
            'default'     => true,
        ],
        'feature_paypal_checkout' => [
            'title'       => 'PayPal Express Checkout',
            'category'    => 'Payment Gateways',
            'icon'        => 'fab fa-paypal',
            'description' => 'Allow customers to pay outstanding sales invoices online using PayPal Express v2.',
            'default'     => true,
        ],
        'feature_cod_checkout' => [
            'title'       => 'Cash On Delivery (COD)',
            'category'    => 'Payment Gateways',
            'icon'        => 'fas fa-money-bill-wave',
            'description' => 'Enable Cash On Delivery workflow with driver dispatch and cashier physical handover.',
            'default'     => true,
        ],
    ];

    /**
     * Display Feature Toggles Switchboard.
     */
    public function index()
    {
        $setting = GeneralSetting::first();
        $storedToggles = $setting?->feature_toggles ?? [];

        // Build active state mapping with defaults
        $toggles = [];
        foreach (self::AVAILABLE_TOGGLES as $key => $meta) {
            $toggles[$key] = array_merge($meta, [
                'key'     => $key,
                'enabled' => isset($storedToggles[$key]) ? (bool)$storedToggles[$key] : $meta['default'],
            ]);
        }

        // Group by category for clear UI presentation
        $groupedToggles = collect($toggles)->groupBy('category');

        return view('backend.settings.feature_toggles', compact('groupedToggles', 'storedToggles'));
    }

    /**
     * Handle single toggle switch change via AJAX.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => ['required', 'string'],
            'enabled' => ['required', 'boolean'],
        ]);

        $featureKey = $request->input('feature');
        $isEnabled  = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);

        if (!array_key_exists($featureKey, self::AVAILABLE_TOGGLES)) {
            return response()->json([
                'success' => false,
                'message' => "Unrecognized feature toggle key: {$featureKey}",
            ], 422);
        }

        $setting = GeneralSetting::first() ?: GeneralSetting::create(['id' => 1]);
        $currentToggles = is_array($setting->feature_toggles) ? $setting->feature_toggles : [];
        $currentToggles[$featureKey] = $isEnabled;

        $setting->feature_toggles = $currentToggles;

        if ($featureKey === 'feature_paypal_checkout') {
            $setting->paypal_enabled = $isEnabled;
        }
        if ($featureKey === 'feature_cod_checkout') {
            $setting->cod_enabled = $isEnabled;
        }

        $setting->save();

        // Clear 24-hr high-speed cache
        Cache::forget('system_feature_toggles');

        $title = self::AVAILABLE_TOGGLES[$featureKey]['title'];
        $statusText = $isEnabled ? 'enabled' : 'disabled';

        return response()->json([
            'success' => true,
            'message' => "{$title} has been {$statusText}.",
            'feature' => $featureKey,
            'enabled' => $isEnabled,
        ]);
    }

    /**
     * Bulk save all toggles from standard form submission.
     */
    public function updateAll(Request $request)
    {
        $setting = GeneralSetting::firstOrCreate(['id' => 1]);
        $submitted = $request->input('toggles', []);

        $newToggles = [];
        foreach (self::AVAILABLE_TOGGLES as $key => $meta) {
            $isEnabled = !empty($submitted[$key]);
            $newToggles[$key] = $isEnabled;
        }

        $updates = [
            'feature_toggles' => json_encode($newToggles),
            'paypal_enabled'  => $newToggles['feature_paypal_checkout'] ?? false,
            'cod_enabled'     => $newToggles['feature_cod_checkout'] ?? false,
        ];

        GeneralSetting::where('id', $setting->id)->update($updates);

        Cache::forget('system_feature_toggles');

        Toastr::success('System feature toggles updated successfully!');
        return redirect()->route('admin.settings.feature-toggles');
    }
}
