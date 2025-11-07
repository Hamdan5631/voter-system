<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    /**
     * Get overview statistics.
     */
    public function overview(Request $request)
    {
        $user = $request->user();
        $query = Voter::query();

        // Filter by role
        if ($user->isSuperadmin()) {
            if ($request->has('ward_id')) {
                $query->where('ward_id', $request->ward_id);
            }
            if ($request->has('worker_id')) {
                $query->whereHas('assignment', function ($q) use ($request) {
                    $q->where('worker_id', $request->worker_id);
                });
            }
        } elseif ($user->isTeamLead()) {
            $query->where('ward_id', $user->ward_id);
            if ($request->has('worker_id')) {
                $query->whereHas('assignment', function ($q) use ($request) {
                    $q->where('worker_id', $request->worker_id);
                });
            }
        } elseif ($user->isBoothAgent()) {
            // Booth Agent sees data for their ward only
            $query->where('ward_id', $user->ward_id);
        } elseif ($user->isWorker()) {
            // Worker sees data for their assigned voters only
            $query->whereHas('assignment', function ($q) use ($user) {
                $q->where('worker_id', $user->id);
            });
        } else {
            // No access for other roles
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Get statistics
        $totalVoters = $query->count();
        $votedCount = (clone $query)->whereHas('latestStatus', function ($q) {
            $q->where('status', 'voted');
        })->count();
        $visitedCount = (clone $query)->whereHas('latestStatus', function ($q) {
            $q->where('status', 'visited');
        })->count();
        $notVotedCount = $totalVoters - $votedCount;
        $assignedCount = (clone $query)->has('assignment')->count();
        $notAssignedCount = (clone $query)->doesntHave('assignment')->count();
        $notVisitedCount = $totalVoters - $visitedCount;
        $votedPercentage = $totalVoters > 0 ? ($votedCount / $totalVoters) * 100 : 0;
        $notVotedPercentage = $totalVoters > 0 ? ($notVotedCount / $totalVoters) * 100 : 0;


        return response()->json([
            'data' => [
                'total_voters' => $totalVoters,
                'voted_count' => $votedCount,
                'visited_count' => $visitedCount,
                'not_voted_count' => $notVotedCount,
                'not_visited_count' => $notVisitedCount,
                'assigned_count' => $assignedCount,
                'not_assigned_count' => $notAssignedCount,
                'voted_percentage' => round($votedPercentage, 2),
                'not_voted_percentage' => round($notVotedPercentage, 2),
                'role' => $user->role,
                'ward_id' => $user->ward_id ?? null,
            ],
        ], 200);
    }
}