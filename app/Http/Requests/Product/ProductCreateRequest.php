<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductCreateRequest extends FormRequest
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
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'max:200', 'unique:products,name'],
            'category_id' => ['required', 'integer'],
            'sub_category_id' => ['nullable', 'integer'],
            'child_category_id' => ['nullable', 'integer'],
            'brand_id' => ['nullable', 'integer'],
            'unit_id' => ['nullable', 'integer'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'outlet_price' => ['nullable', 'numeric', 'min:0'],
            'long_description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
            'barcode' => ['nullable', 'string', 'max:200'],
            'raw_material_cost' => ['nullable', 'numeric', 'min:0'],
            'transport_cost' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_qty' => ['nullable', 'integer', 'min:1'],
            'discount_type' => ['nullable', 'in:flat,percent'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'vat_type' => ['nullable', 'in:flat,percent'],
            'vat_value' => ['nullable', 'numeric', 'min:0'],
            'qty' => ['nullable', 'numeric', 'min:0'],
            'variants' => ['nullable', 'array'],
            'variants.*.color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'variants.*.size_id' => ['nullable', 'integer', 'exists:sizes,id'],
            'variants.*.qty' => ['nullable', 'numeric', 'min:0'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.outlet_price' => ['nullable', 'numeric', 'min:0'],
            // qty removed from product create form
        ];
    }
}
