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
            'nightly_rate' => $this->faker->randomFloat(2, 50, 500),
            'weekly_rate' => $this->faker->randomFloat(2, 300, 2000),
            'monthly_rate' => $this->faker->randomFloat(2, 1000, 8000),
            'currency' => 'AUD',
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'property_id' => \App\Models\Property::factory(), // Assuming Property factory exists
        ];
    }
}
