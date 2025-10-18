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
        'phone' => 'nullable|string|max:255', // Optional phone number
        'check_in_date' => 'required|date|after_or_equal:today',
        'check_out_date' => 'required|date|after:check_in_date',
        'number_of_guests' => 'required|integer|min:1|max:20',
        'total_price' => 'required|integer|min:0',
        // Optional fields
        'number_of_rooms' => 'nullable|integer|min:1',
        'flexible_dates' => 'nullable|boolean',
        'special_requests' => 'nullable|string|max:1000',
        'source' => 'nullable|in:direct,airbnb,booking_com,agoda,owner_blocked,maintenance,other',
        'external_booking_id' => 'nullable|string|max:255',
        'booking_type' => 'nullable|in:booking,inquiry,blocked,maintenance',
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
            return back()->withErrors([
                'dates' => 'The selected dates are not available for this property.'
            ])->withInput();
        }
        
        // Create the booking
        $booking = Booking::create([
            'property_id' => $property->id,
            'source' => $validated['source'] ?? 'direct',
            'external_booking_id' => $validated['external_booking_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'check_in_date' => $checkInDate->format('Y-m-d'),
            'check_out_date' => $checkOutDate->format('Y-m-d'),
            'number_of_guests' => $validated['number_of_guests'],
            'number_of_rooms' => $validated['number_of_rooms'] ?? null,
            'flexible_dates' => $validated['flexible_dates'] ?? false,
            'status' => 'pending', // Default to pending
            'booking_type' => $validated['booking_type'] ?? 'booking',
            'total_price' => $validated['total_price'],
            'commission_rate' => 0.1,
            'commission_amount' => $validated['total_price'] * 0.1, // Example commission calculation
            'commission_paid' => false,
            'special_requests' => $validated['special_requests'] ?? null,
            'notes' => null, // Internal notes can be added later by admin
        ]);
        
        // Log the booking creation
        Log::info('Booking created', [
            'booking_id' => $booking->id,
            'property_id' => $property->id,
            'guest_email' => $booking->email,
            'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
            'source' => $booking->source
        ]);
        
        // You might want to send confirmation emails here
        // Mail::to($booking->email)->send(new BookingConfirmation($booking));
        // Mail::to($property->user->email)->send(new NewBookingNotification($booking));
        
        // Return success response
        return redirect()->route('properties.show', $property)
            ->with('success', 'Your booking request has been submitted successfully! You will receive a confirmation email shortly.');
                          
    } catch (\Exception $e) {
        Log::error('Booking creation failed', [
            'error' => $e->getMessage(),
            'property_id' => $validated['property_id'] ?? null,
            'email' => $validated['email'] ?? null
        ]);
        
        // ALWAYS redirect with flash error (no JSON responses)
        return redirect()->back()
            ->with('error', 'An error occurred while processing your booking. Please try again.')
            ->withInput();
    }
    }
}
