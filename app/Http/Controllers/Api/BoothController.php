<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoothController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booth::query()->with(['panchayat', 'ward']);

        if ($request->has('panchayat_id')) {
            $query->where('panchayat_id', $request->panchayat_id);
        }

        if ($request->has('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        $booths = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($booths);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'booth_number' => 'required|string',
            'panchayat_id' => 'required|exists:panchayats,id',
            'ward_id' => 'required|exists:wards,id',
        ]);

        $booth = Booth::create($validated);

        return response()->json($booth->load(['panchayat', 'ward']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booth $booth)
    {
        return response()->json($booth->load(['panchayat', 'ward']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booth $booth)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'booth_number' => 'sometimes|required|string',
            'panchayat_id' => 'sometimes|required|exists:panchayats,id',
            'ward_id' => 'sometimes|required|exists:wards,id',
        ]);

        $booth->update($validated);

        return response()->json($booth->load(['panchayat', 'ward']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booth $booth)
    {
        $booth->delete();

        return response()->json(['message' => 'Booth deleted successfully']);
    }
}