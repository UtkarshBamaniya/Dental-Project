<?php

namespace App\Http\Requests\DentalClinic;

use App\Models\DentalPatient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class DentalPatientAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request): array
    {
        /** @var DentalPatient|null $patient */
        $patient = $this->route('patient');

        $rules = [
            'appointment' => ['required', 'array'],
            'appointment.appointment_date' => ['required', 'date'],
            'appointment.appointment_time' => ['required'],
            'appointment.visit_type' => ['required', Rule::in(['First Visit', 'Follow-up'])],
            'appointment.priority' => ['required', Rule::in(['Normal', 'Urgent', 'Emergency'])],
            'appointment.status' => ['required', Rule::in(['Scheduled', 'Completed', 'Cancelled', 'Rescheduled', 'No Show'])],
            
            'billing.payment_status' => ['required', Rule::in(['Pending', 'Partial', 'Paid'])],
        ];

        // If it's follow-up, only appointment and billing rules are needed
        if ($this->route()->getActionMethod() === 'storeFollowUp') {
            return $rules;
        }

        // Add patient fields for store/update
        $patientRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'mobile_no' => ['required', 'string', 'max:15'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];

        $rules = array_merge($rules, $patientRules);

        return $rules;
    }
}
