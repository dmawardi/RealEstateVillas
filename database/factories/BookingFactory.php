<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate random check-in and check-out dates
        $checkInDate = $this->faker->dateTimeBetween('now', '+1 year');
        // Ensure check-out is always after check-in by adding 1-30 days
        $minStay = 1; // minimum 1 day stay
        $maxStay = 30; // maximum 30 days stay
        $stayDuration = $this->faker->numberBetween($minStay, $maxStay);
        
        $checkOutDate = Carbon::parse($checkInDate)->addDays($stayDuration);

        $bookingSource = $this->faker->randomElement(['direct', 'airbnb', 'booking_com', 'agoda', 'owner_blocked', 'maintenance', 'other']);
        $bookingType = $this->faker->randomElement(['booking', 'inquiry', 'blocked', 'maintenance']);
        if ($bookingType === 'booking') {
            $flexibleDates = false;
        } else {
            $flexibleDates = true;
        }
        return [
            'property_id' => \App\Models\Property::factory(),
            'source' => $bookingSource,
            'external_booking_id' => $this->faker->optional()->uuid,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->optional()->phoneNumber,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'number_of_guests' => $this->faker->numberBetween(1, 10),
            'number_of_rooms' => $this->faker->optional()->numberBetween(1, 5),
            'flexible_dates' => $flexibleDates,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled', 'completed', 'blocked']),
            'booking_type' => $bookingType,
            'total_price' => $this->faker->optional()->numberBetween(100, 10000),
            'commission_rate' => $this->faker->optional()->randomFloat(2, 0, 100),
            'commission_amount' => $this->faker->optional()->numberBetween(0, 5000),
            'commission_paid' => $this->faker->boolean,
            'special_requests' => $this->faker->optional()->text,
            'notes' => $this->faker->optional()->text,
        ];
    }
}
