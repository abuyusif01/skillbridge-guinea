<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\View\View;

class WorkerProfileController extends Controller
{
    public function __invoke(User $worker): View
    {
        abort_unless(
            $worker->role === UserRole::Worker && $worker->is_active && $worker->workerProfile,
            404
        );

        $worker->load(['workerProfile.trade']);

        $reviewQuery = $worker->reviewsAsWorker()->published();
        $reviewCount = $reviewQuery->count();
        $averageRating = $reviewQuery->avg('rating');
        $ratingCounts = $worker->reviewsAsWorker()
            ->published()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $reviews = $worker->reviewsAsWorker()
            ->published()
            ->with('customer')
            ->latest()
            ->paginate(10);

        $completedJobs = $worker->bookingsAsWorker()
            ->where('status', BookingStatus::Completed)
            ->count();

        return view('workers.show', [
            'worker' => $worker,
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'averageRating' => $averageRating,
            'ratingCounts' => $ratingCounts,
            'completedJobs' => $completedJobs,
        ]);
    }
}
