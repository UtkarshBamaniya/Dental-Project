<?php

namespace App\Http\Requests\DentalClinic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DentalPatientAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $actionMethod = $this->route()?->getActionMethod();
        $entity = $this->query('entity');

        if ($actionMethod === 'update' && $entity === 'appointment') {
            return array_merge($this->appointmentRules(), $this->billingRules());
        }

        if ($actionMethod === 'update' && $entity === 'medical-history') {
            return $this->medicalHistoryRules();
        }

        return match ($actionMethod) {
            'store' => array_merge(
                $this->patientRules(),
                $this->medicalHistoryRules(),
                $this->appointmentRules(),
                $this->billingRules()
            ),
            'update' => $this->patientRules(),
            'storeFollowUp', 'updateAppointment' => array_merge(
                $this->appointmentRules(),
                $this->billingRules()
            ),
            'updateMedicalHistory' => $this->medicalHistoryRules(),
            default => [],
        };
    }

    private function patientRules(): array
    {
        return [
            'patient_code' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'date_of_birth' => ['nullable', 'date'],
            'age' => ['nullable', 'numeric', 'min:0'],
            'mobile_no' => ['required', 'string', 'max:15'],
            'alternate_mobile_no' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'referred_by' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }

    private function medicalHistoryRules(): array
    {
        return [
            'medical_history' => ['sometimes', 'array'],
            'medical_history.blood_group' => ['nullable', 'string', 'max:20'],
            'medical_history.medical_id' => ['nullable', 'array'],
            'medical_history.medical_id.*' => ['integer', 'exists:medical_details,id'],
            'medical_history.allergy_details' => ['nullable', 'string'],
            'medical_history.current_medicine' => ['nullable', 'string'],
            'medical_history.previous_dental_treatment' => ['nullable', 'string'],
            'medical_history.other_medical_notes' => ['nullable', 'string'],
        ];
    }

    private function appointmentRules(): array
    {
        return [
            'appointment' => ['required', 'array'],
            'appointment.appointment_date' => ['required', 'date'],
            'appointment.appointment_time' => ['required'],
            'appointment.doctor_id' => ['nullable', 'numeric'],
            'appointment.visit_type' => ['required', Rule::in(['First Visit', 'Follow-up'])],
            'appointment.appointment_type_id' => ['nullable', 'integer', 'exists:appointment_types,id'],
            'appointment.priority' => ['required', Rule::in(['Normal', 'Urgent', 'Emergency'])],
            'appointment.status' => ['required', Rule::in(['Scheduled', 'Completed', 'Cancelled', 'Rescheduled', 'No Show'])],
            'appointment.chief_complaint' => ['nullable', 'string'],
            'appointment.problem_area' => ['nullable', 'string', 'max:255'],
            'appointment.tooth_no' => ['nullable', 'string', 'max:255'],
            'appointment.notes' => ['nullable', 'string'],
        ];
    }

    private function billingRules(): array
    {
        return [
            'billing' => ['sometimes', 'array'],
            'billing.consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'billing.treatment_estimate' => ['nullable', 'numeric', 'min:0'],
            'billing.discount' => ['nullable', 'numeric', 'min:0'],
            'billing.paid_amount' => ['nullable', 'numeric', 'min:0'],
            'billing.payment_mode' => ['nullable', Rule::in(['Cash', 'UPI', 'Card', 'Bank Transfer'])],
            'billing.payment_status' => ['required', Rule::in(['Pending', 'Partial', 'Paid'])],
            'billing.remarks' => ['nullable', 'string'],
        ];
    }
}
