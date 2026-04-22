<?php

namespace App\Domains\Orders\Policies;

use App\Domains\Orders\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('exams.viewAny');
    }

    public function view(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('exams.create');
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('exams.update');
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('exams.delete');
    }

    public function restore(User $user, Exam $exam): bool
    {
        return $user->hasDirectPermission('exams.delete');
    }

    public function forceDelete(User $user, Exam $exam): bool
    {
        return $user->hasRole('Administrador');
    }
}
