<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorUpdateRequest extends FormRequest
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
        $vendor = $this->route('vendor');
        $id = is_object($vendor) ? $vendor->id : $vendor;

        return [
            'shop_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')->ignore($id)->whereNull('deleted_at'),
            ],
            'address' => ['required', 'string', 'max:500'],
            'country' => ['required', 'string', 'max:255'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:0,1'],
        ];
    }

    /**
     * Custom validation attribute names.
     */
    public function attributes(): array
    {
        return [
            'shop_name' => 'company name',
            'currency_id' => 'currency',
        ];
    }
}
