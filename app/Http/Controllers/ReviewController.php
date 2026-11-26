<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Notifications\ReviewReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Booking $booking): View
    {
        $this->authorize('review', $booking);

        $booking->load(['worker.workerProfile.trade']);

        return view('reviews.create', [
            'booking' => $booking,
        ]);
    }

    public function store(StoreReviewRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('review', $booking);

        $data = $request->validated();

        $review = Review::create([
            'booking_id' => $booking->id,
            'customer_id' => $request->user()->id,
            'worker_id' => $booking->worker_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'status' => ReviewStatus::Published,
        ]);

        $booking->worker->notify(
            (new ReviewReceived($review))->locale($booking->worker->locale ?: 'fr')
        );

        return redirect()
            ->route('workers.show', $booking->worker)
            ->with('status', 'Merci pour votre avis.');
    }
}
