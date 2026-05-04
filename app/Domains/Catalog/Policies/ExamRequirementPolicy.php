<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\ExamRequirement;
use App\Models\User;

class ExamRequirementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function view(User $user, ExamRequirement $examRequirement): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function update(User $user, ExamRequirement $examRequirement): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function delete(User $user, ExamRequirement $examRequirement): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function restore(User $user, ExamRequirement $examRequirement): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function forceDelete(User $user, ExamRequirement $examRequirement): bool
    {
        return $user->hasRole('Administrador');
    }
}
