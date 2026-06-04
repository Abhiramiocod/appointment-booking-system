<?php

namespace App\Http\Requests\Staffs;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffProfileRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:20'],
            'bio' => ['nullable', 'string'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'profile_image' => ['nullable', 'string'],
        ];
    }
}
