<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class CheckoutDiscountResolver
{
    private ?Discount $defaultDiscount = null;
    private bool $defaultDiscountLoaded = false;

    public function resolveForLine($product, float $lineSubtotal, int $quantity = 1): array
    {
        $lineSubtotal = max(0, $lineSubtotal);
        $quantity = max(1, $quantity);
        $currentSubtotal = $lineSubtotal;
        $discounts = [];
        $totalAmount = 0.0;

        // 1. Resolve Product Discount
        $productDiscount = $this->resolveProductDiscount($product);
        if ($productDiscount !== null) {
            $amount = $this->calculateAmount(
                $currentSubtotal,
                $productDiscount['type'],
                $productDiscount['value'],
                $quantity,
                true
            );
            $discounts[] = [
                'source' => 'product',
                'type' => $productDiscount['type'],
                'value' => $productDiscount['value'],
                'amount' => $amount,
            ];
            $totalAmount += $amount;
            $currentSubtotal = max(0, $currentSubtotal - $amount);
        }

        // 2. Resolve Default Discount (Only if no product discount was applied)
        if (empty($discounts)) {
            $defaultDiscount = $this->getDefaultDiscount();
            if ($defaultDiscount) {
                $type = $defaultDiscount->type === 'flat' ? 'flat' : 'percent';
                $value = max(0, (float) $defaultDiscount->value);
                $amount = $this->calculateAmount(
                    $currentSubtotal,
                    $type,
                    $value,
                    $quantity,
                    true
                );
                $discounts[] = [
                    'source' => 'default',
                    'type' => $type,
                    'value' => $value,
                    'amount' => $amount,
                ];
                $totalAmount += $amount;
            }
        }

        return [
            'amount' => round($totalAmount, 2),
            'discounts' => $discounts,
            // Main source/type for backward compatibility if needed
            'source' => count($discounts) > 1 ? 'mixed' : (isset($discounts[0]) ? $discounts[0]['source'] : 'none'),
            'type' => count($discounts) === 1 ? $discounts[0]['type'] : null,
            'value' => count($discounts) === 1 ? $discounts[0]['value'] : 0,
        ];
    }

    public function getDefaultDiscount(): ?Discount
    {
        if ($this->defaultDiscountLoaded) {
            return $this->defaultDiscount;
        }

        if (!Schema::hasTable('discounts')) {
            $this->defaultDiscountLoaded = true;
            $this->defaultDiscount = null;
            return null;
        }

        $this->defaultDiscount = Discount::query()
            ->where('is_default', true)
            ->where('status', true)
            ->first();

        $this->defaultDiscountLoaded = true;
        return $this->defaultDiscount;
    }

    private function resolveProductDiscount($product): ?array
    {
        if (!$product) {
            return null;
        }

        $type = strtolower(trim((string) data_get($product, 'discount_type', '')));
        $value = (float) data_get($product, 'discount', 0);

        // Backward compatibility: if type is missing but value exists, treat as percent.
        if ($type === '' && $value > 0) {
            $type = 'percent';
        }

        if (!in_array($type, ['flat', 'percent'], true)) {
            return null;
        }

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

    private function calculateAmount(
        float $lineSubtotal,
        string $type,
        float $value,
        int $quantity = 1,
        bool $flatPerUnit = false
    ): float
    {
        if ($lineSubtotal <= 0 || $value <= 0) {
            return 0.0;
        }

        if ($type === 'flat') {
            if ($flatPerUnit) {
                return round(min($lineSubtotal, $value * max(1, $quantity)), 2);
            }

            return round(min($lineSubtotal, $value), 2);
        }

        return round(($lineSubtotal * $value) / 100, 2);
    }
}
