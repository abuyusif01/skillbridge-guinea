<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->completed(),
            'customer_id' => User::factory(),
            'worker_id' => User::factory()->worker(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(10),
            'status' => ReviewStatus::Published,
        ];
    }

    public function reported(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Reported,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Hidden,
        ]);
    }
}
