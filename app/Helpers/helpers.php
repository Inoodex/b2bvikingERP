<?php

if (!function_exists('setActive')) {
    /**
     * Set active class for sidebar menu.
     *
     * @param array $routes
     * @param string $class
     * @return string
     */
    function setActive(array $routes, string $class = 'active'): string
    {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return $class;
            }
        }

        return '';
    }
}

if (!function_exists('getSettings')) {
    function getSettings()
    {
        static $settings = null;
        if ($settings === null) {
            $dbSettings = null;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('general_settings')) {
                    $dbSettings = \App\Models\GeneralSetting::first();
                }
            } catch (\Throwable $e) {
                // Ignore DB error
            }

            if ($dbSettings) {
                $settings = $dbSettings;
                if (empty($settings->currency_icon)) {
                    $settings->currency_icon = 'DKK';
                }
                if (empty($settings->currency_name)) {
                    $settings->currency_name = 'DKK';
                }
                if (empty($settings->base_currency_icon)) {
                    $settings->base_currency_icon = $settings->currency_icon;
                }
                if (empty($settings->base_currency_name)) {
                    $settings->base_currency_name = $settings->currency_name;
                }
                if (empty($settings->site_name)) {
                    $settings->site_name = config('app.name', 'B2B Viking ERP');
                }
            } else {
                $settings = (object)[
                    'site_name' => config('app.name', 'B2B Viking ERP'),
                    'site_logo' => 'uploads/logo.png',
                    'base_currency_name' => 'Danish Krone',
                    'base_currency_icon' => 'kr.',
                    'currency_name' => 'Danish Krone',
                    'currency_icon' => 'kr.',
                    'currency_rate' => 1.0000,
                    'contact_email' => 'admin@b2bviking.com',
                    'address' => '',
                    'phone' => '',
                    'contact_phone' => '',
                ];
            }
        }
        return $settings;
    }
}

if (!function_exists('getConvertedAmount')) {
    /**
     * Internal helper to get amount in System currency.
     * Since System is Base, this usually returns the amount as-is.
     */
    function getConvertedAmount($amount)
    {
        return $amount;
    }
}

if (!function_exists('formatConverted')) {
    /**
     * Format amount using System Default settings.
     */
    function formatConverted($amount)
    {
        $settings = getSettings();
        return $settings->currency_icon . number_format($amount, 2);
    }
}

if (!function_exists('formatWithVendor')) {
    /**
     * Format amount using Vendor's specific currency.
     * System (Stored) -> Vendor (Display)
     * Conversion: Vendor = System / Rate
     */
    function formatWithVendor($amount, $icon, $rate)
    {
        // Prevent division by zero
        $rate = $rate > 0 ? $rate : 1;
        $converted = $amount / $rate;
        return $icon . number_format($converted, 2);
    }
}

if (!function_exists('formatWithCurrency')) {
    /**
     * Format amount with System currency.
     */
    function formatWithCurrency($amount)
    {
        return formatConverted($amount);
    }
}
