<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $id = $this->route('appointment_type');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('appointment_types', 'name')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
