<?php

declare(strict_types=1);

namespace App\Http\Requests\Slider;

use Illuminate\Foundation\Http\FormRequest;

class SliderCreateRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starting_price' => ['nullable', 'numeric', 'min:0'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'serial' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'boolean'],
            'banner' => ['required', 'image', 'max:2048'],
        ];
    }
}
