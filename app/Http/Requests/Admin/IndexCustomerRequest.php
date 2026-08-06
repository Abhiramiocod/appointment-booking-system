<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'min_bookings' => ['nullable', 'integer', 'min:0'],
            'sort_by' => [
                'nullable',
                Rule::in([
                    'name',
                    'created_at',
                    'bookings_count',
                ]),
            ],
        ];
    }
}
