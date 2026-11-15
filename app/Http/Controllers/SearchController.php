<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'trade' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'min_rating' => ['nullable', 'numeric', 'between:1,5'],
        ]);

        $trade = $filters['trade'] ?? null;
        $city = $filters['city'] ?? null;
        $minRating = isset($filters['min_rating']) && $filters['min_rating'] !== '' ? (float) $filters['min_rating'] : null;

        $workers = User::query()
            ->where('role', UserRole::Worker)
            ->where('is_active', true)
            ->whereHas('workerProfile', function ($query) use ($trade, $city) {
                $query->where('is_available', true);

                if ($trade) {
                    $query->whereHas('trade', fn ($tradeQuery) => $tradeQuery->where('slug', $trade));
                }

                if ($city) {
                    $query->where(function ($location) use ($city) {
                        $location->where('service_area', 'like', "%{$city}%")
                            ->orWhereHas('user', fn ($userQuery) => $userQuery->where('city', 'like', "%{$city}%"));
                    });
                }
            })
            ->with(['workerProfile.trade'])
            ->withCount([
                'reviewsAsWorker as published_reviews_count' => fn ($query) => $query->where('status', ReviewStatus::Published),
            ])
            ->withAvg([
                'reviewsAsWorker as published_reviews_avg_rating' => fn ($query) => $query->where('status', ReviewStatus::Published),
            ], 'rating')
            ->when($minRating !== null, function ($query) use ($minRating) {
                // Cast to a numeric literal; PDO binds floats as text on SQLite,
                // which breaks comparisons against the AVG() result.
                $query->whereRaw(
                    '(select avg(rating) from reviews where reviews.worker_id = users.id and reviews.status = ?) >= '.(float) $minRating,
                    [ReviewStatus::Published->value]
                );
            })
            ->orderByDesc('published_reviews_avg_rating')
            ->orderBy('name')
            ->get();

        return view('search', [
            'workers' => $workers,
            'trades' => Trade::query()->active()->orderBy('name_fr')->get(),
            'filters' => $filters,
        ]);
    }
}
