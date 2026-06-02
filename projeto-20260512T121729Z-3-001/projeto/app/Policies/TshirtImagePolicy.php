<?php

namespace App\Policies;

use App\Models\Tshirt_image;
use App\Models\User;

class TshirtImagePolicy
{
    /**
     * Customer can view their own private images.
     * Admin/Employee can view any (for order context).
     * Catalog images (customer_id = null) are public.
     */
    public function view(User $user, Tshirt_image $tshirtImage): bool
    {
        // Catalog images are public
        if (is_null($tshirtImage->customer_id)) {
            return true;
        }

        // Owner
        if ($user->user_type === 'C' && $tshirtImage->customer_id === $user->id) {
            return true;
        }

        // Admin or Employee (for order context)
        return in_array($user->user_type, ['A', 'F']);
    }

    /**
     * Only authenticated customers can upload private images.
     */
    public function create(User $user): bool
    {
        return $user->user_type === 'C';
    }

    /**
     * Only the owner can update their private image.
     */
    public function update(User $user, Tshirt_image $tshirtImage): bool
    {
        return $user->user_type === 'C'
            && $tshirtImage->customer_id === $user->id;
    }

    /**
     * Only the owner can delete their private image.
     */
    public function delete(User $user, Tshirt_image $tshirtImage): bool
    {
        return $user->user_type === 'C'
            && $tshirtImage->customer_id === $user->id;
    }
}
