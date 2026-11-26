<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Notifications\BookingCancelled;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_customer_can_cancel_a_pending_booking(): void
    {
        $booking = $this->booking(BookingStatus::Pending);

        $this->actingAs($booking->customer)
            ->patch(route('bookings.cancel', $booking))
            ->assertRedirect();

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_customer_can_cancel_an_accepted_booking(): void
    {
        $booking = $this->booking(BookingStatus::Accepted);

        $this->actingAs($booking->customer)
            ->patch(route('bookings.cancel', $booking))
            ->assertRedirect();

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_finished_bookings_cannot_be_cancelled(): void
    {
        foreach ([BookingStatus::Completed, BookingStatus::Declined, BookingStatus::Cancelled] as $status) {
            $booking = $this->booking($status);

            $this->actingAs($booking->customer)
                ->patch(route('bookings.cancel', $booking))
                ->assertForbidden();

            $this->assertSame($status, $booking->fresh()->status);
        }
    }

    public function test_other_people_cannot_cancel_the_booking(): void
    {
        $booking = $this->booking(BookingStatus::Pending);
        $otherCustomer = User::factory()->create();

        $this->actingAs($otherCustomer)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->actingAs($booking->worker)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_worker_is_notified_when_the_customer_cancels(): void
    {
        Notification::fake();

        $booking = $this->booking(BookingStatus::Pending);

        $this->actingAs($booking->customer)
            ->patch(route('bookings.cancel', $booking));

        Notification::assertSentTo(
            $booking->worker,
            BookingCancelled::class,
            fn (BookingCancelled $notification) => $notification->booking->id === $booking->id
        );
    }

    public function test_customer_dashboard_shows_the_cancel_action_only_when_allowed(): void
    {
        $customer = User::factory()->create();
        $worker = $this->worker();

        $pending = $this->booking(BookingStatus::Pending, $customer, $worker);
        $completed = $this->booking(BookingStatus::Completed, $customer, $worker);

        $response = $this->actingAs($customer)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee($pending->description)
            ->assertSee('Annuler la réservation');

        $this->assertSame(1, substr_count($response->getContent(), 'Annuler la réservation'));

        $this->actingAs($customer)
            ->patch(route('bookings.cancel', $completed))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $booking = $this->booking(BookingStatus::Pending);

        $this->patch(route('bookings.cancel', $booking))->assertRedirect(route('login'));
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

    private function booking(BookingStatus $status, ?User $customer = null, ?User $worker = null): Booking
    {
        $worker ??= $this->worker();

        return Booking::factory()->create([
            'customer_id' => ($customer ?? User::factory()->create())->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
            'description' => 'Réparer une prise électrique.',
            'status' => $status,
        ]);
    }
}
