<?php

declare(strict_types=1);

namespace App\Http\Requests\ChildCategory;

use App\Models\ChildCategory;
use Illuminate\Foundation\Http\FormRequest;

class ChildCategoryUpdateRequest extends FormRequest
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
        $childCategory = $this->route('child_category');
        $id = $childCategory instanceof ChildCategory ? $childCategory->id : $childCategory;

        return [
            'category' => ['required', 'integer', 'exists:categories,id'],
            'sub_category' => ['required', 'integer', 'exists:sub_categories,id'],
            'name' => ['required', 'string', 'max:255', 'unique:child_categories,name,' . $id],
            'status' => ['required', 'boolean'],
        ];
    }
}
