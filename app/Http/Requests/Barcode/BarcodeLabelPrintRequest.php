<?php

declare(strict_types=1);

namespace App\Http\Requests\Barcode;

use Illuminate\Foundation\Http\FormRequest;

class BarcodeLabelPrintRequest extends FormRequest
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
            'preset' => ['required', 'string'],
            'print_mode' => ['nullable', 'string'],
            'items' => ['required'], // can be JSON string or array
            'show_price' => ['nullable', 'boolean'],
            'show_name' => ['nullable', 'boolean'],
            'show_sku' => ['nullable', 'boolean'],
            'show_brand' => ['nullable', 'boolean'],
            'show_barcode_text' => ['nullable', 'boolean'],
            'show_variant_spec' => ['nullable', 'boolean'],
        ];
    }
}
