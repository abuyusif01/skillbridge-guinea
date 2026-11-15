<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_search_lists_available_workers(): void
    {
        $worker = $this->worker('electrician', 'Kaloum');

        $this->get('/search')
            ->assertOk()
            ->assertSee($worker->name)
            ->assertSee('Électricien');
    }

    public function test_search_filters_by_trade_and_city(): void
    {
        $electrician = $this->worker('electrician', 'Kaloum');
        $plumber = $this->worker('plumber', 'Ratoma');

        $this->get('/search?trade=plumber&city=Ratoma')
            ->assertOk()
            ->assertSee($plumber->name)
            ->assertDontSee($electrician->name);
    }

    public function test_search_filters_by_minimum_rating(): void
    {
        $topRated = $this->worker('electrician', 'Kaloum');
        $lowRated = $this->worker('plumber', 'Matoto');

        $this->review($topRated, 5);
        $this->review($lowRated, 2);

        $this->get('/search?min_rating=4')
            ->assertOk()
            ->assertSee($topRated->name)
            ->assertDontSee($lowRated->name);
    }

    public function test_unavailable_workers_are_hidden(): void
    {
        $worker = $this->worker('electrician', 'Kaloum', ['is_available' => false]);

        $this->get('/search')
            ->assertOk()
            ->assertDontSee($worker->name);
    }

    private function worker(string $tradeSlug, string $city, array $profileOverrides = []): User
    {
        $trade = Trade::where('slug', $tradeSlug)->firstOrFail();

        $worker = User::factory()->worker()->create(['city' => $city]);

        WorkerProfile::factory()->create(array_merge([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'service_area' => $city,
        ], $profileOverrides));

        return $worker;
    }

    private function review(User $worker, int $rating): Review
    {
        $customer = User::factory()->create();

        $booking = Booking::factory()->completed()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
        ]);

        return Review::factory()->create([
            'booking_id' => $booking->id,
            'rating' => $rating,
            'status' => ReviewStatus::Published,
        ]);
    }
}
