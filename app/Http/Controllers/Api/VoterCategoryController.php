<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoterCategory;
use Illuminate\Http\Request;

class VoterCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', VoterCategory::class);
        return VoterCategory::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', VoterCategory::class);
        $request->validate([
            'name' => 'required|string|max:255|unique:voter_categories,name',
            'description' => 'nullable|string',
        ]);

        $voterCategory = VoterCategory::create($request->all());

        return response()->json($voterCategory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VoterCategory $voterCategory)
    {
        $this->authorize('view', $voterCategory);
        return $voterCategory->load('voters');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VoterCategory $voterCategory)
    {
        $this->authorize('update', $voterCategory);
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:voter_categories,name,' . $voterCategory->id,
            'description' => 'nullable|string',
        ]);

        $voterCategory->update($request->all());

        return response()->json($voterCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VoterCategory $voterCategory)
    {
        $this->authorize('delete', $voterCategory);
        $voterCategory->delete();

        return response()->json(null, 204);
    }

    /**
     * Add voters to a category.
     */
    public function addVoters(Request $request, VoterCategory $voterCategory)
    {
        $this->authorize('addVoters', $voterCategory);
        $request->validate([
            'voter_ids' => 'required|array',
            'voter_ids.*' => 'exists:voters,id',
        ]);

        $voters = collect($request->voter_ids)->mapWithKeys(function ($id) {
            return [$id => ['user_id' => auth()->id()]];
        });

        $voterCategory->voters()->syncWithoutDetaching($voters);

        return response()->json(['message' => 'Voters added to category.']);
    }

    /**
     * Remove voters from a category.
     */
    public function removeVoters(Request $request, VoterCategory $voterCategory)
    {
        $this->authorize('removeVoters', $voterCategory);
        $request->validate([
            'voter_ids' => 'required|array',
            'voter_ids.*' => 'exists:voters,id',
        ]);

        $voterCategory->voters()->detach($request->voter_ids);

        return response()->json(['message' => 'Voters removed from category.']);
    }
}
