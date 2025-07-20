<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PropertyPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nightly_rate' => $this->faker->numberBetween(500000, 5000000),
            'weekly_rate' => $this->faker->numberBetween(7500000, 20000000),
            'monthly_rate' => $this->faker->numberBetween(10000000, 80000000),
            'currency' => 'IDR', // Default currency
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'property_id' => \App\Models\Property::factory(), // Assuming Property factory exists
        ];
    }
}
