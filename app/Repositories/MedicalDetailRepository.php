<?php

namespace App\Repositories;

use App\Models\MedicalDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class MedicalDetailRepository
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

        $query = MedicalDetail::query()
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
            ->through(function (MedicalDetail $medicalDetail) {
                return [
                    'id' => $medicalDetail->id,
                    'name' => $medicalDetail->name,
                    'description' => $medicalDetail->description,
                    'status' => $medicalDetail->status,
                    'status_label' => $medicalDetail->status === 1 ? 'Active' : 'Inactive',
                    'created_at' => $medicalDetail->created_at?->format('Y-m-d H:i:s'),
                ];
            });
    }

    public function create(array $data): MedicalDetail
    {
        return MedicalDetail::query()->create([
            'name' => trim((string) $data['name']),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'status' => (int) $data['status'],
        ]);
    }

    public function edit(int $id): MedicalDetail
    {
        return MedicalDetail::query()->findOrFail($id);
    }

    public function update(array $data, int $id): MedicalDetail
    {
        $medicalDetail = MedicalDetail::query()->findOrFail($id);

        $medicalDetail->update([
            'name' => trim((string) $data['name']),
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'status' => (int) $data['status'],
        ]);

        return $medicalDetail->fresh();
    }

    public function delete(int $id): void
    {
        MedicalDetail::query()->findOrFail($id)->delete();
    }
}
