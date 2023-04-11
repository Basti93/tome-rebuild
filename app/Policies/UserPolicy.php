<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->hasPermissionTo('view-user');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view-user');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('edit-user');
    }

    public function updateMe(User $user, $args): bool
    {
        if ($user->id != $args['id']) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $args): bool
    {
        if (isset($args['roles'])) {
            if (!$user->hasPermissionTo('edit-role')) {
                return false;
            }
            if ($user->id == $args['id']) {
                return false;
            }
            if (in_array(1, $args['roles']) && !$user->hasPermissionTo('edit-role-admin')) {
                return false;
            }
            if (in_array(2, $args['roles']) && !$user->hasPermissionTo('edit-role-coach')) {
                return false;
            }
        }
        if (isset($args['groups']) && !$user->hasPermissionTo('edit-group')) {
            return false;
        }
        return $user->hasPermissionTo('edit-user');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
           return false;
        }
        return !$model->hasRole("coach")
            && !$model->hasRole("admin")
            && $user->hasPermissionTo('edit-user');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
