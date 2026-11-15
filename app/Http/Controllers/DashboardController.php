<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isWorker()) {
            $user->loadMissing('workerProfile.trade');

            $bookings = Booking::forWorker($user)
                ->with(['customer', 'trade', 'review'])
                ->latest()
                ->get();

            return view('dashboards.worker', [
                'user' => $user,
                'pending' => $bookings->filter(fn ($booking) => $booking->status === BookingStatus::Pending)->values(),
                'accepted' => $bookings->filter(fn ($booking) => $booking->status === BookingStatus::Accepted)->values(),
                'completed' => $bookings->filter(fn ($booking) => $booking->status === BookingStatus::Completed)->values(),
                'stats' => [
                    ['label' => 'Demandes en attente', 'value' => $bookings->where('status', BookingStatus::Pending)->count()],
                    ['label' => 'Acceptées', 'value' => $bookings->where('status', BookingStatus::Accepted)->count()],
                    ['label' => 'Terminées', 'value' => $bookings->where('status', BookingStatus::Completed)->count()],
                    ['label' => 'Note moyenne', 'value' => $this->averageRating($user)],
                ],
            ]);
        }

        $bookings = $user->bookingsAsCustomer()
            ->with(['worker', 'trade', 'review'])
            ->latest()
            ->get();

        return view('dashboards.customer', [
            'user' => $user,
            'bookings' => $bookings,
            'stats' => [
                ['label' => 'Réservations', 'value' => $bookings->count()],
                ['label' => 'En attente', 'value' => $bookings->where('status', BookingStatus::Pending)->count()],
                ['label' => 'Terminées', 'value' => $bookings->where('status', BookingStatus::Completed)->count()],
                ['label' => 'Avis donnés', 'value' => $user->reviewsAsCustomer()->count()],
            ],
        ]);
    }

    private function averageRating(User $user): string
    {
        $average = Review::query()
            ->where('worker_id', $user->id)
            ->published()
            ->avg('rating');

        return $average === null ? '—' : number_format((float) $average, 1, ',', ' ');
    }
}
