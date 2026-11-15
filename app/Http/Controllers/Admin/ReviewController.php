<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Avis supprimé.');
    }
}
