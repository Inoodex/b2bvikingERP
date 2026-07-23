<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('company');
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,' . $id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'currency_id' => 'nullable|exists:currencies,id',
            'status' => 'required|boolean',
        ];
    }
}
