<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin() || $user->isManager() || $user->isCustomer();
    }

    public function view(User $user, Order $order)
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $order->belongsToUser($user);
    }

    public function create(User $user)
    {
        return $user->isAdmin() || $user->isCustomer(); // Maybe managers cannot create orders. Can they? I am confused. However,
    }

    public function update(User $user, Order $order)
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return false;
    }


    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
