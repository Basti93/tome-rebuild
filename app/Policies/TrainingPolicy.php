<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;

class TrainingPolicy
{

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole("admin")) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-training');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Training $model): bool
    {
        return $user->hasPermissionTo('view-training');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('edit-training');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $args): bool
    {
        return $user->hasPermissionTo('edit-training');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Training $model): bool
    {
        return $user->hasPermissionTo('edit-training');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Training $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Training $model): bool
    {
        return false;
    }
}
