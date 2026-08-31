<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_id' => 'required|exists:purchases,id',
            'outlet_id' => 'required|exists:outlets,id',
            'bin_id' => 'nullable|exists:warehouse_bins,id',
            'receipt_date' => 'required|date',
            'qc_status' => 'required|in:passed,conditionally_accepted,failed',
            'qc_remarks' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.bin_id' => 'nullable|exists:warehouse_bins,id',
            'items.*.received_qty' => 'required|numeric|min:0',
            'items.*.accepted_qty' => 'required|numeric|min:0',
            'items.*.rejected_qty' => 'nullable|numeric|min:0',
            'items.*.qc_pass' => 'nullable|boolean',
            'items.*.remarks' => 'nullable|string|max:255',
        ];
    }
}
