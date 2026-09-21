<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class CategoryCreateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'gpc_code' => ['nullable', 'string', 'max:20'],
            'gpc_title' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'frontend_show' => ['nullable', 'boolean'],
        ];
    }
}
