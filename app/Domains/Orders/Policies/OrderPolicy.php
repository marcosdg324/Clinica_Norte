<?php

namespace App\Domains\Orders\Policies;

use App\Domains\Orders\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasDirectPermission('orders.viewAny');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->hasDirectPermission('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.update');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.delete');
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->hasDirectPermission('orders.delete');
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole('Administrador');
    }
}
