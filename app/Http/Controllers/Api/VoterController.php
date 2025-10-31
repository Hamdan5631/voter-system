<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use App\Models\VoterWorkerAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class VoterController extends Controller
{
    /**
     * Display a listing of the voters.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Voter::query()->with(['ward', 'assignment.worker']);

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

        if ($request->has('status')) {
            $query->status($request->boolean('status'));
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
            'panchayat' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'));
        }

        $voter = Voter::create([
            'serial_number' => $validated['serial_number'],
            'ward_id' => $validated['ward_id'],
            'panchayat' => $validated['panchayat'],
            'image_path' => $imagePath,
        ]);

        $voter->load(['ward']);

        return response()->json([
            'message' => 'Voter created successfully',
            'voter' => $voter,
        ], 201);
    }

    /**
     * Display the specified voter.
     */
    public function show(Request $request, Voter $voter)
    {
        $this->authorize('view', $voter);

        $voter->load(['ward', 'assignment.worker', 'assignment.teamLead']);

        return response()->json($voter, 200);
    }

    /**
     * Update the specified voter.
     */
    public function update(Request $request, Voter $voter)
    {
        $this->authorize('update', $voter);

        $validated = $request->validate([
            'serial_number' => 'sometimes|required|string|unique:voters,serial_number,' . $voter->id,
            'ward_id' => 'sometimes|required|exists:wards,id',
            'panchayat' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($voter->image_path) {
                $this->deleteImage($voter->image_path);
            }
            $validated['image_path'] = $this->uploadImage($request->file('image'));
        }

        $voter->update($validated);

        $voter->load(['ward']);

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
     * Update voter status (voted/unvoted).
     */
    public function updateStatus(Request $request, Voter $voter)
    {
        $this->authorize('updateStatus', $voter);

        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $voter->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Voter status updated successfully',
            'voter' => $voter->load(['ward']),
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
            'voter' => $voter->load(['ward', 'assignment.worker']),
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

        $voter->load(['ward', 'assignment.worker', 'assignment.teamLead']);

        return response()->json([
            'message' => 'Voter assigned to worker successfully',
            'voter' => $voter,
        ], 200);
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
     * Upload and resize image.
     */
    private function uploadImage($file)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'voters/' . $filename;

        // Resize image
        $image = Image::make($file);
        $image->resize(800, 800, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Get storage disk
        $disk = $this->getImageDisk();
        
        // Encode image based on original extension
        $encoded = match(strtolower($file->getClientOriginalExtension())) {
            'jpg', 'jpeg' => $image->encode('jpg', 85),
            'png' => $image->encode('png'),
            default => $image->encode('jpg', 85),
        };

        // Save to storage (S3 or local)
        Storage::disk($disk)->put($path, (string) $encoded, 'public');

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

    /**
     * Get image URL.
     */
    private function getImageUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        $disk = $this->getImageDisk();
        
        if ($disk === 's3') {
            return Storage::disk('s3')->url($path);
        } else {
            return Storage::disk('public')->url($path);
        }
    }
}
