<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Panchayat;
use App\Models\Booth;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        $ward->load(['users', 'voters', 'panchayat', 'clonedFrom', 'clonedWards']);

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

    /**
     * Clone ward with voters and booths data only (fresh data, no assignments or statuses).
     */
    public function cloneWard(Request $request)
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'new_ward_name' => 'required|string|max:255',
            'new_ward_number' => 'required|string',
        ]);

        $sourceWard = Ward::with('voters')->findOrFail($validated['ward_id']);
        $sourceBooths = Booth::where('ward_id', $sourceWard->id)->get();

        return DB::transaction(function () use ($sourceWard, $sourceBooths, $validated) {
            // Create new ward with same panchayat_id and track source ward
            $newWard = Ward::create([
                'name' => $validated['new_ward_name'],
                'ward_number' => $validated['new_ward_number'],
                'panchayat_id' => $sourceWard->panchayat_id,
                'description' => $sourceWard->description,
                'cloned_from_ward_id' => $sourceWard->id,
            ]);

            // Clone booths and create mapping (old_booth_id => new_booth_id)
            $boothMapping = [];
            foreach ($sourceBooths as $oldBooth) {
                $newBooth = Booth::create([
                    'name' => $oldBooth->name,
                    'booth_number' => $oldBooth->booth_number,
                    'panchayat_id' => $oldBooth->panchayat_id,
                    'ward_id' => $newWard->id,
                ]);
                $boothMapping[$oldBooth->id] = $newBooth->id;
            }

            // Clone voters only (no assignments, statuses, or categories)
            $voterMapping = [];
            foreach ($sourceWard->voters as $oldVoter) {
                $newVoter = Voter::create([
                    'serial_number' => $oldVoter->serial_number,
                    'ward_id' => $newWard->id,
                    'panchayat' => $oldVoter->panchayat,
                    'panchayat_id' => $oldVoter->panchayat_id,
                    'booth_id' => isset($boothMapping[$oldVoter->booth_id]) ? $boothMapping[$oldVoter->booth_id] : null,
                    'image_path' => $oldVoter->image_path,
                ]);
                $voterMapping[$oldVoter->id] = $newVoter->id;
            }

            return response()->json([
                'message' => 'Ward cloned successfully with fresh data',
                'data' => [
                    'new_ward' => $newWard->load(['panchayat', 'clonedFrom']),
                    'source_ward_id' => $sourceWard->id,
                    'source_ward_name' => $sourceWard->name,
                    'cloned_booths_count' => count($boothMapping),
                    'cloned_voters_count' => count($voterMapping),
                ],
            ], 201);
        });
    }

    /**
     * Revert/delete a cloned ward (only works for cloned wards).
     */
    public function revertClonedWard(Request $request, Ward $ward)
    {
        // Check if this ward was cloned
        if (!$ward->cloned_from_ward_id) {
            return response()->json([
                'message' => 'This ward is not a cloned ward. Only cloned wards can be reverted.',
                'error' => 'not_cloned',
            ], 422);
        }

        // Get source ward info before deletion
        $sourceWard = $ward->clonedFrom;
        $boothsCount = Booth::where('ward_id', $ward->id)->count();
        $votersCount = Voter::where('ward_id', $ward->id)->count();
        $usersCount = $ward->users()->count();

        return DB::transaction(function () use ($ward, $sourceWard, $boothsCount, $votersCount, $usersCount) {
            // Store info before deletion
            $wardInfo = [
                'id' => $ward->id,
                'name' => $ward->name,
                'ward_number' => $ward->ward_number,
            ];

            // Delete the ward (cascades will handle voters and booths)
            $ward->delete();

            return response()->json([
                'message' => 'Cloned ward reverted successfully',
                'data' => [
                    'reverted_ward' => $wardInfo,
                    'source_ward' => $sourceWard ? [
                        'id' => $sourceWard->id,
                        'name' => $sourceWard->name,
                        'ward_number' => $sourceWard->ward_number,
                    ] : null,
                    'deleted_counts' => [
                        'booths' => $boothsCount,
                        'voters' => $votersCount,
                        'users' => $usersCount,
                    ],
                ],
            ], 200);
        });
    }
}
