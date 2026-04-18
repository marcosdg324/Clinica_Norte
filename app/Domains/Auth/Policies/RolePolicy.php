<?php

namespace App\Domains\Auth\Policies;

use App\Domains\Auth\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.viewAny');
    }

    public function view(User $user, Role $model): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $model): bool
    {
        return $user->can('roles.update');
    }

    public function delete(User $user, Role $model): bool
    {
        return $user->can('roles.delete');
    }

    public function forceDelete(User $user, Role $model): bool
    {
        return $user->hasRole('Administrador');
    }
}
