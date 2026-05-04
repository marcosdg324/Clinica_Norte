<?php

namespace App\Domains\Auth\Policies;

use App\Domains\Auth\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function view(User $user, Permission $model): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function update(User $user, Permission $model): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function delete(User $user, Permission $model): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function forceDelete(User $user, Permission $model): bool
    {
        return $user->hasRole('Administrador');
    }
}
