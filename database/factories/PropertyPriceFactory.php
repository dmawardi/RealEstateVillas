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
        $nightlyRate = $this->faker->numberBetween(500000, 5000000);
        // Industry convention: progressive discounts
        $weeklyRate = $nightlyRate * 0.85;   // 15% discount for 7+ nights
        $monthlyRate = $nightlyRate * 0.70;  // 30% discount for 30+ nights
        return [
            'nightly_rate' => $nightlyRate,
            'weekly_rate' => $weeklyRate,
            'monthly_rate' => $monthlyRate,
            'name' => $this->faker->word(),
            'weekly_discount_percent' => 15.00,
            'monthly_discount_percent' => 30.00,
            'currency' => 'IDR', // Default currency
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'property_id' => \App\Models\Property::factory(), // Assuming Property factory exists
        ];
    }
}
