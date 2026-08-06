<?php

namespace App\Http\Requests\Staffs\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class ProposeAppointmentTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposed_date' => ['required', 'date', 'after_or_equal:today'],
            'proposed_time' => ['required', 'string'],
            'proposed_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
