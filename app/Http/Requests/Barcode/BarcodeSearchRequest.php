<?php

declare(strict_types=1);

namespace App\Http\Requests\Barcode;

use Illuminate\Foundation\Http\FormRequest;

class BarcodeSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'limit' => ['nullable', 'integer', 'min:0', 'max:2500'],
        ];
    }
}
