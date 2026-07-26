<?php

namespace App\Http\Requests\Backend\Company;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'currency_id' => 'nullable|exists:currencies,id',
            'status' => 'required|boolean',
        ];
    }
}
