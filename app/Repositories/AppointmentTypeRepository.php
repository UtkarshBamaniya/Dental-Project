<?php

namespace App\Repositories;

use App\Models\AppointmentType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class AppointmentTypeRepository
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

    public function index(array $input): LengthAwarePaginator
    {
        $sortField = (string) Arr::get($input, 'sortField', 'id');
        $sortOrder = (string) Arr::get($input, 'sortOrder', 'desc');
        $perPage = max(1, min((int) Arr::get($input, 'size', 10), 100));
        $page = max(1, (int) Arr::get($input, 'page', 1));

        $query = AppointmentType::query()
            ->when(Arr::get($input, 'name'), function ($builder, $value) {
                $builder->where('name', 'like', '%'.$value.'%');
            })
            ->when(Arr::get($input, 'description'), function ($builder, $value) {
                $builder->where('description', 'like', '%'.$value.'%');
            })
            ->when(Arr::get($input, 'status') !== null && Arr::get($input, 'status') !== '', function ($builder) use ($input) {
                $builder->where('status', (int) Arr::get($input, 'status'));
            });

        if (!in_array($sortField, ['id', 'name', 'description', 'status', 'created_at'], true)) {
            $sortField = 'id';
        }

        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString()
            ->through(function (AppointmentType $appointmentType) {
                return [
                    'id' => $appointmentType->id,
                    'name' => $appointmentType->name,
                    'description' => $appointmentType->description,
                    'status' => $appointmentType->status,
                    'status_label' => $appointmentType->status === 1 ? 'Active' : 'Inactive',
                    'created_at' => $appointmentType->created_at?->format('Y-m-d H:i:s'),
                ];
            });
    }

    public function create(array $data): AppointmentType
    {
        return AppointmentType::query()->create([
            'name' => trim((string) $data['name']),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'status' => (int) $data['status'],
        ]);
    }

    public function edit(int $id): AppointmentType
    {
        return AppointmentType::query()->findOrFail($id);
    }

    public function update(array $data, int $id): AppointmentType
    {
        $appointmentType = AppointmentType::query()->findOrFail($id);

        $appointmentType->update([
            'name' => trim((string) $data['name']),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'status' => (int) $data['status'],
        ]);

        return $appointmentType->fresh();
    }

    public function delete(int $id): bool
    {
        $appointmentType = AppointmentType::query()
            ->withCount('appointments')
            ->findOrFail($id);

        if ($appointmentType->appointments_count > 0) {
            return false;
        }

        $appointmentType->delete();

        return true;
    }
}
