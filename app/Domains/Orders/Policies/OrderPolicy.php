<?php

namespace App\Domains\Orders\Policies;

use App\Domains\Orders\Models\Order;
use App\Models\User;
use App\Support\ResponsibleClinicalStaffScoping;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('orders.access');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.access')
            && ResponsibleClinicalStaffScoping::userMayAccessOrderInPanel($user, $order);
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('orders.access');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.access')
            && ResponsibleClinicalStaffScoping::userMayAccessOrderInPanel($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.access')
            && ResponsibleClinicalStaffScoping::userMayAccessOrderInPanel($user, $order);
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.access')
            && ResponsibleClinicalStaffScoping::userMayAccessOrderInPanel($user, $order);
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole('Administrador');
    }
}
