<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentTypeRequest;
use App\Repositories\AppointmentTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentTypeController extends Controller
{
    protected string $basePath;

    protected AppointmentTypeRepository $repository;

    public function __construct()
    {
        $this->basePath = 'GeneralDetails/AppointmentTypeMaster/';
        $this->repository = new AppointmentTypeRepository();
    }

    public function index(): Response|JsonResponse
    {
        $data = $this->repository->index(request()->all());

        if (json_request()) {
            return response()->json($data);
        }

        return Inertia::render($this->basePath.'Index', [
            'permissions' => $this->repository->permission(),
            'title' => 'Appointment Type Master',
            'desc' => 'Manage appointment type masters with name, description, and status.',
            'icon' => 'pi pi-calendar',
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('appointment-type.index');
    }

    public function store(AppointmentTypeRequest $request): RedirectResponse
    {
        $this->repository->create($request->validated());

        return redirect()
            ->route('appointment-type.index')
            ->with('success', 'Appointment type created successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('appointment-type.edit', $id);
    }

    public function edit(int $id): JsonResponse|RedirectResponse
    {
        if (json_request()) {
            return response()->json($this->repository->edit($id));
        }

        return redirect()->route('appointment-type.index');
    }

    public function update(AppointmentTypeRequest $request, int $id): RedirectResponse
    {
        $this->repository->update($request->validated(), $id);

        return redirect()
            ->route('appointment-type.index')
            ->with('success', 'Appointment type updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            return redirect()
                ->route('appointment-type.index')
                ->with('error', 'Appointment type cannot be deleted because it is used in appointments.');
        }

        return redirect()
            ->route('appointment-type.index')
            ->with('success', 'Appointment type deleted successfully.');
    }
}
