<?php

namespace Database\Factories;

use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkerProfile>
 */
class WorkerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->worker(),
            'trade_id' => Trade::factory(),
            'bio' => fake()->paragraph(),
            'experience_years' => fake()->numberBetween(1, 20),
            'service_area' => fake()->randomElement(['Kaloum, Conakry', 'Matoto, Conakry', 'Ratoma, Conakry']),
            'rate_min' => fake()->numberBetween(75, 500) * 1000,
            'is_available' => true,
            'verified_at' => now(),
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
        ]);
    }
}
