<?php

namespace App\Http\Requests\Sales;

use App\Models\SalesInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('user_id') && !$this->has('customer_id')) {
            $this->merge([
                'customer_id' => $this->user_id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id'      => 'required|exists:users,id',
            'order_id'         => 'nullable|exists:orders,id',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'payment_date'     => 'required|date',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string|max:50',
            'transaction_id'   => 'nullable|string|max:100',
            'reference_no'     => 'nullable|string|max:100',
            'account_id'       => 'nullable|exists:chart_of_accounts,id',
            'bank_account_id'  => 'nullable|exists:bank_accounts,id',
            'allocations_json' => 'nullable|string',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->boolean('allow_advance') && $this->filled('sales_invoice_id') && $this->filled('amount')) {
                $invoice = SalesInvoice::find($this->sales_invoice_id);
                if ($invoice) {
                    $due = (float) $invoice->due_amount;
                    $paying = (float) $this->amount;
                    if ($paying > ($due + 0.01)) {
                        $validator->errors()->add('amount', "Payment amount (kr. {$paying}) exceeds outstanding invoice due (kr. {$due}). Overpayment is not permitted unless recorded as Customer Advance.");
                    }
                }
            }
        });
    }
}
