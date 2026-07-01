<?php

namespace App\Repositories;

use App\Models\DentalAppointment;
use App\Models\DentalAppointmentBilling;
use App\Models\MedicalDetail;
use App\Models\DentalPatient;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DentalPatientAppointmentRepository
{
    public function permission(): array
    {
        return [
            'canView' => true,
            'canUpdate' => true,
            'canCreate' => true,
            'canDelete' => true,
        ];
    }

    public function index(array $request): LengthAwarePaginator
    {
        $filters = [
            'search' => (string) Arr::get($request, 'search', ''),
            'patient_code' => (string) Arr::get($request, 'patient_code', ''),
            'patient_name' => (string) Arr::get($request, 'patient_name', ''),
            'mobile_no' => (string) Arr::get($request, 'mobile_no', ''),
            'gender' => (string) Arr::get($request, 'gender', ''),
            'appointment_status' => (string) Arr::get($request, 'appointment_status', ''),
            'appointment_date' => (string) Arr::get($request, 'appointment_date', ''),
        ];

        $sortField = (string) Arr::get($request, 'sort_field', Arr::get($request, 'sortField', ''));
        $sortOrder = (string) Arr::get($request, 'sort_order', Arr::get($request, 'sortOrder', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = max(1, min((int) Arr::get($request, 'per_page', Arr::get($request, 'size', 10)), 100));
        $page = max(1, (int) Arr::get($request, 'page', 1));

        return DentalPatient::query()
            ->with(['medicalHistory', 'latestAppointment.billing', 'latestAppointment.appointmentType'])
            ->withMax('appointments', 'appointment_date')
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('mobile_no', 'like', "%{$search}%");
                });
            })
            ->when($filters['patient_code'], fn ($query, $code) => $query->where('patient_code', 'like', "%{$code}%"))
            ->when($filters['patient_name'], function ($query, $name) {
                $query->where(function ($subQuery) use ($name) {
                    $subQuery
                        ->where('first_name', 'like', "%{$name}%")
                        ->orWhere('middle_name', 'like', "%{$name}%")
                        ->orWhere('last_name', 'like', "%{$name}%");
                });
            })
            ->when($filters['mobile_no'], fn ($query, $mobile) => $query->where('mobile_no', 'like', "%{$mobile}%"))
            ->when($filters['gender'], fn ($query, $gender) => $query->where('gender', $gender))
            ->when($filters['appointment_status'], function ($query, $status) {
                $query->whereHas('latestAppointment', fn ($appointmentQuery) => $appointmentQuery->where('status', $status));
            })
            ->when($filters['appointment_date'], function ($query, $date) {
                $query->whereHas('latestAppointment', function ($appointmentQuery) use ($date) {
                    $appointmentQuery->whereDate('appointment_date', Carbon::parse($date)->toDateString());
                });
            })
            ->when($sortField === 'patient_code', fn ($query) => $query->orderBy('patient_code', $sortOrder))
            ->when($sortField === 'patient_name', fn ($query) => $query->orderBy('first_name', $sortOrder)->orderBy('last_name', $sortOrder))
            ->when($sortField === 'mobile_no', fn ($query) => $query->orderBy('mobile_no', $sortOrder))
            ->when($sortField === 'gender', fn ($query) => $query->orderBy('gender', $sortOrder))
            ->when($sortField === 'age', fn ($query) => $query->orderBy('age', $sortOrder))
            ->when(in_array($sortField, ['appointment_date', 'latest_appointment_date'], true), fn ($query) => $query->orderBy('appointments_max_appointment_date', $sortOrder))
            ->when(blank($sortField), function ($query) {
                $query
                    ->orderByDesc('appointments_max_appointment_date')
                    ->orderByDesc('id');
            })
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString()
            ->through(function (DentalPatient $patient, int $index) use ($page, $perPage) {
                $latestAppointment = $patient->latestAppointment;

                return [
                    'id' => $patient->id,
                    'sequence' => (($page - 1) * $perPage) + $index + 1,
                    'patient_code' => $patient->patient_code,
                    'patient_name' => $this->patientFullName($patient),
                    'mobile_no' => $patient->mobile_no,
                    'gender' => $patient->gender,
                    'age' => $patient->age,
                    'latest_appointment_date' => $latestAppointment?->appointment_date?->format('Y-m-d'),
                    'latest_appointment_status' => $latestAppointment?->status,
                    'latest_appointment_id' => $latestAppointment?->id,
                ];
            });
    }

    public function create(array $data): DentalPatient
    {
        return DB::transaction(function () use ($data) {
            $patient = DentalPatient::create([
                ...$this->patientPayload($data),
                'patient_code' => Arr::get($data, 'patient_code') ?: null,
                'created_by' => auth()->id() ?? 0,
                'updated_by' => auth()->id() ?? 0,
            ]);

            if (blank($patient->patient_code)) {
                $patient->update([
                    'patient_code' => $this->generatePatientCode($patient->id),
                ]);
            }

            $patient->medicalHistory()->create(
                $this->medicalHistoryPayload(Arr::get($data, 'medical_history', []))
            );

            $appointment = $patient->appointments()->create([
                ...$this->appointmentPayload(Arr::get($data, 'appointment', [])),
                'created_by' => auth()->id() ?? 0,
                'updated_by' => auth()->id() ?? 0,
            ]);

            $appointment->update([
                'appointment_no' => $this->generateAppointmentNo($appointment->id),
            ]);

            $appointment->billing()->create(
                $this->billingPayload(Arr::get($data, 'billing', []))
            );

            return $patient->fresh(['medicalHistory', 'latestAppointment.billing']);
        });
    }

    public function getEditPatientData(DentalPatient $patient): array
    {
        $patient->load('medicalHistory');

        return [
            'patient' => $this->patientFormPayload($patient),
            'medicalHistory' => $this->medicalHistoryPayloadForView($patient->medicalHistory),
        ];
    }

    public function updatePatient(DentalPatient $patient, array $data): DentalPatient
    {
        return DB::transaction(function () use ($patient, $data) {
            $patient->update([
                ...$this->patientPayload($data),
                'updated_by' => auth()->id() ?? 0,
            ]);

            if (blank($patient->patient_code) && blank(Arr::get($data, 'patient_code'))) {
                $patient->update([
                    'patient_code' => $this->generatePatientCode($patient->id),
                ]);
            }

            return $patient->fresh('medicalHistory');
        });
    }

    public function delete(int|string $id): void
    {
        /** @var DentalPatient $patient */
        $patient = DentalPatient::query()
            ->with('appointments.billing')
            ->findOrFail($id);

        DB::transaction(function () use ($patient) {
            foreach ($patient->appointments as $appointment) {
                $appointment->billing()?->delete();
                $appointment->delete();
            }

            $patient->medicalHistory()?->delete();
            $patient->delete();
        });
    }

    public function getShowPageData(DentalPatient $patient): array
    {
        $patient->load([
            'medicalHistory',
            'appointments' => fn ($query) => $query
                ->with(['billing', 'appointmentType'])
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->orderByDesc('id'),
            'latestAppointment.billing',
        ]);

        return [
            'patient' => $this->patientProfilePayload($patient),
            'medicalHistory' => $this->medicalHistoryPayloadForView($patient->medicalHistory),
            'summary' => $this->appointmentSummaryPayload($patient->appointments),
            'appointments' => $patient->appointments->map(
                fn (DentalAppointment $appointment) => $this->appointmentHistoryPayload($appointment)
            )->values(),
            'sidebar' => $this->sidebarPayload(),
            'permissions' => $this->permission(),
            'title' => 'Patient Profile',
            'desc' => 'Patient summary, medical history, appointments, and billing details.',
        ];
    }

    public function storeFollowUp(DentalPatient $patient, array $data): DentalAppointment
    {
        return DB::transaction(function () use ($patient, $data) {
            $appointment = $patient->appointments()->create([
                ...$this->appointmentPayload([
                    ...Arr::get($data, 'appointment', []),
                    'visit_type' => 'Follow-up',
                ]),
                'created_by' => auth()->id() ?? 0,
                'updated_by' => auth()->id() ?? 0,
            ]);

            $appointment->update([
                'appointment_no' => $this->generateAppointmentNo($appointment->id),
            ]);

            $appointment->billing()->create(
                $this->billingPayload(Arr::get($data, 'billing', []))
            );

            return $appointment->fresh('billing');
        });
    }

    public function updateAppointment(DentalAppointment $appointment, array $data): DentalAppointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            $appointment->update([
                ...$this->appointmentPayload(Arr::get($data, 'appointment', [])),
                'updated_by' => auth()->id() ?? 0,
            ]);

            $appointment->billing()->updateOrCreate(
                ['appointment_id' => $appointment->id],
                $this->billingPayload(Arr::get($data, 'billing', []))
            );

            return $appointment->fresh('billing');
        });
    }

    public function destroyAppointment(DentalAppointment $appointment): void
    {
        DB::transaction(function () use ($appointment) {
            $appointment->billing()?->delete();
            $appointment->delete();
        });
    }

    public function updateMedicalHistory(DentalPatient $patient, array $data): void
    {
        DB::transaction(function () use ($patient, $data) {
            $patient->medicalHistory()->updateOrCreate(
                ['patient_id' => $patient->id],
                $this->medicalHistoryPayload(Arr::get($data, 'medical_history', $data))
            );
        });
    }

    private function patientPayload(array $patient): array
    {
        return [
            'patient_code' => Arr::get($patient, 'patient_code') ?: null,
            'first_name' => Arr::get($patient, 'first_name'),
            'middle_name' => Arr::get($patient, 'middle_name'),
            'last_name' => Arr::get($patient, 'last_name'),
            'gender' => Arr::get($patient, 'gender'),
            'date_of_birth' => $this->normalizeDate(Arr::get($patient, 'date_of_birth')),
            'age' => Arr::get($patient, 'age'),
            'mobile_no' => Arr::get($patient, 'mobile_no'),
            'alternate_mobile_no' => Arr::get($patient, 'alternate_mobile_no'),
            'email' => Arr::get($patient, 'email'),
            'address' => Arr::get($patient, 'address'),
            'city' => Arr::get($patient, 'city'),
            'state' => Arr::get($patient, 'state'),
            'pincode' => Arr::get($patient, 'pincode'),
            'occupation' => Arr::get($patient, 'occupation'),
            'referred_by' => Arr::get($patient, 'referred_by'),
            'status' => Arr::get($patient, 'status', 'Active'),
        ];
    }

    private function medicalHistoryPayload(array $medicalHistory): array
    {
        return [
            'blood_group' => Arr::get($medicalHistory, 'blood_group'),
            'medical_id' => collect(Arr::get($medicalHistory, 'medical_id', []))
                ->filter(fn ($value) => filled($value))
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all(),
            'allergy_details' => Arr::get($medicalHistory, 'allergy_details'),
            'current_medicine' => Arr::get($medicalHistory, 'current_medicine'),
            'previous_dental_treatment' => Arr::get($medicalHistory, 'previous_dental_treatment'),
            'other_medical_notes' => Arr::get($medicalHistory, 'other_medical_notes'),
        ];
    }

    private function appointmentPayload(array $appointment): array
    {
        return [
            'appointment_date' => $this->normalizeDate(Arr::get($appointment, 'appointment_date')),
            'appointment_time' => $this->normalizeTime(Arr::get($appointment, 'appointment_time')),
            'doctor_id' => Arr::get($appointment, 'doctor_id'),
            'visit_type' => Arr::get($appointment, 'visit_type', 'First Visit'),
            'appointment_type_id' => Arr::get($appointment, 'appointment_type_id'),
            'chief_complaint' => Arr::get($appointment, 'chief_complaint'),
            'problem_area' => Arr::get($appointment, 'problem_area'),
            'tooth_no' => Arr::get($appointment, 'tooth_no'),
            'priority' => Arr::get($appointment, 'priority', 'Normal'),
            'status' => Arr::get($appointment, 'status', 'Scheduled'),
            'notes' => Arr::get($appointment, 'notes'),
        ];
    }

    private function billingPayload(array $billing): array
    {
        return [
            'consultation_fee' => Arr::get($billing, 'consultation_fee', 0),
            'treatment_estimate' => Arr::get($billing, 'treatment_estimate', 0),
            'discount' => Arr::get($billing, 'discount', 0),
            'paid_amount' => Arr::get($billing, 'paid_amount', 0),
            'payment_mode' => Arr::get($billing, 'payment_mode'),
            'payment_status' => Arr::get($billing, 'payment_status', 'Pending'),
            'remarks' => Arr::get($billing, 'remarks'),
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
            'full_name' => $this->patientFullName($patient),
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
        return $this->patientProfilePayload($patient);
    }

    private function medicalHistoryPayloadForView(mixed $medicalHistory): array
    {
        $medicalIds = collect($medicalHistory?->medical_id ?? [])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        $medicalNames = empty($medicalIds)
            ? []
            : MedicalDetail::query()
                ->whereIn('id', $medicalIds)
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all();

        return [
            'blood_group' => $medicalHistory?->blood_group,
            'medical_id' => $medicalIds,
            'medical_names' => $medicalNames,
            'allergy_details' => $medicalHistory?->allergy_details,
            'current_medicine' => $medicalHistory?->current_medicine,
            'previous_dental_treatment' => $medicalHistory?->previous_dental_treatment,
            'other_medical_notes' => $medicalHistory?->other_medical_notes,
        ];
    }

    private function appointmentSummaryPayload(Collection $appointments): array
    {
        $completed = $appointments->where('status', 'Completed')->count();
        $pendingScheduled = $appointments->filter(
            fn (DentalAppointment $appointment) => in_array($appointment->status, ['Scheduled', 'Rescheduled'], true)
        )->count();

        $totalPaid = $appointments->sum(fn (DentalAppointment $appointment) => (float) ($appointment->billing?->paid_amount ?? 0));
        $outstanding = $appointments->sum(function (DentalAppointment $appointment) {
            $billing = $appointment->billing;
            $charge = max(
                (float) ($billing?->treatment_estimate ?? 0) > 0
                    ? (float) ($billing?->treatment_estimate ?? 0)
                    : (float) ($billing?->consultation_fee ?? 0),
                0
            );

            return max($charge - (float) ($billing?->discount ?? 0) - (float) ($billing?->paid_amount ?? 0), 0);
        });

        return [
            'total_appointments' => $appointments->count(),
            'completed_appointments' => $completed,
            'pending_scheduled_appointments' => $pendingScheduled,
            'total_paid_amount' => round($totalPaid, 2),
            'outstanding_amount' => round($outstanding, 2),
        ];
    }

    private function appointmentHistoryPayload(DentalAppointment $appointment): array
    {
        /** @var DentalAppointmentBilling|null $billing */
        $billing = $appointment->billing;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'appointment_no' => $appointment->appointment_no,
            'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time,
            'doctor_id' => $appointment->doctor_id,
            'doctor_name' => $appointment->doctor_id ? 'Doctor #'.$appointment->doctor_id : null,
            'visit_type' => $appointment->visit_type,
            'appointment_type_id' => $appointment->appointment_type_id,
            'appointment_type' => $appointment->appointmentType?->name,
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
                'payment_status' => $billing?->payment_status ?? 'Pending',
                'remarks' => $billing?->remarks,
            ],
        ];
    }

    private function sidebarPayload(): array
    {
        $today = Carbon::today();

        $todayAppointments = DentalAppointment::query()
            ->with(['patient', 'appointmentType'])
            ->whereDate('appointment_date', $today->toDateString())
            ->orderBy('appointment_time')
            ->limit(6)
            ->get();

        $upcomingAppointments = DentalAppointment::query()
            ->with(['patient', 'appointmentType'])
            ->whereDate('appointment_date', '>', $today->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(6)
            ->get();

        $recentPatients = DentalPatient::query()
            ->with('latestAppointment')
            ->latest('id')
            ->limit(6)
            ->get();

        return [
            'today_appointments' => $todayAppointments->map(fn (DentalAppointment $appointment) => $this->sidebarAppointmentItem($appointment))->values(),
            'upcoming_appointments' => $upcomingAppointments->map(fn (DentalAppointment $appointment) => $this->sidebarAppointmentItem($appointment))->values(),
            'recent_patients' => $recentPatients->map(fn (DentalPatient $patient) => [
                'id' => $patient->id,
                'patient_name' => $this->patientFullName($patient),
                'appointment_date' => $patient->latestAppointment?->appointment_date?->format('Y-m-d'),
                'appointment_time' => $patient->latestAppointment?->appointment_time,
                'doctor' => $patient->latestAppointment?->doctor_id ? 'Doctor #'.$patient->latestAppointment->doctor_id : 'Unassigned',
                'status' => $patient->latestAppointment?->status ?? $patient->status,
            ])->values(),
        ];
    }

    private function sidebarAppointmentItem(DentalAppointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'patient_name' => $this->patientFullName($appointment->patient),
            'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time,
            'doctor' => $appointment->doctor_id ? 'Doctor #'.$appointment->doctor_id : 'Unassigned',
            'status' => $appointment->status,
        ];
    }

    private function patientFullName(?DentalPatient $patient): string
    {
        return trim(collect([
            $patient?->first_name,
            $patient?->middle_name,
            $patient?->last_name,
        ])->filter()->implode(' '));
    }
}
