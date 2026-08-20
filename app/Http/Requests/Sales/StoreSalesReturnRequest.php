<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'outlet_id' => 'required|exists:outlets,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.return_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.condition' => 'nullable|in:good,damaged,expired',
            'items.*.restock' => 'nullable|boolean',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }
}
