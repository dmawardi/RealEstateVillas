<?php
namespace App\Services;

use App\Models\Property;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
   /**
     * Get unavailable date ranges for a property
     * This is much more efficient than listing all available dates
     */
    public function getUnavailablePeriods(Property $property, Carbon $startDate, Carbon $endDate): array
    {
        $bookings = Booking::forProperty($property->id)
            ->confirmed()
            ->inDateRange($startDate, $endDate)
            ->orderBy('check_in_date')
            ->get(['check_in_date', 'check_out_date']);

        $unavailablePeriods = [];
        
        foreach ($bookings as $booking) {
            $unavailablePeriods[] = [
                'start' => $booking->check_in_date->format('Y-m-d'),
                'end' => $booking->check_out_date->subDay()->format('Y-m-d'), // Checkout day is available
                'type' => 'booking'
            ];
        }

        return $unavailablePeriods;
    }

    /**
     * Check if a property is available for the given date range
     * This is the core method - everything else should use this
     * 
     * @param Property $property
     * @param Carbon $checkIn
     * @param Carbon $checkOut
     * @param Booking|int|null $excludeBooking Booking to exclude from availability check (for updates)
     */
    public function isPropertyAvailable(Property $property, Carbon $checkIn, Carbon $checkOut, $excludeBooking = null): bool
    {
        $query = Booking::forProperty($property->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                // A booking conflicts if it overlaps with our desired period
                $query->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
            });

        // Exclude a specific booking (useful when updating an existing booking)
        if ($excludeBooking) {
            // If it's an object, get the ID, else assume it's an ID
            $bookingId = is_object($excludeBooking) ? $excludeBooking->id : $excludeBooking;
            $query->where('id', '!=', $bookingId);
        }

        // If no conflicting bookings exist, the property is available
        return !$query->exists();
    }

    /**
     * Get available date ranges (gaps between bookings)
     * Only use this when you actually need the ranges, not individual dates
     */
    public function getAvailablePeriods(Property $property, Carbon $startDate, Carbon $endDate): array
    {
        $bookings = Booking::forProperty($property->id)
            ->confirmed()
            ->inDateRange($startDate, $endDate)
            ->orderBy('check_in_date')
            ->get(['check_in_date', 'check_out_date']);

        $availablePeriods = [];
        $currentDate = $startDate->copy();

        foreach ($bookings as $booking) {
            $bookingStart = $booking->check_in_date;
            
            // If there's a gap before this booking, it's available
            if ($currentDate->lt($bookingStart)) {
                $availablePeriods[] = [
                    'start' => $currentDate->format('Y-m-d'),
                    'end' => $bookingStart->copy()->subDay()->format('Y-m-d')
                ];
            }
            
            // Move past this booking
            $currentDate = $booking->check_out_date->copy();
        }

        // Check if there's availability after the last booking
        if ($currentDate->lte($endDate)) {
            $availablePeriods[] = [
                'start' => $currentDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ];
        }

        return $availablePeriods;
    }
}