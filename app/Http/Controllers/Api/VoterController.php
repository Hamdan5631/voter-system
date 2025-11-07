<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panchayat;
use App\Models\Voter;
use App\Models\VoterStatus;
use App\Models\VoterWorkerAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VoterController extends Controller
{
    /**
     * Display a listing of the voters.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Voter::query()->with(['ward', 'assignment.worker', 'latestStatus.user','panchayat']);

        // Superadmin can see all voters
        if (!$user->isSuperadmin()) {
            // Team Lead and Booth Agent can see voters in their ward
            if ($user->isTeamLead() || $user->isBoothAgent()) {
                $query->where('ward_id', $user->ward_id);
            }
            // Worker can only see assigned voters
            elseif ($user->isWorker()) {
                $query->whereHas('worker', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
        }

        // Filters
        if ($request->has('serial_number')) {
            $query->searchSerialNumber($request->serial_number);
        }

        if ($request->has('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        if ($request->has('panchayat')) {
            $query->panchayat($request->panchayat);
        }

        if ($request->has('panchayat_id')) {
            $query->where('panchayat_id', $request->panchayat_id);
        }


        if ($request->has('status')) {
            $query->status($request->status);
        }

        $voters = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($voters, 200);
    }

    /**
     * Store a newly created voter.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Voter::class);

        $validated = $request->validate([
            'serial_number' => 'required|string|unique:voters,serial_number',
            'ward_id' => 'required|exists:wards,id',
            'panchayat' => 'nullable|string|max:255',
            'panchayat_id' => 'required|exists:panchayats,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'));
        }

        $panchayat = Panchayat::find($validated['panchayat_id']);
        $panchayat_name = $panchayat->name;

        $voter = Voter::create([
            'serial_number' => $validated['serial_number'],
            'ward_id' => $validated['ward_id'],
            'panchayat' => $panchayat_name,
            'panchayat_id' => $validated['panchayat_id'],
            'image_path' => $imagePath,
        ]);

        // Create initial status record
        VoterStatus::create([
            'voter_id' => $voter->id,
            'user_id' => $request->user()->id,
            'status' => 'not_voted',
        ]);

        $voter->load(['ward', 'latestStatus.user']);

        return response()->json([
            'message' => 'Voter created successfully',
            'voter' => $voter,
        ], 201);
    }

    /**
     * Store a newly created voter.
     */
    public function bulkStore(Request $request)
    {
        $this->authorize('create', Voter::class);

        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'panchayat_id' => 'required|exists:panchayats,id',
            'voters' => 'required|array',
            'voters.*.serial_number' => 'required|string',
            'voters.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $panchayat = Panchayat::find($validated['panchayat_id']);
        $panchayat_name = $panchayat->name;

        $createdVoters = [];
        $duplicateVoters = [];

        foreach ($validated['voters'] as $voterData) {
            // Check for existing serial number
            $existingVoter = Voter::where('serial_number', $voterData['serial_number'])->first();

            if ($existingVoter) {
                $duplicateVoters[] = $voterData['serial_number'];
                continue; // Skip to the next voter
            }

            $imagePath = null;
            if (isset($voterData['image'])) {
                $imagePath = $this->uploadImage($voterData['image']);
            }

            $voter = Voter::create([
                'serial_number' => $voterData['serial_number'],
                'ward_id' => $validated['ward_id'],
                'panchayat' => $panchayat_name,
                'panchayat_id' => $validated['panchayat_id'],
                'image_path' => $imagePath,
            ]);

            // Create initial status record
            VoterStatus::create([
                'voter_id' => $voter->id,
                'user_id' => $request->user()->id,
                'status' => 'not_voted',
            ]);

            $voter->load(['ward', 'latestStatus.user']);
            $createdVoters[] = $voter;
        }

        $message = sprintf('%d voters created successfully.', count($createdVoters));
        if (!empty($duplicateVoters)) {
            $message .= sprintf(' %d voters already exist with serial numbers: %s.', count($duplicateVoters), implode(', ', $duplicateVoters));
        }

        return response()->json([
            'message' => $message,
            'voters' => $createdVoters,
            'duplicate_serial_numbers' => $duplicateVoters,
        ], 201);
    }

    /**
     * Find voter by serial number.
     */
    public function findBySerialNumber(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string',
        ]);

        $user = $request->user();
        $query = Voter::query()->with(['ward', 'assignment.worker', 'assignment.teamLead', 'latestStatus.user']);

        // Superadmin can see all voters
        if (!$user->isSuperadmin()) {
            // Team Lead and Booth Agent can see voters in their ward
            if ($user->isTeamLead() || $user->isBoothAgent()) {
                $query->where('ward_id', $user->ward_id);
            }
            // Worker can only see assigned voters
            elseif ($user->isWorker()) {
                $query->whereHas('worker', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
        }

        // Find by exact serial number
        $voter = $query->where('serial_number', $validated['serial_number'])->first();

        if (!$voter) {
            return response()->json([
                'message' => 'Voter not found',
            ], 404);
        }

        // Check authorization
        $this->authorize('view', $voter);

        return response()->json($voter, 200);
    }

    /**
     * Display the specified voter.
     */
    public function show(Request $request, Voter $voter)
    {
        $this->authorize('view', $voter);

        $voter->load(['ward', 'assignment.worker', 'assignment.teamLead', 'latestStatus.user']);

        return response()->json($voter, 200);
    }

    /**
     * Update the specified voter.
     */
    public function update(Request $request, Voter $voter)
    {
        $this->authorize('update', $voter);
        // Validate (still for safety)
        $request->validate([
            'serial_number' => 'sometimes|required|string|unique:voters,serial_number,' . $voter->id,
            'ward_id' => 'sometimes|required|exists:wards,id',
            'panchayat_id' => 'sometimes|required|exists:panchayats,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($voter->image_path) {
                $this->deleteImage($voter->image_path);
            }
    
            // Upload new image and merge into request
            $imagePath = $this->uploadImage($request->file('image'));
            $request->merge(['image_path' => $imagePath]);
        }
    
        // Set panchayat name if ID provided
        if ($request->filled('panchayat_id')) {
            $panchayat = Panchayat::find($request->panchayat_id);
            if ($panchayat) {
                $request->merge(['panchayat' => $panchayat->name]);
            }
        }
        // ✅ Update voter directly from request data
        $voter->update($request->all());
    
        // Reload relations
        $voter->load(['ward', 'latestStatus.user']);
    
        return response()->json([
            'message' => 'Voter updated successfully',
            'voter' => $voter,
        ], 200);
    }
    
    /**
     * Remove the specified voter.
     */
    public function destroy(Voter $voter)
    {
        $this->authorize('delete', $voter);

        // Delete image if exists
        if ($voter->image_path) {
            $this->deleteImage($voter->image_path);
        }

        $voter->delete();

        return response()->json([
            'message' => 'Voter deleted successfully',
        ], 200);
    }

    /**
     * Update voter status (not_voted, voted, visited).
     */
    public function updateStatus(Request $request, Voter $voter)
    {
        $user = $request->user();
        
        // Check authorization - workers can only set to 'visited'
        if ($user->isWorker() && ($request->status != 'visited' || $request->status != 'not_visited')) {
            abort(403, 'Workers can only change status to "visited" or "not_visited"');
        }
        
        $this->authorize('updateStatus', $voter);

        $validated = $request->validate([
            'status' => 'required|string|in:not_voted,voted,visited,not_visited',
        ]);

        // Create new status record in voter_statuses table
        VoterStatus::create([
            'voter_id' => $voter->id,
            'user_id' => $user->id,
            'status' => $validated['status'],
        ]);

        // Reload voter with latest status
        $voter->load(['ward', 'latestStatus.user']);

        return response()->json([
            'message' => 'Voter status updated successfully',
            'voter' => $voter,
        ], 200);
    }

    /**
     * Update remark for assigned voter (worker only).
     */
    public function updateRemark(Request $request, Voter $voter)
    {
        $this->authorize('updateRemark', $voter);

        $validated = $request->validate([
            'remark' => 'required|string|max:500',
        ]);

        $assignment = VoterWorkerAssignment::where('voter_id', $voter->id)
            ->where('worker_id', $request->user()->id)
            ->firstOrFail();

        $assignment->update(['remark' => $validated['remark']]);

        return response()->json([
            'message' => 'Remark updated successfully',
            'voter' => $voter->load(['ward', 'assignment.worker', 'latestStatus.user']),
        ], 200);
    }

    /**
     * Assign voter to worker (team lead only).
     */
    public function assignWorker(Request $request, Voter $voter)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:users,id',
        ]);

        $worker = User::findOrFail($validated['worker_id']);

        // Check authorization using AssignmentPolicy
        if (!$request->user()->can('assign', [Voter::class, $voter, $worker])) {
            abort(403, 'Unauthorized to assign this voter');
        }

        // Create or update assignment
        VoterWorkerAssignment::updateOrCreate(
            ['voter_id' => $voter->id],
            [
                'worker_id' => $worker->id,
                'assigned_by' => $request->user()->id, 
            ]
        );

        $voter->load(['ward', 'assignment.worker', 'assignment.teamLead', 'latestStatus.user']);

        return response()->json([
            'message' => 'Voter assigned to worker successfully',
            'voter' => $voter,
        ], 200);
    }

    /**
     * Unassign voter from a worker.
     */
    public function unassignWorker(Request $request, Voter $voter)
    {
        $this->authorize('unassign', $voter);

        $assignment = VoterWorkerAssignment::where('voter_id', $voter->id)->first();

        if ($assignment) {
            $assignment->delete();
            return response()->json(['message' => 'Voter unassigned successfully'], 200);
        }

        return response()->json(['message' => 'Voter is not assigned'], 404);
    }

    /**
     * Get unassigned voters (voters not assigned to any worker).
     */
    public function getUnassignedVoters(Request $request)
    {
        $user = $request->user();
        $query = Voter::query()->with(['ward', 'latestStatus.user'])
            ->whereDoesntHave('assignment');

        // Superadmin can see all unassigned voters
        if (!$user->isSuperadmin()) {
            // Team Lead and Booth Agent can see unassigned voters in their ward
            if ($user->isTeamLead() || $user->isBoothAgent()) {
                $query->where('ward_id', $user->ward_id);
            }
            // Workers cannot see unassigned voters list
            elseif ($user->isWorker()) {
                abort(403, 'Workers cannot view unassigned voters');
            }
        }

        // Filter by ward_id if provided
        if ($request->has('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        // Additional filters
        if ($request->has('serial_number')) {
            $query->searchSerialNumber($request->serial_number);
        }

        if ($request->has('panchayat')) {
            $query->panchayat($request->panchayat);
        }

        if ($request->has('status')) {
            $query->status($request->status);
        }

        $voters = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($voters, 200);
    }

    /**
     * Get assigned voters (voters assigned to any worker).
     */
    public function getAssignedVoters(Request $request)
    {
        $user = $request->user();
        $query = Voter::query()
            ->join('voter_worker_assignments', 'voters.id', '=', 'voter_worker_assignments.voter_id')
            ->select('voters.*', 'voter_worker_assignments.worker_id as assigned_worker_id')
            ->with(['ward', 'assignment.worker', 'latestStatus.user']);

        // Superadmin can see all assigned voters
        if (!$user->isSuperadmin()) {
            // Team Lead and Booth Agent can see assigned voters in their ward
            if ($user->isTeamLead() || $user->isBoothAgent()) {
                $query->where('ward_id', $user->ward_id);
            }
            // Workers can only see their own assigned voters
            elseif ($user->isWorker()) {
                $query->where('voter_worker_assignments.worker_id', $user->id);
            }
        }

        // Filter by ward_id if provided
        if ($request->has('worker_id')) {
            $query->where('voter_worker_assignments.worker_id', $request->worker_id);
        }

        $voters = $query->latest()->paginate($request->get('per_page', 15));

        // If no assigned voters found, return a custom 404 not found response
        if ($voters->isEmpty()) {
            return response()->json([
                'message' => 'No assigned voters found'
            ], 404);
        }

        return response()->json($voters, 200);
    }

    /**
     * Bulk assign multiple voters to worker (team lead only).
     */
    public function bulkAssignWorker(Request $request)
    {
        $validated = $request->validate([
            'voter_ids' => 'required|array|min:1',
            'voter_ids.*' => 'required|exists:voters,id',
            'worker_id' => 'required|exists:users,id',
        ]);

        $worker = User::findOrFail($validated['worker_id']);

        // Verify worker is actually a worker
        if (!$worker->isWorker()) {
            abort(422, 'The specified user is not a worker');
        }

        $user = $request->user();
        $assignedVoters = [];
        $failedVoters = [];

        foreach ($validated['voter_ids'] as $voterId) {
            $voter = Voter::findOrFail($voterId);

            // Check authorization using AssignmentPolicy
            if (!$user->can('assign', [Voter::class, $voter, $worker])) {
                $failedVoters[] = [
                    'voter_id' => $voterId,
                    'reason' => 'Unauthorized to assign this voter',
                ];
                continue;
            }

            // Create or update assignment
            VoterWorkerAssignment::updateOrCreate(
                ['voter_id' => $voter->id],
                [
                    'worker_id' => $worker->id,
                    'assigned_by' => $user->id,
                ]
            );

            $voter->load(['ward', 'assignment.worker', 'assignment.teamLead', 'latestStatus.user']);
            $assignedVoters[] = $voter;
        }

        return response()->json([
            'message' => sprintf(
                '%d voter(s) assigned successfully, %d failed',
                count($assignedVoters),
                count($failedVoters)
            ),
            'assigned_count' => count($assignedVoters),
            'failed_count' => count($failedVoters),
            'assigned_voters' => $assignedVoters,
            'failed_voters' => $failedVoters,
        ], count($failedVoters) === 0 ? 200 : 207); // 207 Multi-Status if some failed
    }

    /**
     * Get the storage disk for voter images.
     */
    private function getImageDisk(): string
    {
        // Use S3 if configured, otherwise use public/local
        return env('VOTER_IMAGE_DISK', env('FILESYSTEM_DISK', 'public'));
    }

    /**
     * Upload image.
     */
    private function uploadImage($file)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'voters/' . $filename;

        // Get storage disk
        $disk = $this->getImageDisk();
        
        // Save file to storage (S3 or local) using our specific filename
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()), 'public');

        return $path;
    }

    /**
     * Delete image from storage.
     */
    private function deleteImage($path)
    {
        $disk = $this->getImageDisk();
        
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

}
