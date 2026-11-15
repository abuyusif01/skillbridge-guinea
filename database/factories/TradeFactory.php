<?php

namespace Database\Factories;

use App\Models\Trade;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Trade>
 */
class TradeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'slug' => Str::slug($name),
            'name_fr' => ucfirst($name),
            'name_en' => ucfirst($name),
            'icon' => 'wrench',
            'is_active' => true,
        ];
    }
}
