<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isWorker()) {
            $user->loadMissing('workerProfile.trade');

            $stats = [
                ['label' => 'Demandes en attente', 'value' => Booking::forWorker($user)->pending()->count()],
                ['label' => 'Terminées', 'value' => Booking::forWorker($user)->where('status', BookingStatus::Completed)->count()],
                ['label' => 'Avis reçus', 'value' => Review::where('worker_id', $user->id)->published()->count()],
                ['label' => 'Note moyenne', 'value' => $this->averageRating($user)],
            ];
        } elseif ($user->isAdmin()) {
            $stats = [
                ['label' => 'Utilisateurs', 'value' => User::count()],
                ['label' => 'Professionnels', 'value' => User::where('role', UserRole::Worker)->count()],
                ['label' => 'Réservations', 'value' => Booking::count()],
                ['label' => 'Avis signalés', 'value' => Review::where('status', ReviewStatus::Reported)->count()],
            ];
        } else {
            $stats = [
                ['label' => 'Réservations', 'value' => $user->bookingsAsCustomer()->count()],
                ['label' => 'En attente', 'value' => $user->bookingsAsCustomer()->where('status', BookingStatus::Pending)->count()],
                ['label' => 'Terminées', 'value' => $user->bookingsAsCustomer()->where('status', BookingStatus::Completed)->count()],
                ['label' => 'Avis donnés', 'value' => $user->reviewsAsCustomer()->count()],
            ];
        }

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    private function averageRating(User $user): string
    {
        $average = Review::where('worker_id', $user->id)
            ->published()
            ->avg('rating');

        return $average === null ? '—' : number_format((float) $average, 1, ',', ' ');
    }
}
