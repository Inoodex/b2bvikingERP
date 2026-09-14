<?php

declare(strict_types=1);

namespace App\Http\Requests\SubCategory;

use App\Models\SubCategory;
use Illuminate\Foundation\Http\FormRequest;

class SubCategoryUpdateRequest extends FormRequest
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
        $subCategory = $this->route('sub_category');
        $id = $subCategory instanceof SubCategory ? $subCategory->id : $subCategory;

        return [
            'category' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255', 'unique:sub_categories,name,' . $id],
            'status' => ['required', 'boolean'],
        ];
    }
}
