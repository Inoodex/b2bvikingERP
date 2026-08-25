<?php

namespace App\Http\Requests\Backend\StockAdjustment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'outlet_id' => 'required',
            'adjustment_type' => 'required|in:increase,decrease,reconciliation',
            'reason_code' => 'required|in:damage,physical_count,expired,sample_marketing,theft_loss,internal_use,other',
            'reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable',
            'items.*.adjusted_qty' => 'required|numeric|gt:0',
            'items.*.counted_qty' => 'nullable|numeric',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.item_note' => 'nullable|string|max:255',
        ];
    }
}
