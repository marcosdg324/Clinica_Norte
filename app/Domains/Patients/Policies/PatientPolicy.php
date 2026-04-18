<?php

namespace App\Domains\Patients\Policies;

use App\Domains\Patients\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('patients.viewAny');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.view');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('patients.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.update');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.delete');
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.delete');
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->hasRole('Administrador');
    }
}
