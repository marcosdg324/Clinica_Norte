<?php

namespace App\Domains\Auth\Policies;

use App\Domains\Auth\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function view(User $user, Role $model): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function update(User $user, Role $model): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function delete(User $user, Role $model): bool
    {
        return $user->hasDirectPermission('auth.access');
    }

    public function forceDelete(User $user, Role $model): bool
    {
        return $user->hasRole('Administrador');
    }
}
