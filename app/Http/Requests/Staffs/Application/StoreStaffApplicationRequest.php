<?php

namespace App\Http\Requests\Staffs\Application;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'designation_id' => ['required', 'integer', 'exists:designations,id'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'cover_letter' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
