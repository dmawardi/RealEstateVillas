<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(private AvailabilityService $availabilityService)
    {
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request, Property $property)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1|max:20',
            'total_price' => 'required|numeric|min:0',
            'number_of_rooms' => 'nullable|integer|min:1',
            'flexible_dates' => 'nullable|boolean',
            'special_requests' => 'nullable|string|max:1000',
            'source' => 'nullable|in:direct,airbnb,booking_com,agoda,owner_blocked,maintenance,other',
            'external_booking_id' => 'nullable|string|max:255',
            'booking_type' => 'nullable|in:booking,inquiry,blocked,maintenance',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_paid' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Parse dates
            $checkInDate = Carbon::parse($validated['check_in_date']);
            $checkOutDate = Carbon::parse($validated['check_out_date']);
            
            // Check availability before creating booking
            $isAvailable = $this->availabilityService->isPropertyAvailable(
                $property,
                $checkInDate,
                $checkOutDate
            );
            
            if (!$isAvailable) {
                return response()->json([
                    'message' => 'The selected dates are not available for this property.',
                    'errors' => [
                        'dates' => ['The selected dates are not available for this property.']
                    ]
                ], 422);
            }

            // Calculate commission amount if rate is provided and amount is not explicitly set
            $commissionAmount = $validated['commission_amount'] ?? null;
            if ($validated['commission_rate'] && $validated['total_price'] && !$commissionAmount) {
                $commissionAmount = ($validated['total_price'] * $validated['commission_rate']) / 100;
            }
            
            // Create the booking
            $booking = Booking::create([
                'property_id' => $property->id,
                'source' => $validated['source'] ?? 'direct',
                'external_booking_id' => $validated['external_booking_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'number_of_guests' => $validated['number_of_guests'],
                'number_of_rooms' => $validated['number_of_rooms'],
                'flexible_dates' => $validated['flexible_dates'] ?? false,
                'status' => 'pending', // Default to pending
                'booking_type' => $validated['booking_type'] ?? 'booking',
                'total_price' => $validated['total_price'],
                'commission_rate' => $validated['commission_rate'],
                'commission_amount' => $commissionAmount,
                'commission_paid' => $validated['commission_paid'] ?? false,
                'special_requests' => $validated['special_requests'],
                'notes' => $validated['notes'],
            ]);
            
            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'guest_email' => $booking->email,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'source' => $booking->source
            ]);
            
            return response()->json([
                'message' => 'Booking created successfully.',
                'booking' => $booking
            ]);
                            
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'property_id' => $property->id,
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'An error occurred while processing your booking.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Booking $booking)
    {
        // Validate the incoming request - removed date restrictions for editing
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'check_in_date' => 'required|date', // Removed after_or_equal:today for editing
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1|max:20',
            'total_price' => 'required|numeric|min:0', // Changed to numeric for decimal support
            'number_of_rooms' => 'nullable|integer|min:1',
            'flexible_dates' => 'nullable|boolean',
            'special_requests' => 'nullable|string|max:1000',
            'source' => 'nullable|in:direct,airbnb,booking_com,agoda,owner_blocked,maintenance,other',
            'external_booking_id' => 'nullable|string|max:255',
            'booking_type' => 'nullable|in:booking,inquiry,blocked,maintenance',
            'commission_rate' => 'nullable|numeric|min:0|max:100', // Added commission fields
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_paid' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000', // Added notes field
        ]);

        try {
            $checkInDate = Carbon::parse($validated['check_in_date']);
            $checkOutDate = Carbon::parse($validated['check_out_date']);

            // Check availability if dates changed
            if (
                $booking->check_in_date !== $checkInDate->format('Y-m-d') ||
                $booking->check_out_date !== $checkOutDate->format('Y-m-d')
            ) {
                $isAvailable = $this->availabilityService->isPropertyAvailable(
                    $booking->property,
                    $checkInDate,
                    $checkOutDate,
                    $booking->id // Exclude current booking from availability check
                );

                if (!$isAvailable) {
                    return response()->json([
                        'message' => 'The selected dates are not available for this property.',
                        'errors' => [
                            'dates' => ['The selected dates are not available for this property.']
                        ]
                    ], 422);
                }
            }

            // Calculate commission amount if rate is provided and amount is not explicitly set
            $commissionAmount = $validated['commission_amount'] ?? null;
            if ($validated['commission_rate'] && $validated['total_price'] && !$commissionAmount) {
                $commissionAmount = ($validated['total_price'] * $validated['commission_rate']) / 100;
            }

            // Update booking fields - include all fields from the form
            $booking->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'number_of_guests' => $validated['number_of_guests'],
                'number_of_rooms' => $validated['number_of_rooms'],
                'flexible_dates' => $validated['flexible_dates'] ?? false,
                'source' => $validated['source'] ?? $booking->source,
                'external_booking_id' => $validated['external_booking_id'],
                'booking_type' => $validated['booking_type'] ?? $booking->booking_type,
                'total_price' => $validated['total_price'],
                'commission_rate' => $validated['commission_rate'],
                'commission_amount' => $commissionAmount,
                'commission_paid' => $validated['commission_paid'] ?? false,
                'special_requests' => $validated['special_requests'],
                'notes' => $validated['notes'], // Add notes field
            ]);

            Log::info('Booking updated', [
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'guest_email' => $booking->email,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'source' => $booking->source,
                'updated_fields' => array_keys($validated)
            ]);

            // Return JSON response for API calls
            return response()->json([
                'message' => 'Booking updated successfully.',
                'booking' => $booking->fresh() // Return updated booking
            ]);

        } catch (\Exception $e) {
            Log::error('Booking update failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the booking.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
