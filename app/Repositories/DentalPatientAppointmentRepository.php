<?php

namespace App\Repositories;

use App\Models\DentalAppointment;
use App\Models\DentalAppointmentBilling;
use App\Models\DentalPatient;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DentalPatientAppointmentRepository
{
    public function permission()
    {
        $permissions = [
            'canView'=>true,
            'canUpdate'=>true, 
            'canCreate'=>true,
            'canDelete'=>true
        ];
        return $permissions;
    }

    public function index(array $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $filters = [
            'search' => (string) Arr::get($request, 'search', ''),
            'patient_name' => (string) Arr::get($request, 'patient_name', ''),
            'mobile_no' => (string) Arr::get($request, 'mobile_no', ''),
            'gender' => (string) Arr::get($request, 'gender', ''),
            'age' => (string) Arr::get($request, 'age', ''),
            'appointment_status' => (string) Arr::get($request, 'appointment_status', ''),
            'appointment_date' => (string) Arr::get($request, 'appointment_date', ''),
        ];

        $sortField = (string) Arr::get($request, 'sort_field', '');
        $sortOrder = (string) Arr::get($request, 'sort_order', '') === 'asc' ? 'asc' : 'desc';
        $perPage = max(1, min((int) Arr::get($request, 'per_page', 10), 100));
        $page = max(1, (int) Arr::get($request, 'page', 1));

        $patients = DentalPatient::query()
            ->with(['medicalHistory', 'latestAppointment.billing'])
            ->withMax('appointments', 'appointment_date')
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('mobile_no', 'like', "%{$search}%");
                });
            })
            ->when($filters['patient_name'], function ($query, $name) {
                $query->where(function ($subQuery) use ($name) {
                    $subQuery->where('first_name', 'like', "%{$name}%")
                             ->orWhere('last_name', 'like', "%{$name}%")
                             ->orWhere('middle_name', 'like', "%{$name}%");
                });
            })
            ->when($filters['mobile_no'], function ($query, $mobile) {
                $query->where('mobile_no', 'like', "%{$mobile}%");
            })
            ->when($filters['gender'], function ($query, $gender) {
                $query->where('gender', $gender);
            })
            ->when($filters['age'], function ($query, $age) {
                $query->where('age', $age);
            })
            ->when($filters['appointment_status'], function ($query, $status) {
                $query->whereHas('appointments', function ($appointmentQuery) use ($status) {
                    $appointmentQuery->where('status', $status);
                });
            })
            ->when($filters['appointment_date'], function ($query, $date) {
                $query->whereHas('appointments', function ($appointmentQuery) use ($date) {
                    $appointmentQuery->whereDate('appointment_date', Carbon::parse($date)->toDateString());
                });
            })
            ->when($sortField === 'patient_code', fn ($query) => $query->orderBy('patient_code', $sortOrder))
            ->when($sortField === 'mobile_no', fn ($query) => $query->orderBy('mobile_no', $sortOrder))
            ->when($sortField === 'gender', fn ($query) => $query->orderBy('gender', $sortOrder))
            ->when($sortField === 'age', fn ($query) => $query->orderBy('age', $sortOrder))
            ->when($sortField === 'latest_appointment_date', fn ($query) => $query->orderBy('appointments_max_appointment_date', $sortOrder))
            ->when(blank($sortField), function ($query) {
                $query
                    ->orderByDesc('appointments_max_appointment_date')
                    ->orderByDesc('id');
            })
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (DentalPatient $patient, int $index) use ($page, $perPage) {
                $latestAppointment = $patient->latestAppointment;

                return [
                    'id' => $patient->id,
                    'sequence' => (($page - 1) * $perPage) + $index + 1,
                    'patient_code' => $patient->patient_code,
                    'patient_name' => trim(collect([
                        $patient->first_name,
                        $patient->middle_name,
                        $patient->last_name,
                    ])->filter()->implode(' ')),
                    'mobile_no' => $patient->mobile_no,
                    'gender' => $patient->gender,
                    'age' => $patient->age,
                    'latest_appointment_date' => $latestAppointment?->appointment_date?->format('Y-m-d'),
                    'latest_appointment_status' => $latestAppointment?->status,
                ];
            });

        return $patients;
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            $patient = DentalPatient::create($data);

            $patient->update([
                'patient_code' => $this->generatePatientCode($patient->id),
            ]);

            $patient->medicalHistory()->create(
                $this->medicalHistoryPayload($data['medical_history'] ?? [])
            );

            $appointment = $patient->appointments()->create(
                $this->appointmentPayload($data['appointment'])
            );
            $appointment->update([
                'appointment_no' => $this->generateAppointmentNo($appointment->id),
            ]);


            $appointment->billing()->create(
                $this->billingPayload($data['billing'] ?? [])
            );

            return $patient;
        });
    }

    public function getShowData($id)
    {
        $patient = DentalPatient::find($id);
        $patient->load([
            'medicalHistory',
            'appointments' => fn ($query) => $query
                ->with('billing')
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->orderByDesc('id'),
        ]);

        return [
            'patient' => $this->patientProfilePayload($patient),
            'medicalHistory' => $this->medicalHistoryPayloadForView($patient->medicalHistory),
            'appointments' => $patient->appointments->map(
                fn (DentalAppointment $appointment) => $this->appointmentHistoryPayload($appointment)
            ),
        ];
    }

    public function getEditData(DentalPatient $patient): array
    {
        $patient->load(['medicalHistory', 'latestAppointment.billing']);

        return [
            'patient' => $this->patientFormPayload($patient),
        ];
    }

    public function update(array $data)
    {
        $patient = DentalPatient::find($data['id']);
        DB::transaction(function () use ($data, $patient) {
            $data['updated_by'] = auth()->id();
            $patient->update($data);

            $patient->medicalHistory()->updateOrCreate(
                ['patient_id' => $patient->id],
                $this->medicalHistoryPayload($data['medical_history'] ?? [])
            );

            $appointmentId = Arr::get($data, 'appointment.id');
            $appointment = $appointmentId
                ? $patient->appointments()->whereKey($appointmentId)->firstOrFail()
                : $patient->latestAppointment()->firstOrFail();

            $appointment->update(
                $this->appointmentPayload($data['appointment'])
            );

            $billingPayload = $this->billingPayload($data['billing'] ?? []);

            if ($appointment->billing) {
                $appointment->billing()->update($billingPayload);
            } else {
                $appointment->billing()->create($billingPayload);
            }
        });
    }

    public function delete($id)
    {
        $patient = DentalPatient::find($id);
        DB::transaction(function () use ($patient) {
            $patient->appointments()->get()->each->delete();
            $patient->delete();
        });
    }

    public function storeFollowUp(DentalPatient $patient, array $data): void
    {
        DB::transaction(function () use ($data, $patient) {
            $appointmentData = $this->appointmentPayload([
                ...$data['appointment'],
                'visit_type' => 'Follow-up',
            ]);

            $appointment = $patient->appointments()->create($appointmentData);

            $appointment->update([
                'appointment_no' => $this->generateAppointmentNo($appointment->id),
            ]);

            $appointment->billing()->create(
                $this->billingPayload($data['billing'] ?? [])
            );
        });
    }

    private function medicalHistoryPayload(array $medicalHistory): array
    {
        return [
            'blood_group' => $medicalHistory['blood_group'] ?? null,
            'diabetes' => (bool) ($medicalHistory['diabetes'] ?? false),
            'blood_pressure' => (bool) ($medicalHistory['blood_pressure'] ?? false),
            'heart_disease' => (bool) ($medicalHistory['heart_disease'] ?? false),
            'allergy' => (bool) ($medicalHistory['allergy'] ?? false),
            'allergy_details' => $medicalHistory['allergy_details'] ?? null,
            'current_medicine' => $medicalHistory['current_medicine'] ?? null,
            'previous_dental_treatment' => $medicalHistory['previous_dental_treatment'] ?? null,
            'pregnancy_status' => (bool) ($medicalHistory['pregnancy_status'] ?? false),
            'other_medical_notes' => $medicalHistory['other_medical_notes'] ?? null,
        ];
    }

    private function appointmentPayload(array $appointment): array
    {
        $payload = [
            'appointment_date' => $this->normalizeDate($appointment['appointment_date'] ?? null),
            'appointment_time' => $this->normalizeTime($appointment['appointment_time'] ?? null),
            'doctor_id' => $appointment['doctor_id'] ?? null,
            'visit_type' => $appointment['visit_type'],
            'appointment_type' => $appointment['appointment_type'] ?? null,
            'chief_complaint' => $appointment['chief_complaint'] ?? null,
            'problem_area' => $appointment['problem_area'] ?? null,
            'tooth_no' => $appointment['tooth_no'] ?? null,
            'priority' => $appointment['priority'],
            'status' => $appointment['status'],
            'notes' => $appointment['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

    return $payload;
    }

    private function billingPayload(array $billing): array
    {
        return [
            'consultation_fee' => $billing['consultation_fee'] ?? 0,
            'treatment_estimate' => $billing['treatment_estimate'] ?? 0,
            'discount' => $billing['discount'] ?? 0,
            'paid_amount' => $billing['paid_amount'] ?? 0,
            'payment_mode' => $billing['payment_mode'] ?? null,
            'payment_status' => $billing['payment_status'] ?? 'Pending',
            'remarks' => $billing['remarks'] ?? null,
        ];
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    private function normalizeTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('H:i:s');
    }

    private function generatePatientCode(int $id): string
    {
        return 'PAT-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    private function generateAppointmentNo(int $id): string
    {
        return 'APP-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    private function patientProfilePayload(DentalPatient $patient): array
    {
        return [
            'id' => $patient->id,
            'patient_code' => $patient->patient_code,
            'first_name' => $patient->first_name,
            'middle_name' => $patient->middle_name,
            'last_name' => $patient->last_name,
            'full_name' => trim(collect([$patient->first_name, $patient->middle_name, $patient->last_name])->filter()->implode(' ')),
            'gender' => $patient->gender,
            'date_of_birth' => $patient->date_of_birth?->format('Y-m-d'),
            'age' => $patient->age,
            'mobile_no' => $patient->mobile_no,
            'alternate_mobile_no' => $patient->alternate_mobile_no,
            'email' => $patient->email,
            'address' => $patient->address,
            'city' => $patient->city,
            'state' => $patient->state,
            'pincode' => $patient->pincode,
            'occupation' => $patient->occupation,
            'referred_by' => $patient->referred_by,
            'status' => $patient->status,
        ];
    }

    private function patientFormPayload(DentalPatient $patient): array
    {
        $latestAppointment = $patient->latestAppointment;
        $billing = $latestAppointment?->billing;

        return [
            ...$this->patientProfilePayload($patient),
            'medical_history' => $this->medicalHistoryPayloadForView($patient->medicalHistory),
            'appointment' => [
                'id' => $latestAppointment?->id,
                'appointment_no' => $latestAppointment?->appointment_no,
                'appointment_date' => $latestAppointment?->appointment_date?->format('Y-m-d'),
                'appointment_time' => $latestAppointment?->appointment_time,
                'doctor_id' => $latestAppointment?->doctor_id,
                'visit_type' => $latestAppointment?->visit_type ?? 'First Visit',
                'appointment_type' => $latestAppointment?->appointment_type,
                'chief_complaint' => $latestAppointment?->chief_complaint,
                'problem_area' => $latestAppointment?->problem_area,
                'tooth_no' => $latestAppointment?->tooth_no,
                'priority' => $latestAppointment?->priority ?? 'Normal',
                'status' => $latestAppointment?->status ?? 'Scheduled',
                'notes' => $latestAppointment?->notes,
            ],
            'billing' => [
                'consultation_fee' => $billing?->consultation_fee !== null ? (float) $billing->consultation_fee : 0,
                'treatment_estimate' => $billing?->treatment_estimate !== null ? (float) $billing->treatment_estimate : 0,
                'discount' => $billing?->discount !== null ? (float) $billing->discount : 0,
                'paid_amount' => $billing?->paid_amount !== null ? (float) $billing->paid_amount : 0,
                'payment_mode' => $billing?->payment_mode,
                'payment_status' => $billing?->payment_status ?? 'Pending',
                'remarks' => $billing?->remarks,
            ],
        ];
    }

    private function medicalHistoryPayloadForView(mixed $medicalHistory): array
    {
        return [
            'blood_group' => $medicalHistory?->blood_group,
            'diabetes' => (bool) ($medicalHistory?->diabetes ?? false),
            'blood_pressure' => (bool) ($medicalHistory?->blood_pressure ?? false),
            'heart_disease' => (bool) ($medicalHistory?->heart_disease ?? false),
            'allergy' => (bool) ($medicalHistory?->allergy ?? false),
            'allergy_details' => $medicalHistory?->allergy_details,
            'current_medicine' => $medicalHistory?->current_medicine,
            'previous_dental_treatment' => $medicalHistory?->previous_dental_treatment,
            'pregnancy_status' => (bool) ($medicalHistory?->pregnancy_status ?? false),
            'other_medical_notes' => $medicalHistory?->other_medical_notes,
        ];
    }

    private function appointmentHistoryPayload(DentalAppointment $appointment): array
    {
        /** @var DentalAppointmentBilling|null $billing */
        $billing = $appointment->billing;

        return [
            'id' => $appointment->id,
            'appointment_no' => $appointment->appointment_no,
            'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time,
            'doctor_id' => $appointment->doctor_id,
            'visit_type' => $appointment->visit_type,
            'appointment_type' => $appointment->appointment_type,
            'chief_complaint' => $appointment->chief_complaint,
            'problem_area' => $appointment->problem_area,
            'tooth_no' => $appointment->tooth_no,
            'priority' => $appointment->priority,
            'status' => $appointment->status,
            'notes' => $appointment->notes,
            'billing' => [
                'consultation_fee' => $billing?->consultation_fee !== null ? (float) $billing->consultation_fee : 0,
                'treatment_estimate' => $billing?->treatment_estimate !== null ? (float) $billing->treatment_estimate : 0,
                'discount' => $billing?->discount !== null ? (float) $billing->discount : 0,
                'paid_amount' => $billing?->paid_amount !== null ? (float) $billing->paid_amount : 0,
                'payment_mode' => $billing?->payment_mode,
                'payment_status' => $billing?->payment_status,
                'remarks' => $billing?->remarks,
            ],
        ];
    }
}
