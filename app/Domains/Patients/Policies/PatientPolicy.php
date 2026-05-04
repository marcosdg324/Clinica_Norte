<?php

namespace App\Domains\Patients\Policies;

use App\Domains\Patients\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('patients.access');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('patients.access');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.access');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.access');
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->hasDirectPermission('patients.access');
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->hasRole('Administrador');
    }
}
