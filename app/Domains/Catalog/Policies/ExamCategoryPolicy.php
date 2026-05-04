<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\ExamCategory;
use App\Models\User;

class ExamCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function view(User $user, ExamCategory $examCategory): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function update(User $user, ExamCategory $examCategory): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function delete(User $user, ExamCategory $examCategory): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function restore(User $user, ExamCategory $examCategory): bool
    {
        return $user->hasDirectPermission('catalog.access');
    }

    public function forceDelete(User $user, ExamCategory $examCategory): bool
    {
        return $user->hasRole('Administrador');
    }
}
