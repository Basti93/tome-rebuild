<?php

namespace App\Policies;

use App\Models\Config;
use App\Models\User;

class ConfigPolicy
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
        return $user->hasPermissionTo('view-config');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Config $model): bool
    {
        return $user->hasPermissionTo('view-config');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('edit-config');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $args): bool
    {
        return $user->hasPermissionTo('edit-config');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Config $model): bool
    {
        return $user->hasPermissionTo('edit-config');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Config $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Config $model): bool
    {
        return false;
    }
}
