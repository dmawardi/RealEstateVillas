<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyAttachment>
 */
class PropertyAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imagePlaceholder = $this->faker->imageUrl(800, 600, 'real-estate', true, 'property');

        return [
            'property_id' => \App\Models\Property::factory(),
            'title' => $this->faker->words(3, true),
            'path' => $imagePlaceholder,
            'original_filename' => $this->faker->word . '.jpg',
            'file_type' => 'image/jpeg',
            'file_size' => $this->faker->numberBetween(1000, 5000000),
            'caption' => $this->faker->sentence,
            'is_visible_to_customer' => true, // Default to visible
            'is_active' => true, // Default to active
            'order' => $this->faker->numberBetween(1, 100),
            'type' => 'image', // (eg. image, document, floor_plan)
        ];
    }
}
