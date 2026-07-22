<?php

namespace App\Http\Requests\SubCategory;

use Illuminate\Foundation\Http\FormRequest;

class SubCategoryUpdateRequest extends FormRequest
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
        $id=$this->route('sub_category');
        // dd($id);
        return [
            'category' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:255', 'unique:sub_categories,name,' . $id],
            'status' => ['required', 'boolean'],
        ];
    }
}
