<?php

namespace App\Domains\Imaging\Policies;

use App\Domains\Imaging\Models\ImagingEquipment;
use App\Models\User;

class ImagingEquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function view(User $user, ImagingEquipment $equipment): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function update(User $user, ImagingEquipment $equipment): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function delete(User $user, ImagingEquipment $equipment): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function restore(User $user, ImagingEquipment $equipment): bool
    {
        return $user->hasDirectPermission('imaging.access');
    }

    public function forceDelete(User $user, ImagingEquipment $equipment): bool
    {
        return false;
    }
}
