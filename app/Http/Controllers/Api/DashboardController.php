<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Voter::query();

        // Filter by role
        if ($user->isSuperadmin()) {
            // Superadmin sees all data - no filter needed
        } elseif ($user->isTeamLead()) {
            // Team Lead sees data for their ward only
            $query->where('ward_id', $user->ward_id);
        } elseif ($user->isBoothAgent()) {
            // Booth Agent sees data for their ward only
            $query->where('ward_id', $user->ward_id);
        } elseif ($user->isWorker()) {
            // Worker sees data for their assigned voters only
            $query->whereHas('worker', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        } else {
            // No access for other roles
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Get statistics
        $totalVoters = $query->count();
        $votersVoted = (clone $query)->where('status', true)->count();
        $votersNotVoted = (clone $query)->where('status', false)->count();
        
        // Calculate percentage
        $votedPercentage = $totalVoters > 0 
            ? round(($votersVoted / $totalVoters) * 100, 2) 
            : 0;
        
        $notVotedPercentage = $totalVoters > 0 
            ? round(($votersNotVoted / $totalVoters) * 100, 2) 
            : 0;

        return response()->json([
            'data' => [
                'total_voters' => $totalVoters,
                'voters_voted' => $votersVoted,
                'voters_not_voted' => $votersNotVoted,
                'voted_percentage' => $votedPercentage,
                'not_voted_percentage' => $notVotedPercentage,
                'role' => $user->role,
                'ward_id' => $user->ward_id ?? null,
            ],
        ], 200);
    }
}
