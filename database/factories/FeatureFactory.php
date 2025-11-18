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
        $categories = ['amenity', 'safety', 'comfort', 'entertainment', 'outdoor', 'kitchen', 'bathroom', 'accessibility', 'security'];

        $svgIcons = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M2 17h20v2H2v-2zm1.15-4.05L4 11l.85 1.95L6.7 11l1.85 1.95L10.4 11l1.85 1.95L14.1 11l1.85 1.95L17.8 11l2.05 1.95L21 11v8H3v-8l-.85 1.95zM12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
            </svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M6.59 6L5 7.59 6.41 9 8 7.41 6.59 6zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-1-13h2v6h-2V7zm0 7h2v2h-2v-2z"/>
            </svg>',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M1 9l2 2c2.88-2.88 6.88-2.88 9.76 0L15 9c-4.3-4.3-11.3-4.3-15.56 0zM3.5 13.5l2 2c1.38-1.38 3.62-1.38 5 0l2-2c-2.74-2.74-7.26-2.74-10 0zM12 21l3-3-3-3-3 3 3 3z"/>
            </svg>'
        ];

        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->optional()->randomElement($svgIcons),
            'is_quantifiable' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(true), // Default to true
        ];
    }
}
