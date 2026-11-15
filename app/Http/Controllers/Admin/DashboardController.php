<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Travailleurs', 'value' => User::where('role', UserRole::Worker)->count()],
                ['label' => 'Clients', 'value' => User::where('role', UserRole::Customer)->count()],
                ['label' => 'Réservations', 'value' => Booking::count()],
                ['label' => 'Avis signalés', 'value' => Review::where('status', ReviewStatus::Reported)->count()],
            ],
            'users' => User::query()->orderByDesc('created_at')->limit(50)->get(),
            'reviews' => Review::query()->with(['customer', 'worker'])->latest()->limit(50)->get(),
            'bookings' => Booking::query()->with(['customer', 'worker'])->latest()->limit(50)->get(),
            'pendingBookings' => Booking::where('status', BookingStatus::Pending)->count(),
        ]);
    }
}
