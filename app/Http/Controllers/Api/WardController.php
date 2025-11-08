<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Panchayat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WardController extends Controller
{
    /**
     * Display a listing of the wards.
     */
    public function index(Request $request)
    {
        $query = Ward::query()->with('panchayat');

        // Filter by panchayat
        if ($request->has('panchayat_id')) {
            $query->where('panchayat_id', $request->panchayat_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('ward_number', 'like', "%{$request->search}%");
        }

        $wards = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($wards, 200);
    }

    /**
     * Store a newly created ward.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ward_number' => [
                'required',
                'string',
            ],
            'panchayat_id' => 'required|exists:panchayats,id',
            'description' => 'nullable|string',
        ]);

        $ward = Ward::create($validated);

        return response()->json([
            'message' => 'Ward created successfully',
            'ward' => $ward->load('panchayat'),
        ], 201);
    }

    /**
     * Display the specified ward.
     */
    public function show(Ward $ward)
    {
        $ward->load(['users', 'voters', 'panchayat']);

        return response()->json($ward, 200);
    }

    /**
     * Update the specified ward.
     */
    public function update(Request $request, Ward $ward)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'ward_number' => [
                'sometimes',
                'required',
                'string',
            ],
            'panchayat_id' => 'sometimes|required|exists:panchayats,id',
            'description' => 'nullable|string',
        ]);

        $ward->update($validated);
        $ward->load('panchayat');

        return response()->json([
            'message' => 'Ward updated successfully',
            'ward' => $ward,
        ], 200);
    }

    /**
     * Get wards by panchayat.
     */
    public function getByPanchayat(Request $request, Panchayat $panchayat)
    {
        $query = Ward::query()
            ->where('panchayat_id', $panchayat->id)
            ->with('panchayat');

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('ward_number', 'like', "%{$request->search}%");
        }

        $wards = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($wards, 200);
    }

    /**
     * Remove the specified ward.
     */
    public function destroy(Ward $ward)
    {
        $ward->delete();

        return response()->json([
            'message' => 'Ward deleted successfully',
        ], 200);
    }
}
