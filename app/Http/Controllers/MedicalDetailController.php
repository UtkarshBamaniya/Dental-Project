<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalDetailRequest;
use App\Repositories\MedicalDetailRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MedicalDetailController extends Controller
{
    protected string $basePath;

    protected MedicalDetailRepository $repository;

    public function __construct()
    {
        $this->basePath = 'GeneralDetails/MedicalDetailMaster/';
        $this->repository = new MedicalDetailRepository();
    }

    public function index(): Response|JsonResponse
    {
        $data = $this->repository->index(request()->all());

        if (json_request()) {
            return response()->json($data);
        }

        return Inertia::render($this->basePath.'Index', [
            'permissions' => $this->repository->permission(),
            'title' => 'Medical Details Master',
            'desc' => 'Manage medical detail masters with name, description, and status.',
            'icon' => 'pi pi-info-circle',
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('medical-details.index');
    }

    public function store(MedicalDetailRequest $request): RedirectResponse
    {
        $this->repository->create($request->validated());

        return redirect()
            ->route('medical-details.index')
            ->with('success', 'Medical detail created successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('medical-details.edit', $id);
    }

    public function edit(int $id): JsonResponse|RedirectResponse
    {
        if (json_request()) {
            return response()->json($this->repository->edit($id));
        }

        return redirect()->route('medical-details.index');
    }

    public function update(MedicalDetailRequest $request, int $id): RedirectResponse
    {
        $this->repository->update($request->validated(), $id);

        return redirect()
            ->route('medical-details.index')
            ->with('success', 'Medical detail updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->repository->delete($id);

        return redirect()
            ->route('medical-details.index')
            ->with('success', 'Medical detail deleted successfully.');
    }
}
