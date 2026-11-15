<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;

class WorkerBookingController extends Controller
{
    public function accept(Booking $booking): RedirectResponse
    {
        $this->authorize('respond', $booking);

        $booking->forceFill([
            'status' => BookingStatus::Accepted,
            'responded_at' => now(),
        ])->save();

        return back()->with('status', 'Demande acceptée.');
    }

    public function decline(Booking $booking): RedirectResponse
    {
        $this->authorize('respond', $booking);

        $booking->forceFill([
            'status' => BookingStatus::Declined,
            'responded_at' => now(),
        ])->save();

        return back()->with('status', 'Demande déclinée.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->authorize('complete', $booking);

        $booking->forceFill([
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ])->save();

        return back()->with('status', 'Travail marqué comme terminé.');
    }
}
