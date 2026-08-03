<?php

namespace App\Http\Requests\Backend\VendorReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRefundRequest extends FormRequest
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
            'vendor_return_id' => ['required', 'exists:vendor_returns,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'refund_date'      => ['required', 'date'],
            'payment_method'   => ['required', 'in:bank_transfer,cash,cheque,lc_refund'],
            'bank_name'        => ['nullable', 'string', 'max:255'],
            'cheque_no'        => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
