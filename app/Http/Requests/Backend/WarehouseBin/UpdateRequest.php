<?php

namespace App\Http\Requests\Backend\WarehouseBin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'zone_id' => 'required|exists:warehouse_zones,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ];
    }
}
