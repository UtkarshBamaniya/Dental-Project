<?php

namespace App\Http\Controllers;

use App\Http\Requests\DentalClinic\DentalPatientAppointmentRequest;
use App\Models\DentalPatient;
use App\Repositories\DentalPatientAppointmentRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DentalPatientAppointmentController extends Controller
{
    protected $basePath;
    protected $urlSegment;
    protected $repository;
    
    public function __construct()
    {
        // parent::__construct();
        // $this->middleware('auth');
        // $this->middleware('role_or_permission:size_group.create', ['only' => ['create', 'store']]);
        // $this->middleware('role_or_permission:size_group.view', ['only' => ['index', 'show']]);
        // $this->middleware('role_or_permission:size_group.update', ['only' => ['edit', 'update']]);
        // $this->middleware('role_or_permission:size_group.delete', ['only' => ['destroy']]);
        $this->basePath = 'DentalClinic/PatientAppointmentMaster/';
        $this->urlSegment = request()->segment(1);
        $this->repository = new DentalPatientAppointmentRepository();
    }

    public function index()
    {
        $input = request()->all();
        $data = $this->repository->index($input);

        if (json_request()) {
            return response()->json($data);
        }
        
        return Inertia::render(
            $this->basePath . 'Index',
            [
                'data' => $data,
                'permissions' => $this->repository->permission(),
                'title' => 'Patient Appointment Master',
                'desc' => 'Manage Patient Appointment - Add New / Edit / Delete Patient Appointment.',
                'icon' => 'pi pi-calendar-plus',
            ]
        );
    }

    public function store(DentalPatientAppointmentRequest $request)
    {
        $input = $request->all();
        $this->repository->create($input);

        return redirect()
            ->route('dental-patient-appointments.index')
            ->with('success', 'Patient appointment created successfully.');
    }

    public function show($id)
    {
        if (json_request()) {
            return response()->json($this->repository->getShowData($id));
        }

        return redirect()->route('dental-patient-appointments.index');
    }

    public function edit($id)
    {
        $patient = DentalPatient::find($id);
        if (json_request()) {
            return response()->json($this->repository->getEditData($patient));
        }
    }

    public function update(DentalPatientAppointmentRequest $request)
    {
        $input = $request->all();
        $this->repository->update($input);

        // return redirect()
        //     ->route('dental-patient-appointments.index')
        //     ->with('success', 'Patient appointment updated successfully.');
    }

    public function destroy($id)
    {
        $this->repository->delete($id);

        // return redirect()
        //     ->route('dental-patient-appointments.index')
        //     ->with('success', 'Patient appointment deleted successfully.');
    }

    public function storeFollowUp(DentalPatientAppointmentRequest $request, DentalPatient $patient): RedirectResponse
    {
        $this->repository->storeFollowUp(
            $patient,
            $request->all()
        );

        return redirect()
            ->route('dental-patient-appointments.index')
            ->with('success', 'Follow-up appointment created successfully.');
    }
}
