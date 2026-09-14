<?php

declare(strict_types=1);

namespace App\Http\Requests\ProductType;

use Illuminate\Foundation\Http\FormRequest;

class ProductTypeCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200', 'unique:product_types,name'],
            'status' => ['required', 'boolean'],
        ];
    }
}
