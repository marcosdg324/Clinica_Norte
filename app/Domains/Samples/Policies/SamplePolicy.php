<?php

namespace App\Domains\Samples\Policies;

use App\Domains\Samples\Models\Sample;
use App\Models\User;
use App\Support\ResponsibleClinicalStaffScoping;

class SamplePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('samples.access');
    }

    public function view(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.access')
            && ResponsibleClinicalStaffScoping::userMayAccessSampleInPanel($user, $sample);
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('samples.access');
    }

    public function update(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.access')
            && ResponsibleClinicalStaffScoping::userMayAccessSampleInPanel($user, $sample);
    }

    public function reject(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.access')
            && ResponsibleClinicalStaffScoping::userMayAccessSampleInPanel($user, $sample)
            && $user->hasDirectPermission('samples.reject')
            && $sample->status === 'en_analisis';
    }

    public function approve(User $user, Sample $sample): bool
    {
        return $user->hasDirectPermission('samples.access')
            && ResponsibleClinicalStaffScoping::userMayAccessSampleInPanel($user, $sample)
            && $user->hasDirectPermission('samples.approve');
    }

    public function delete(User $user, Sample $sample): bool
    {
        return false; // Las muestras no se eliminan
    }

    public function restore(User $user, Sample $sample): bool
    {
        return false;
    }

    public function forceDelete(User $user, Sample $sample): bool
    {
        return false;
    }
}
