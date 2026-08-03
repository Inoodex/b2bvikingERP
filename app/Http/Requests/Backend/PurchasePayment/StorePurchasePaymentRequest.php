<?php

namespace App\Http\Requests\Backend\PurchasePayment;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_bill_id' => ['nullable', 'exists:vendor_bills,id'],
            'purchase_id' => ['required', 'exists:purchases,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,cheque,lc_settlement,other'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'bank_name' => ['nullable', 'required_if:payment_method,bank_transfer,cheque', 'string', 'max:255'],
            'cheque_no' => ['nullable', 'required_if:payment_method,cheque', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'receipt' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_id.required' => 'Please select a Purchase Order.',
            'vendor_id.required' => 'Vendor selection is required.',
            'payment_date.required' => 'Payment date is required.',
            'payment_method.required' => 'Payment method must be selected.',
            'amount.gt' => 'Payment amount must be greater than zero.',
            'bank_name.required_if' => 'Bank name is required for bank transfer or cheque payments.',
            'cheque_no.required_if' => 'Cheque number is required for cheque payments.',
        ];
    }
}
