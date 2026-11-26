<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Review;
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

        // Aggregate reviews once instead of running a correlated subquery for
        // every worker. This keeps the filter and the pagination count working
        // identically on SQLite and MySQL.
        $ratingStats = Review::query()
            ->selectRaw('worker_id, count(*) as published_reviews_count, avg(rating) as published_reviews_avg_rating')
            ->where('status', ReviewStatus::Published)
            ->groupBy('worker_id');

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
            ->leftJoinSub($ratingStats, 'rating_stats', fn ($join) => $join->on('rating_stats.worker_id', '=', 'users.id'))
            ->when($minRating !== null, function ($query) use ($minRating) {
                // Embed the validated numeric literal: PDO binds floats as text
                // on SQLite, which makes comparisons against AVG() fail.
                $query->whereRaw(
                    'rating_stats.published_reviews_avg_rating >= '.(float) $minRating
                );
            })
            ->with(['workerProfile.trade'])
            ->select('users.*')
            ->selectRaw('coalesce(rating_stats.published_reviews_count, 0) as published_reviews_count')
            ->selectRaw('rating_stats.published_reviews_avg_rating as published_reviews_avg_rating')
            ->orderByDesc('rating_stats.published_reviews_avg_rating')
            ->orderBy('users.name')
            ->paginate(6)
            ->withQueryString();

        return view('search', [
            'workers' => $workers,
            'trades' => Trade::query()->active()->orderBy('name_fr')->get(),
            'filters' => $filters,
        ]);
    }
}
