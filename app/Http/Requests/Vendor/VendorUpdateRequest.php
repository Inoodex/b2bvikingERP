<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

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
        $id = $this->route('vendor');
        return [
            'shop_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email:filter|max:255|unique:vendors,email,' . $id,
            'address' => 'required|string|max:500',
            'country' => 'required|string|max:255',
            'currency_name' => 'required|string|max:20',
            'currency_icon' => 'required|string|max:10',
            'currency_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ];
    }
}
