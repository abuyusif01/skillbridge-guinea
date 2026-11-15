<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin()
            || $user->id === $booking->customer_id
            || $user->id === $booking->worker_id;
    }

    public function respond(User $user, Booking $booking): bool
    {
        return $user->id === $booking->worker_id && $booking->isPending();
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $user->id === $booking->worker_id && $booking->isAccepted();
    }

    public function review(User $user, Booking $booking): bool
    {
        return $user->id === $booking->customer_id && $booking->canBeReviewed();
    }
}
