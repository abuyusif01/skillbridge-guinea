<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, User $worker): View
    {
        $this->ensureBookableWorker($worker);
        abort_unless($request->user()->isCustomer(), 403);
        $worker->loadMissing('workerProfile.trade');

        return view('bookings.create', [
            'worker' => $worker,
        ]);
    }

    public function store(StoreBookingRequest $request, User $worker): RedirectResponse
    {
        $this->ensureBookableWorker($worker);
        abort_unless($request->user()->isCustomer(), 403);

        $data = $request->validated();

        $booking = Booking::create([
            'customer_id' => $request->user()->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
            'description' => $data['description'],
            'preferred_date' => $data['preferred_date'],
            'time_slot' => $data['time_slot'],
            'address' => $data['address'],
            'contact_phone' => $data['contact_phone'],
            'status' => BookingStatus::Pending,
        ]);

        return redirect()->route('bookings.success', $booking);
    }

    public function success(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['worker', 'trade']);

        return view('bookings.success', [
            'booking' => $booking,
        ]);
    }

    private function ensureBookableWorker(User $worker): void
    {
        abort_unless(
            $worker->role === UserRole::Worker
                && $worker->is_active
                && $worker->workerProfile
                && $worker->workerProfile->is_available,
            404
        );
    }
}
