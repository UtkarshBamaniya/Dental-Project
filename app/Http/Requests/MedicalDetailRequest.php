<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicalDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $id = $this->route('medical_detail');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('medical_details', 'name')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
