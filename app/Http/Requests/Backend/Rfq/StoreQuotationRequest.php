<?php

namespace App\Http\Requests\Backend\Rfq;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfq_id' => ['required', 'exists:rfqs,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'quotation_no' => ['nullable', 'string', 'max:50'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'delivery_terms' => ['nullable', 'string'],
            'validity_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
