<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoterCategory;
use Illuminate\Auth\Access\Response;

class VoterCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VoterCategory $voterCategory): bool
    {
        return $user->id === $voterCategory->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VoterCategory $voterCategory): bool
    {
        return $user->id === $voterCategory->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VoterCategory $voterCategory): bool
    {
        return $user->id === $voterCategory->user_id;
    }

    /**
     * Determine whether the user can add voters to the category.
     */
    public function addVoters(User $user, VoterCategory $voterCategory): bool
    {
        return $user->id === $voterCategory->user_id;
    }

    /**
     * Determine whether the user can remove voters from the category.
     */
    public function removeVoters(User $user, VoterCategory $voterCategory): bool
    {
        return $user->id === $voterCategory->user_id;
    }
}
