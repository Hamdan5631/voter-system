<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panchayat;
use Illuminate\Http\Request;

class PanchayatController extends Controller
{
    /**
     * Display a listing of the panchayats.
     */
    public function index(Request $request)
    {
        $query = Panchayat::query()->withCount('wards');

        // Filter by district
        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%");
        }

        $panchayats = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($panchayats, 200);
    }

    /**
     * Store a newly created panchayat.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:panchayats,code',
            'district' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $panchayat = Panchayat::create($validated);

        return response()->json([
            'message' => 'Panchayat created successfully',
            'panchayat' => $panchayat,
        ], 201);
    }

    /**
     * Display the specified panchayat.
     */
    public function show(Panchayat $panchayat)
    {
        $panchayat->load(['wards']);

        return response()->json($panchayat, 200);
    }

    /**
     * Update the specified panchayat.
     */
    public function update(Request $request, Panchayat $panchayat)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|nullable|string|unique:panchayats,code,' . $panchayat->id,
            'district' => 'sometimes|nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $panchayat->update($validated);

        return response()->json([
            'message' => 'Panchayat updated successfully',
            'panchayat' => $panchayat,
        ], 200);
    }

    /**
     * Remove the specified panchayat.
     */
    public function destroy(Panchayat $panchayat)
    {
        $panchayat->delete();

        return response()->json([
            'message' => 'Panchayat deleted successfully',
        ], 200);
    }
}
