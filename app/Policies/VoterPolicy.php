<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voter;

class VoterPolicy
{
    /**
     * Determine if the user can view any voters.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view voters (filtered by role in controller)
    }

    /**
     * Determine if the user can view the voter.
     */
    public function view(User $user, Voter $voter): bool
    {
        // Superadmin can view all
        if ($user->isSuperadmin()) {
            return true;
        }

        // Team Lead and Booth Agent can view voters in their ward
        if (($user->isTeamLead() || $user->isBoothAgent()) && $user->ward_id === $voter->ward_id) {
            return true;
        }

        // Worker can only view assigned voters
        if ($user->isWorker()) {
            return $user->assignedVoters()->where('voters.id', $voter->id)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can create voters.
     */
    public function create(User $user): bool
    {
        return $user->isSuperadmin();
    }

    /**
     * Determine if the user can update the voter.
     */
    public function update(User $user, Voter $voter): bool
    {
        return $user->isSuperadmin();
    }

    /**
     * Determine if the user can delete the voter.
     */
    public function delete(User $user, Voter $voter): bool
    {
        return $user->isSuperadmin();
    }

    /**
     * Determine if the user can update the voter status (not_voted, voted, visited).
     */
    public function updateStatus(User $user, Voter $voter): bool
    {
        // Superadmin can update any status
        if ($user->isSuperadmin()) {
            return true;
        }

        // Team Lead and Booth Agent can update status for voters in their ward
        if (($user->isTeamLead() || $user->isBoothAgent()) && $user->ward_id === $voter->ward_id) {
            return true;
        }

        // Workers can update status to 'visited' for assigned voters only
        if ($user->isWorker()) {
            return $user->assignedVoters()->where('voters.id', $voter->id)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can add/update remark.
     */
    public function updateRemark(User $user, Voter $voter): bool
    {
        // Only workers assigned to this voter can update remark
        if ($user->isWorker()) {
            return $user->assignedVoters()->where('voters.id', $voter->id)->exists();
        }

        return false;
    }
}
