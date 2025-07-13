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
        // Grab image_placeholder from the storage disk
        $imagePlaceholderPath = '/images/image_placeholder.jpg';
        $imagePlaceholder = \Illuminate\Support\Facades\Storage::disk('public')->path($imagePlaceholderPath);
        return [
            'property_id' => \App\Models\Property::factory(),
            'path' => $imagePlaceholder,
            'original_filename' => $this->faker->word . '.jpg',
            'file_type' => 'image/jpeg',
            'file_size' => $this->faker->numberBetween(1000, 5000000),
            'caption' => $this->faker->sentence,
            'is_visible_to_customer' => true, // Default to visible
            'is_active' => true, // Default to active
        ];
    }
}
