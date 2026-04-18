<?php

namespace App\Domains\Auth\Policies;

use App\Domains\Auth\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('permissions.viewAny');
    }

    public function view(User $user, Permission $model): bool
    {
        return $user->can('permissions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('permissions.create');
    }

    public function update(User $user, Permission $model): bool
    {
        return $user->can('permissions.update');
    }

    public function delete(User $user, Permission $model): bool
    {
        return $user->can('permissions.delete');
    }

    public function forceDelete(User $user, Permission $model): bool
    {
        return $user->hasRole('Administrador');
    }
}
