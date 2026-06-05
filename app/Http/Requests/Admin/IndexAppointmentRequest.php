<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'appointment_date' => ['nullable', 'date'],
            'sort_by' => ['nullable', 'in:appointment_date,created_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ];
    }
}
