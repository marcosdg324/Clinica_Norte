<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function view(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function restore(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function forceDelete(User $user, Exam $exam): bool
    {
        return $user->hasRole('Administrador');
    }
}
