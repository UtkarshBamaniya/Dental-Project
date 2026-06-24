<?php

namespace App\Http\Controllers;

use App\Http\Requests\DentalClinic\DentalPatientAppointmentRequest;
use App\Models\DentalAppointment;
use App\Models\DentalPatient;
use App\Repositories\DentalPatientAppointmentRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DentalPatientAppointmentController extends Controller
{
    protected string $basePath;

    protected DentalPatientAppointmentRepository $repository;

    public function __construct()
    {
        $this->basePath = 'DentalClinic/PatientAppointmentMaster/';
        $this->repository = new DentalPatientAppointmentRepository();
    }

    public function index(): Response|\Illuminate\Http\JsonResponse
    {
        $data = $this->repository->index(request()->all());

        if (json_request()) {
            return response()->json($data);
        }

        return Inertia::render($this->basePath.'Index', [
            'data' => $data,
            'permissions' => $this->repository->permission(),
            'title' => 'Patient Appointment Master',
            'desc' => 'Manage patients, appointments, follow-ups, billing, and medical history.',
            'icon' => 'pi pi-calendar-plus',
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('dental-patient-appointments.index');
    }

    public function store(DentalPatientAppointmentRequest $request): RedirectResponse
    {
        $patient = $this->repository->create($request->validated());

        return redirect()
            ->route('dental-patient-appointments.show', $patient->id)
            ->with('success', 'Patient and first appointment created successfully.');
    }

    public function show($id): Response|\Illuminate\Http\JsonResponse
    {
        $patient = DentalPatient::query()->findOrFail($id);
        $data = $this->repository->getShowPageData($patient);

        if (json_request()) {
            return response()->json($data);
        }

        return Inertia::render($this->basePath.'Show', $data);
    }

    public function edit($id): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $patient = DentalPatient::query()->findOrFail($id);

        if (json_request()) {
            return response()->json($this->repository->getEditPatientData($patient));
        }

        return redirect()->route('dental-patient-appointments.show', $patient->id);
    }

    public function update(DentalPatientAppointmentRequest $request, $id): RedirectResponse
    {
        if ($request->query('entity') === 'appointment') {
            $appointment = DentalAppointment::query()->findOrFail($id);
            $this->repository->updateAppointment($appointment, $request->validated());

            return redirect()
                ->route('dental-patient-appointments.show', $appointment->patient_id)
                ->with('success', 'Appointment updated successfully.');
        }

        if ($request->query('entity') === 'medical-history') {
            $patient = DentalPatient::query()->findOrFail($id);
            $this->repository->updateMedicalHistory($patient, $request->validated());

            return redirect()
                ->route('dental-patient-appointments.show', $patient->id)
                ->with('success', 'Medical history updated successfully.');
        }

        $patient = DentalPatient::query()->findOrFail($id);
        $this->repository->updatePatient($patient, $request->validated());

        return redirect()
            ->back()
            ->with('success', 'Patient details updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        if (request()->query('entity') === 'appointment') {
            $appointment = DentalAppointment::query()->findOrFail($id);
            $patientId = $appointment->patient_id;
            $this->repository->destroyAppointment($appointment);

            return redirect()
                ->route('dental-patient-appointments.show', $patientId)
                ->with('success', 'Appointment deleted successfully.');
        }

        $this->repository->delete($id);

        return redirect()
            ->route('dental-patient-appointments.index')
            ->with('success', 'Patient deleted successfully.');
    }

    public function storeFollowUp(DentalPatientAppointmentRequest $request, DentalPatient $patient): RedirectResponse
    {
        $this->repository->storeFollowUp($patient, $request->validated());

        return redirect()
            ->route('dental-patient-appointments.show', $patient->id)
            ->with('success', 'Follow-up appointment created successfully.');
    }

    public function updateAppointment(DentalPatientAppointmentRequest $request, DentalAppointment $appointment): RedirectResponse
    {
        $this->repository->updateAppointment($appointment, $request->validated());

        return redirect()
            ->route('dental-patient-appointments.show', $appointment->patient_id)
            ->with('success', 'Appointment updated successfully.');
    }

    public function destroyAppointment(DentalAppointment $appointment): RedirectResponse
    {
        $patientId = $appointment->patient_id;
        $this->repository->destroyAppointment($appointment);

        return redirect()
            ->route('dental-patient-appointments.show', $patientId)
            ->with('success', 'Appointment deleted successfully.');
    }

    public function updateMedicalHistory(DentalPatientAppointmentRequest $request, DentalPatient $patient): RedirectResponse
    {
        $this->repository->updateMedicalHistory($patient, $request->validated());

        return redirect()
            ->route('dental-patient-appointments.show', $patient->id)
            ->with('success', 'Medical history updated successfully.');
    }
}
