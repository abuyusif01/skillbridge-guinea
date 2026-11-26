<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Requests\UpdateWorkerProfileRequest;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function edit(Request $request): View
    {
        $user = $request->user()->load('workerProfile.trade');

        abort_unless($user->workerProfile !== null, 404);

        return view('workers.edit', [
            'user' => $user,
            'profile' => $user->workerProfile,
            'trades' => Trade::query()->active()->orderBy('name_fr')->get(),
        ]);
    }

    public function update(UpdateWorkerProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->workerProfile;

        abort_unless($profile !== null, 404);

        $profile->update($request->validated());

        return redirect()
            ->route('worker.profile.edit')
            ->with('status', 'Votre profil a été mis à jour.');
    }
}
