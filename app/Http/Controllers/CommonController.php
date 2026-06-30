<?php

namespace App\Http\Controllers;

use App\Models\MedicalDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getMedicalDetails(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $query = trim((string) $request->input('params.query', $request->input('query', '')));

        $medicalDetails = MedicalDetail::query()
            ->when(! empty($ids), function ($builder) use ($ids) {
                $builder->whereIn('id', (array) $ids);
            }, function ($builder) use ($query) {
                $builder
                    ->where('status', 1)
                    ->when($query !== '', function ($searchBuilder) use ($query) {
                        $searchBuilder->where('name', 'like', '%'.$query.'%');
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($medicalDetails);
    }
}
