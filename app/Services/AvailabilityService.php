<?php
namespace App\Services;

use App\Models\Property;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    public function getAvailableDates(Property $property, Carbon $startDate, Carbon $endDate): array
    {
        // Get all confirmed bookings for this property in the date range
        $bookings = Booking::forProperty($property->id)
            ->confirmed()
            ->inDateRange($startDate, $endDate)
            ->get(['check_in_date', 'check_out_date']);

        $unavailableDates = [];
        
        foreach ($bookings as $booking) {
            $period = CarbonPeriod::create(
                $booking->check_in_date,
                $booking->check_out_date->subDay() // Checkout day is available
            );
            
            foreach ($period as $date) {
                $unavailableDates[] = $date->format('Y-m-d');
            }
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        $availableDates = [];

        foreach ($period as $date) {
            if (!in_array($date->format('Y-m-d'), $unavailableDates)) {
                $availableDates[] = $date->format('Y-m-d');
            }
        }

        return $availableDates;
    }

    public function isPropertyAvailable(Property $property, Carbon $checkIn, Carbon $checkOut): bool
    {
        $conflictingBookings = Booking::forProperty($property->id)
            ->confirmed()
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    // Check if check-in falls within existing booking
                    $q->where('check_in_date', '<=', $checkIn)
                      ->where('check_out_date', '>', $checkIn);
                })->orWhere(function ($q) use ($checkIn, $checkOut) {
                    // Check if check-out falls within existing booking
                    $q->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>=', $checkOut);
                })->orWhere(function ($q) use ($checkIn, $checkOut) {
                    // Check if new booking completely contains existing booking
                    $q->where('check_in_date', '>=', $checkIn)
                      ->where('check_out_date', '<=', $checkOut);
                });
            })
            ->exists();

        return !$conflictingBookings;
    }
}