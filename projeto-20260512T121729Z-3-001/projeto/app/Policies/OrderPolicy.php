<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin and Staff can view any order
        if ($user->user_type === 'A' || $user->user_type === 'F') {
            return true;
        }

        // Customer can only view their own orders
        return $user->user_type === 'C' && (int) $order->customer_id === (int) $user->id;
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Status can only be changed if the order is currently pending
        if ($order->status !== 'pending') {
            return false;
        }

        // Admin and Staff can update status of pending orders
        return $user->user_type === 'A' || $user->user_type === 'F';
    }
}
