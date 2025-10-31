<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query()->with('ward');

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by ward
        if ($request->has('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($users, 200);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', 'string', Rule::in(['superadmin', 'team_lead', 'booth_agent', 'worker'])],
            'ward_id' => 'nullable|exists:wards,id',
        ]);

        // Validate ward assignment based on role
        if (in_array($validated['role'], ['team_lead', 'booth_agent', 'worker'])) {
            if (empty($validated['ward_id'])) {
                return response()->json([
                    'message' => 'Ward is required for this role',
                ], 422);
            }

            // Check if team_lead or booth_agent already exists for this ward
            if (in_array($validated['role'], ['team_lead', 'booth_agent'])) {
                $existingUser = User::where('ward_id', $validated['ward_id'])
                    ->where('role', $validated['role'])
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'message' => ucfirst(str_replace('_', ' ', $validated['role'])) . ' already assigned to this ward',
                    ], 422);
                }
            }
        }

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('ward'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('ward');

        // Add assigned voters if worker
        if ($user->isWorker()) {
            $user->load('assignedVoters');
        }

        // Add assignments if team lead
        if ($user->isTeamLead()) {
            $user->load('assignments.voter', 'assignments.worker');
        }

        return response()->json($user, 200);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => ['sometimes', 'required', 'string', Rule::in(['superadmin', 'team_lead', 'booth_agent', 'worker'])],
            'ward_id' => 'nullable|exists:wards,id',
        ]);

        // Validate ward assignment based on role
        if (isset($validated['role']) && in_array($validated['role'], ['team_lead', 'booth_agent', 'worker'])) {
            $wardId = $validated['ward_id'] ?? $user->ward_id;
            
            if (empty($wardId)) {
                return response()->json([
                    'message' => 'Ward is required for this role',
                ], 422);
            }

            // Check if team_lead or booth_agent already exists for this ward (excluding current user)
            if (in_array($validated['role'], ['team_lead', 'booth_agent'])) {
                $existingUser = User::where('ward_id', $wardId)
                    ->where('role', $validated['role'])
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'message' => ucfirst(str_replace('_', ' ', $validated['role'])) . ' already assigned to this ward',
                    ], 422);
                }
            }
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Update role if changed
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('ward'),
        ], 200);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
