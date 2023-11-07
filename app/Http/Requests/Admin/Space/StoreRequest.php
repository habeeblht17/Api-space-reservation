<?php

namespace App\Http\Requests\Admin\Space;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|uuid|exists:categories,id',
            'title' => 'required|string|min:4',
            'description' => 'required|string|min:10',
            'rate_per_unit' => 'required|numeric',
            'capacity' => 'required|integer',
            'measurement' => 'required|string|min:3',
            'status' => 'required|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
        ];
    }
}
