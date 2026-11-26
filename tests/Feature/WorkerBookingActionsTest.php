<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerBookingActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_worker_can_accept_a_pending_booking(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($worker)
            ->patch(route('worker.bookings.accept', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(BookingStatus::Accepted, $booking->status);
        $this->assertNotNull($booking->responded_at);
    }

    public function test_worker_can_decline_a_pending_booking(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($worker)
            ->patch(route('worker.bookings.decline', $booking))
            ->assertRedirect();

        $this->assertSame(BookingStatus::Declined, $booking->refresh()->status);
    }

    public function test_worker_can_complete_an_accepted_booking(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Accepted);

        $this->actingAs($worker)
            ->patch(route('worker.bookings.complete', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertNotNull($booking->completed_at);
    }

    public function test_booking_cannot_be_completed_before_acceptance(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($worker)
            ->patch(route('worker.bookings.complete', $booking))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_other_workers_cannot_act_on_the_booking(): void
    {
        $worker = $this->worker();
        $otherWorker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($otherWorker)
            ->patch(route('worker.bookings.accept', $booking))
            ->assertForbidden();
    }

    public function test_customers_cannot_use_worker_actions(): void
    {
        $worker = $this->worker();
        $customer = User::factory()->create();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($customer)
            ->patch(route('worker.bookings.accept', $booking))
            ->assertForbidden();
    }

    public function test_worker_dashboard_lists_requests(): void
    {
        $worker = $this->worker();
        $this->booking($worker, BookingStatus::Pending, 'Réparer le tableau électrique.');

        $this->actingAs($worker)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Réparer le tableau électrique.')
            ->assertSee('Accepter')
            ->assertSee('Décliner');
    }

    private function worker(): User
    {
        $worker = User::factory()->worker()->create(['city' => 'Kaloum']);
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'service_area' => 'Kaloum',
        ]);

        return $worker->refresh();
    }

    private function booking(User $worker, BookingStatus $status, string $description = 'Réparer une prise électrique.'): Booking
    {
        $customer = User::factory()->create();

        return Booking::factory()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
            'description' => $description,
            'status' => $status,
        ]);
    }
}
