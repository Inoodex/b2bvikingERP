<?php

namespace App\Http\Requests\Backend\StockTransfer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'from_outlet_id' => 'required',
            'to_outlet_id' => 'required|different:from_outlet_id',
            'transfer_date' => 'required|date',
            'challan_no' => 'nullable|string|max:100',
            'vehicle_no' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:150',
            'driver_phone' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.item_note' => 'nullable|string|max:255',
        ];
    }
}
