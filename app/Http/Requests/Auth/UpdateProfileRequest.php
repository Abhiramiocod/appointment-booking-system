<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $user = $this->user();

        $emailRules = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
        if (in_array($user->provider?->value ?? $user->provider, ['google', 'microsoft'])) {
            $emailRules = ['nullable', 'string', 'email', 'max:255'];
        }
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => $emailRules,
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_avatar' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            // Customer fields
            'preferred_contact_method' => ['nullable', 'string', 'max:50'],

            'emergency_contact' => ['nullable', 'string', 'max:100'],

            'medical_notes' => ['nullable', 'string', 'max:1000'],

            'preferred_language' => ['nullable', 'string', 'max:50'],
        ];

        // Staff-only fields
        if ($user->isStaff()) {
            $rules = array_merge($rules, [
                'designation_id' => ['nullable', 'exists:designations,id'],

                'experience_years' => ['nullable', 'integer', 'min:0'],

                'specialization' => ['nullable', 'string', 'max:255'],

                'license_number' => ['nullable', 'string', 'max:100'],

                'working_since' => ['nullable', 'date'],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes and underscores.',
            'avatar.max' => 'Avatar size must not exceed 5 MB.',
            'email.unique' => 'This email is already in use.',
            'username.unique' => 'This username is already taken.',
        ];
    }
}
