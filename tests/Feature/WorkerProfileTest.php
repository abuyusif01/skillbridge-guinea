<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_shows_worker_details_and_reviews(): void
    {
        $this->seed(TradeSeeder::class);

        $worker = User::factory()->worker()->create(['name' => 'Mamadou Diallo', 'city' => 'Kaloum']);
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'bio' => 'Spécialiste des installations électriques.',
            'service_area' => 'Kaloum, Matoto',
            'verified_at' => now(),
        ]);

        $customer = User::factory()->create(['name' => 'Amadou Bah']);
        $booking = Booking::factory()->completed()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $trade->id,
        ]);

        Review::factory()->create([
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Excellent travail, très rapide !',
        ]);

        $this->get(route('workers.show', $worker))
            ->assertOk()
            ->assertSee('Mamadou Diallo')
            ->assertSee('Spécialiste des installations électriques.')
            ->assertSee('Excellent travail, très rapide !')
            ->assertSee('5,0')
            ->assertSee('Réserver ce travailleur');
    }

    public function test_non_workers_return_not_found(): void
    {
        $customer = User::factory()->create();

        $this->get(route('workers.show', $customer))->assertNotFound();
    }
}
