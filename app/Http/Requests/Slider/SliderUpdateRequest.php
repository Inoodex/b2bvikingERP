<?php

namespace App\Http\Requests\Slider;

use Illuminate\Foundation\Http\FormRequest;

class SliderUpdateRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'serial' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'boolean'],
            'banner' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
