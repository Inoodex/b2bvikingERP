<?php

namespace App\Http\Requests\Backend\VendorReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplacementReceiveRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vendor_return_id'       => ['required', 'exists:vendor_returns,id'],
            'replacement_product_id' => ['required', 'exists:products,id'],
            'replacement_variant_id' => ['nullable', 'exists:product_variants,id'],
            'qty'                    => ['required', 'numeric', 'min:0.0001'],
            'receive_date'           => ['required', 'date'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ];
    }
}
