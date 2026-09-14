<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
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
        $category = $this->route('category');
        $id = $category instanceof Category ? $category->id : $category;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $id],
            'status' => ['required', 'boolean'],
            'frontend_show' => ['nullable', 'boolean'],
        ];
    }
}
