<?php

namespace App\Http\Requests\ChildCategory;

use Illuminate\Foundation\Http\FormRequest;

class ChildCategoryUpdateRequest extends FormRequest
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
        $id=$this->route('child_category');
        return [
              'category' => ['required', 'exists:categories,id'],
            'sub_category' => ['required', 'exists:sub_categories,id'],
            'name' => ['required', 'max:255', 'unique:child_categories,name,' . $id],
            'status' => ['required', 'boolean'],
        ];
    }
}
