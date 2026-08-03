<?php

namespace App\Http\Requests\Backend\VendorBill;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'exists:purchases,id'],
            'goods_receipt_id' => ['nullable', 'exists:goods_receipts,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.landed_cost' => ['nullable', 'numeric', 'min:0'],
            'apply_debit_notes' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_id.required' => 'The purchase order selection is required.',
            'bill_date.required' => 'The bill date is required.',
            'items.required' => 'At least one line item is required to generate a vendor bill.',
            'items.*.qty.gt' => 'Item quantity must be greater than zero.',
        ];
    }
}
