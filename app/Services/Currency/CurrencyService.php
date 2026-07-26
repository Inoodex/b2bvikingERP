<?php

namespace App\Services\Currency;

use App\Models\Currency;

class CurrencyService
{
    public function createCurrency(array $data)
    {
        $isBase = (bool) ($data['is_base'] ?? false);

        if ($isBase) {
            Currency::query()->update(['is_base' => false]);
            $data['exchange_rate'] = 1.0000;
        }

        return Currency::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'exchange_rate' => $data['exchange_rate'],
            'is_base' => $isBase,
            'status' => $data['status'],
        ]);
    }

    public function updateCurrency(Currency $currency, array $data)
    {
        $isBase = (bool) ($data['is_base'] ?? false);

        if ($isBase) {
            Currency::query()->where('id', '!=', $currency->id)->update(['is_base' => false]);
            $data['exchange_rate'] = 1.0000;
        }

        return $currency->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'exchange_rate' => $data['exchange_rate'],
            'is_base' => $isBase,
            'status' => $data['status'],
        ]);
    }

    public function deleteCurrency(Currency $currency)
    {
        if ($currency->is_base) {
            throw new \Exception('Cannot delete base currency!');
        }
        return $currency->delete();
    }
}
