<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('staff')),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

            // Services to assign
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Staff profile fields
            'phone' => ['nullable', 'string', 'max:30'],

            'designation_id' => ['nullable', 'integer', 'exists:designations,id'],

            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],

            'employment_status' => [
                'nullable',
                'string',
                'in:active,inactive,on_leave,terminated,suspended',
            ],
        ];
    }
}
