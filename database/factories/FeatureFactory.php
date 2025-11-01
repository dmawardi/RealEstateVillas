<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['amenity', 'safety', 'comfort', 'entertainment', 'outdoor', 'kitchen', 'bathroom', 'accessibility'];

        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->optional()->word(), // Optional icon, can be a font-awesome class or similar
            'is_quantifiable' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(true), // Default to true
        ];
    }
}
