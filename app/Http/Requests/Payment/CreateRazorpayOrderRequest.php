<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreateRazorpayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:100'],
            'currency' => ['nullable', 'string'],
            'receipt' => ['nullable', 'string'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ];
    }
}
