<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\ExamParameter;
use App\Models\User;

class ExamParameterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function view(User $user, ExamParameter $examParameter): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function update(User $user, ExamParameter $examParameter): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function delete(User $user, ExamParameter $examParameter): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function restore(User $user, ExamParameter $examParameter): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function forceDelete(User $user, ExamParameter $examParameter): bool
    {
        return $user->hasRole('Administrador');
    }
}
