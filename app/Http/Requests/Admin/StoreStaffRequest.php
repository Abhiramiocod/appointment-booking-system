<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

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

            // Services to assign
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ];
    }
}
