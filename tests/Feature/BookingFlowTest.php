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

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_customers_can_view_the_booking_form(): void
    {
        $customer = User::factory()->create();
        $worker = $this->worker();

        $this->actingAs($customer)
            ->get(route('bookings.create', $worker))
            ->assertOk()
            ->assertSee($worker->name)
            ->assertSee('Envoyer la demande');
    }

    public function test_customers_can_create_a_booking(): void
    {
        $customer = User::factory()->create();
        $worker = $this->worker();

        $response = $this->actingAs($customer)->post(route('bookings.store', $worker), [
            'description' => 'Réparer une prise électrique dans le salon.',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'time_slot' => 'morning',
            'address' => 'Kaloum, rue KA-020',
            'contact_phone' => '+224 621 000 099',
        ]);

        $booking = Booking::firstOrFail();

        $response->assertRedirect(route('bookings.success', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'status' => BookingStatus::Pending->value,
            'contact_phone' => '+224621000099',
        ]);

        $this->actingAs($customer)
            ->get(route('bookings.success', $booking))
            ->assertOk()
            ->assertSee('Demande envoyée')
            ->assertSee($worker->name);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $worker = $this->worker();

        $this->get(route('bookings.create', $worker))->assertRedirect(route('login'));
    }

    public function test_workers_and_admins_cannot_create_bookings(): void
    {
        $worker = $this->worker();
        $otherWorker = User::factory()->worker()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($otherWorker)
            ->get(route('bookings.create', $worker))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('bookings.create', $worker))
            ->assertForbidden();
    }

    public function test_other_customers_cannot_view_the_confirmation(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $worker = $this->worker();

        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
        ]);

        $this->actingAs($otherCustomer)
            ->get(route('bookings.success', $booking))
            ->assertForbidden();
    }

    public function test_booking_validation_rejects_past_dates_and_missing_fields(): void
    {
        $customer = User::factory()->create();
        $worker = $this->worker();

        $response = $this->actingAs($customer)
            ->from(route('bookings.create', $worker))
            ->post(route('bookings.store', $worker), [
                'description' => '',
                'preferred_date' => now()->subDay()->toDateString(),
                'time_slot' => 'invalid',
            ]);

        $response->assertSessionHasErrors([
            'description',
            'preferred_date',
            'time_slot',
            'address',
            'contact_phone',
        ]);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_unavailable_workers_cannot_be_booked(): void
    {
        $customer = User::factory()->create();
        $worker = $this->worker(['is_available' => false]);

        $this->actingAs($customer)
            ->get(route('bookings.create', $worker))
            ->assertNotFound();
    }

    public function test_customer_dashboard_lists_the_booking(): void
    {
        $customer = User::factory()->create();
        $worker = $this->worker();

        Booking::factory()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $worker->workerProfile->trade_id,
            'description' => 'Installation de trois lampes.',
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Installation de trois lampes.')
            ->assertSee($worker->name);
    }

    private function worker(array $profileOverrides = []): User
    {
        $worker = User::factory()->worker()->create([
            'city' => 'Kaloum',
        ]);

        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create(array_merge([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'service_area' => 'Kaloum, Matoto',
            'is_available' => true,
        ], $profileOverrides));

        return $worker->refresh();
    }
}
