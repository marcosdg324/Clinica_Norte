<?php

namespace App\Domains\Samples\Policies;

use App\Domains\Samples\Models\Sample;
use App\Models\User;

class SamplePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('samples.viewAny');
    }

    public function view(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.view');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('samples.create');
    }

    public function update(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.update');
    }

    public function delete(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.delete');
    }

    public function restore(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.delete');
    }

    public function forceDelete(User $user, Sample $sample): bool
    {
        return $user->hasRole('Administrador');
    }
}
