<?php

namespace App\Http\Requests\Backend\Rfq;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfq_no' => ['required', 'string', 'max:50', Rule::unique('rfqs', 'rfq_no')],
            'source_type' => ['nullable', 'string', 'in:App\Models\Order,App\Models\CustomProductRequest,App\Models\ProductRequest'],
            'source_id' => ['nullable', 'integer'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'vendors' => ['required', 'array', 'min:1'],
            'vendors.*' => ['exists:vendors,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
