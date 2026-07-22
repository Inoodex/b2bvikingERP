<?php

namespace App\Services;

use App\Models\Tax;
use Illuminate\Support\Facades\Schema;

class CheckoutTaxResolver
{
    private ?Tax $defaultTax = null;
    private bool $defaultTaxLoaded = false;

    public function resolveForLine($product, float $lineSubtotal): array
    {
        $lineSubtotal = max(0, $lineSubtotal);

        $productTax = $this->resolveProductTax($product);
        if ($productTax !== null) {
            return [
                'source' => 'product',
                'type' => $productTax['type'],
                'value' => $productTax['value'],
                'amount' => $this->calculateAmount($lineSubtotal, $productTax['type'], $productTax['value']),
            ];
        }

        $defaultTax = $this->getDefaultTax();
        if ($defaultTax) {
            $type = $defaultTax->type === 'flat' ? 'flat' : 'percent';
            $value = max(0, (float) $defaultTax->value);

            return [
                'source' => 'default',
                'type' => $type,
                'value' => $value,
                'amount' => $this->calculateAmount($lineSubtotal, $type, $value),
            ];
        }

        return [
            'source' => 'none',
            'type' => null,
            'value' => 0.0,
            'amount' => 0.0,
        ];
    }

    public function getDefaultTax(): ?Tax
    {
        if ($this->defaultTaxLoaded) {
            return $this->defaultTax;
        }

        if (!Schema::hasTable('taxes')) {
            $this->defaultTaxLoaded = true;
            $this->defaultTax = null;
            return null;
        }

        $this->defaultTax = Tax::query()
            ->where('is_default', true)
            ->where('status', true)
            ->first();

        $this->defaultTaxLoaded = true;
        return $this->defaultTax;
    }

    private function resolveProductTax($product): ?array
    {
        if (!$product) {
            return null;
        }

        // Future-ready: when product VAT fields are added, resolver will use them automatically.
        $type = data_get($product, 'vat_type');
        $value = data_get($product, 'vat_value');

        if (($type === null || $type === '') && ($value === null || $value === '')) {
            $type = data_get($product, 'tax_type');
            $value = data_get($product, 'tax_value');
        }

        if ($type === null || $type === '' || $value === null || $value === '') {
            return null;
        }

        $type = strtolower((string) $type);
        if (!in_array($type, ['flat', 'percent'], true)) {
            return null;
        }

        $value = (float) $value;
        if ($value <= 0) {
            return null;
        }

        if ($type === 'percent' && $value > 100) {
            $value = 100.0;
        }

        return [
            'type' => $type,
            'value' => $value,
        ];
    }

    private function calculateAmount(float $lineSubtotal, string $type, float $value): float
    {
        if ($lineSubtotal <= 0 || $value <= 0) {
            return 0.0;
        }

        if ($type === 'flat') {
            return round($value, 2);
        }

        return round(($lineSubtotal * $value) / 100, 2);
    }
}
