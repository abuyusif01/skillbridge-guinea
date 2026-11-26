<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Notifications\BookingStatusChanged;
use App\Notifications\NewBookingRequest;
use App\Notifications\ReviewReceived;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_a_new_booking_request_notifies_the_worker(): void
    {
        Notification::fake();

        $customer = User::factory()->create();
        $worker = $this->worker();

        $this->actingAs($customer)->post(route('bookings.store', $worker), [
            'description' => 'Réparer une prise électrique.',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'time_slot' => 'morning',
            'address' => 'Kaloum, rue KA-020',
            'contact_phone' => '+224 621 000 099',
        ]);

        Notification::assertSentTo(
            $worker,
            NewBookingRequest::class,
            fn (NewBookingRequest $notification) => $notification->booking->worker_id === $worker->id
        );
    }

    public function test_accepting_a_booking_notifies_the_customer(): void
    {
        Notification::fake();

        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($worker)
            ->patch(route('worker.bookings.accept', $booking))
            ->assertRedirect();

        Notification::assertSentTo(
            $booking->customer,
            BookingStatusChanged::class,
            fn (BookingStatusChanged $notification) => $notification->booking->status === BookingStatus::Accepted
        );
    }

    public function test_declining_a_booking_notifies_the_customer(): void
    {
        Notification::fake();

        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $this->actingAs($worker)->patch(route('worker.bookings.decline', $booking));

        Notification::assertSentTo(
            $booking->customer,
            BookingStatusChanged::class,
            fn (BookingStatusChanged $notification) => $notification->booking->status === BookingStatus::Declined
        );
    }

    public function test_completing_a_booking_notifies_the_customer(): void
    {
        Notification::fake();

        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Accepted);

        $this->actingAs($worker)->patch(route('worker.bookings.complete', $booking));

        Notification::assertSentTo(
            $booking->customer,
            BookingStatusChanged::class,
            fn (BookingStatusChanged $notification) => $notification->booking->status === BookingStatus::Completed
        );
    }

    public function test_a_new_review_notifies_the_worker(): void
    {
        Notification::fake();

        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Completed);

        $this->actingAs($booking->customer)->post(route('reviews.store', $booking), [
            'rating' => 5,
            'comment' => 'Travail rapide et soigné.',
        ]);

        Notification::assertSentTo(
            $worker,
            ReviewReceived::class,
            fn (ReviewReceived $notification) => $notification->review->worker_id === $worker->id
        );
    }

    public function test_notifications_are_stored_in_the_inbox(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $worker->notify((new NewBookingRequest($booking))->locale('fr'));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $worker->id,
            'notifiable_type' => $worker->getMorphClass(),
            'type' => NewBookingRequest::class,
        ]);

        $this->actingAs($worker)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Nouvelle demande de réservation')
            ->assertSee($booking->customer->name);
    }

    public function test_the_header_shows_the_unread_notification_count(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $worker->notify((new NewBookingRequest($booking))->locale('fr'));

        $this->actingAs($worker)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('notification-count');
    }

    public function test_opening_a_notification_marks_it_read_and_redirects(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);
        $worker->notify((new NewBookingRequest($booking))->locale('fr'));

        $notification = $worker->notifications()->firstOrFail();

        $this->actingAs($worker)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_open_another_users_notification(): void
    {
        $worker = $this->worker();
        $otherWorker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);
        $worker->notify((new NewBookingRequest($booking))->locale('fr'));

        $notification = $worker->notifications()->firstOrFail();

        $this->actingAs($otherWorker)
            ->get(route('notifications.open', $notification->id))
            ->assertNotFound();
    }

    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $worker = $this->worker();
        $booking = $this->booking($worker, BookingStatus::Pending);

        $worker->notify((new NewBookingRequest($booking))->locale('fr'));
        $worker->notify((new BookingStatusChanged($booking))->locale('fr'));

        $this->assertSame(2, $worker->unreadNotifications()->count());

        $this->actingAs($worker)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $worker->unreadNotifications()->count());
    }

    public function test_guests_cannot_view_notifications(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_notifications_are_rendered_in_the_users_locale(): void
    {
        $worker = $this->worker(['locale' => 'en']);
        $booking = $this->booking($worker, BookingStatus::Pending);

        $worker->notify((new NewBookingRequest($booking))->locale('en'));

        $notification = $worker->notifications()->firstOrFail();

        $this->assertSame('New booking request', $notification->data['title']);
    }

    private function worker(array $overrides = []): User
    {
        $worker = User::factory()->worker()->create(array_merge([
            'city' => 'Kaloum',
            'locale' => 'fr',
        ], $overrides));

        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'service_area' => 'Kaloum',
        ]);

        return $worker->refresh();
    }

    private function booking(User $worker, BookingStatus $status): Booking
    {
        $customer = User::factory()->create(['locale' => 'fr']);

        return Booking::factory()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
            'description' => 'Réparer une prise électrique.',
            'status' => $status,
        ]);
    }
}
