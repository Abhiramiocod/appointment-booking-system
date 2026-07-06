<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i:s'],
            'end_time' => ['sometimes', 'date_format:H:i:s'],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'confirmed', 'completed', 'cancelled']),
            ],
            'notes' => ['nullable', 'string'],

            'customer_id' => [
                'sometimes',
                'exists:users,id',
            ],

            'staff_id' => [
                'sometimes',
                'exists:users,id',
            ],

            'service_id' => [
                'sometimes',
                'exists:services,id',
            ],
        ];
    }
}
