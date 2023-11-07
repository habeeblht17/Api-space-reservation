<?php

namespace App\Http\Requests\Admin\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'space_id' => 'required|exists:spaces,id',
            'plan_id' => 'required|exists:plans,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'interval_count' => 'required|integer|min:1',
        ];
    }
}
