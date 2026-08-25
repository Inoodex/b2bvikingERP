<?php

namespace App\Http\Requests\Backend\WarehouseZone;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
            'type' => 'required|in:active,quarantine,scrap',
            'status' => 'nullable|in:0,1',
        ];
    }
}
