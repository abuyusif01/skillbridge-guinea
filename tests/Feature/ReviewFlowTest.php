<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_customer_can_review_a_completed_booking(): void
    {
        [$customer, $worker, $booking] = $this->completedBooking();

        $this->actingAs($customer)
            ->get(route('reviews.create', $booking))
            ->assertOk()
            ->assertSee($worker->name);

        $response = $this->actingAs($customer)->post(route('reviews.store', $booking), [
            'rating' => 4,
            'comment' => 'Travail soigné et rapide.',
        ]);

        $response->assertRedirect(route('workers.show', $worker));

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'rating' => 4,
            'comment' => 'Travail soigné et rapide.',
        ]);

        $this->get(route('workers.show', $worker))
            ->assertOk()
            ->assertSee('Travail soigné et rapide.');
    }

    public function test_a_booking_can_only_be_reviewed_once(): void
    {
        [$customer, , $booking] = $this->completedBooking();

        Review::factory()->create([
            'booking_id' => $booking->id,
            'rating' => 5,
        ]);

        $this->actingAs($customer)
            ->get(route('reviews.create', $booking))
            ->assertForbidden();
    }

    public function test_pending_bookings_cannot_be_reviewed(): void
    {
        [$customer, $worker] = $this->completedBooking();

        $booking = $this->bookingFor($customer, $worker, BookingStatus::Pending);

        $this->actingAs($customer)
            ->get(route('reviews.create', $booking))
            ->assertForbidden();
    }

    public function test_other_customers_cannot_review_the_booking(): void
    {
        [, , $booking] = $this->completedBooking();
        $otherCustomer = User::factory()->create();

        $this->actingAs($otherCustomer)
            ->get(route('reviews.create', $booking))
            ->assertForbidden();
    }

    public function test_workers_cannot_review_customers(): void
    {
        [, $worker, $booking] = $this->completedBooking();

        $this->actingAs($worker)
            ->get(route('reviews.create', $booking))
            ->assertForbidden();
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        [$customer, , $booking] = $this->completedBooking();

        $response = $this->actingAs($customer)
            ->from(route('reviews.create', $booking))
            ->post(route('reviews.store', $booking), ['rating' => 6]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * @return array{0: User, 1: User, 2: Booking}
     */
    private function completedBooking(): array
    {
        $customer = User::factory()->create();
        $worker = User::factory()->worker()->create(['city' => 'Kaloum']);
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'service_area' => 'Kaloum',
        ]);

        $worker->refresh();

        $booking = $this->bookingFor($customer, $worker, BookingStatus::Completed);

        return [$customer, $worker, $booking];
    }

    private function bookingFor(User $customer, User $worker, BookingStatus $status): Booking
    {
        return Booking::factory()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
            'status' => $status,
            'completed_at' => $status === BookingStatus::Completed ? now() : null,
        ]);
    }
}
