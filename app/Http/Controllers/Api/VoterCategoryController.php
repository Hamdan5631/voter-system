<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoterCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoterCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', VoterCategory::class);
        
        $perPage = $request->query('per_page', 15);
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return VoterCategory::with('user')->paginate($perPage);
        }

        return $user->voterCategories()->with('user')->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', VoterCategory::class);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => 'nullable|string',
        ]);

        $voterCategory = VoterCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

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
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('voter_categories')->where('user_id', auth()->id())->ignore($voterCategory->id)],
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
