<?php

namespace App\Domains\Imaging\Policies;

use App\Domains\Imaging\Models\ImagingStudy;
use App\Models\User;
use App\Support\ResponsibleClinicalStaffScoping;

class ImagingStudyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function view(User $user, ImagingStudy $study): bool
    {
        return $user->hasDirectPermission('imaging.access')
            && ResponsibleClinicalStaffScoping::userMayAccessImagingStudyInPanel($user, $study);
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function update(User $user, ImagingStudy $study): bool
    {
        return $user->hasDirectPermission('imaging.access')
            && ResponsibleClinicalStaffScoping::userMayAccessImagingStudyInPanel($user, $study);
    }

    public function approve(User $user, ImagingStudy $study): bool
    {
        return $user->hasDirectPermission('imaging.access')
            && ResponsibleClinicalStaffScoping::userMayAccessImagingStudyInPanel($user, $study)
            && $user->hasDirectPermission('imaging.approve');
    }

    public function delete(User $user, ImagingStudy $study): bool
    {
        return false; // Los estudios no se eliminan
    }

    public function restore(User $user, ImagingStudy $study): bool
    {
        return false;
    }

    public function forceDelete(User $user, ImagingStudy $study): bool
    {
        return false;
    }
}
