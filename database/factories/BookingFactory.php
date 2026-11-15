<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'worker_id' => User::factory()->worker(),
            'trade_id' => Trade::factory(),
            'description' => fake()->sentence(12),
            'preferred_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'time_slot' => fake()->randomElement(['morning', 'afternoon', 'evening']),
            'address' => fake()->address(),
            'contact_phone' => fake()->e164PhoneNumber(),
            'status' => BookingStatus::Pending,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Completed,
            'responded_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Declined,
            'responded_at' => now(),
        ]);
    }
}
