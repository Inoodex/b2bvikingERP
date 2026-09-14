<?php

declare(strict_types=1);

namespace App\Http\Requests\ProductType;

use Illuminate\Foundation\Http\FormRequest;

class ProductTypeToggleStatusRequest extends FormRequest
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
            'id' => ['required', 'integer', 'exists:product_types,id'],
            'status' => ['required'],
        ];
    }
}
