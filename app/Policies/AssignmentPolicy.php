<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voter;

class AssignmentPolicy
{
    /**
     * Determine if the user can assign voters to workers.
     */
    public function assign(User $user, Voter $voter, User $worker): bool
    {
        // Only team lead of the ward can assign voters to workers
        if (!$user->isTeamLead()) {
            return false;
        }

        // Voter must be in the same ward as team lead
        if ($user->ward_id !== $voter->ward_id) {
            return false;
        }

        // Worker must be in the same ward
        if ($user->ward_id !== $worker->ward_id || !$worker->isWorker()) {
            return false;
        }

        return true;
    }
}
