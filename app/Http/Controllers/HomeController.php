<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Review;
use App\Models\Trade;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $trades = Trade::query()
            ->active()
            ->withCount([
                'workerProfiles' => fn ($query) => $query->where('is_available', true),
            ])
            ->orderByDesc('worker_profiles_count')
            ->take(6)
            ->get();

        $featured = User::query()
            ->where('role', UserRole::Worker)
            ->where('is_active', true)
            ->whereHas('workerProfile', fn ($query) => $query->available()->verified())
            ->with(['workerProfile.trade'])
            ->first();

        $featuredRating = null;
        $featuredReviewsCount = 0;

        if ($featured) {
            $featuredReviewsCount = Review::query()
                ->where('worker_id', $featured->id)
                ->where('status', ReviewStatus::Published)
                ->count();

            $average = Review::query()
                ->where('worker_id', $featured->id)
                ->where('status', ReviewStatus::Published)
                ->avg('rating');

            $featuredRating = $average === null
                ? null
                : number_format((float) $average, 1, ',', ' ');
        }

        $stats = [
            'workers' => User::query()
                ->where('role', UserRole::Worker)
                ->where('is_active', true)
                ->whereHas('workerProfile')
                ->count(),
            'cities' => User::query()
                ->where('role', UserRole::Worker)
                ->whereNotNull('city')
                ->distinct()
                ->count('city'),
            'rating' => Review::query()
                ->where('status', ReviewStatus::Published)
                ->avg('rating'),
        ];

        return view('home', [
            'trades' => $trades,
            'featured' => $featured,
            'featuredRating' => $featuredRating,
            'featuredReviewsCount' => $featuredReviewsCount,
            'stats' => $stats,
        ]);
    }
}
