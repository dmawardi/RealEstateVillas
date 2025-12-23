<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationAdminMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingUpdateAdminMail;
use App\Models\Booking;
use App\Models\Property;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(private AvailabilityService $availabilityService)
    {
    }


    public function index(Request $request)
    {
        // Build query with user's bookings
        $query = Booking::where('user_id', $request->user()->id)
            ->with(['property:id,title,slug,district,regency', 'property.attachments' => function($query) {
                $query->where('type', 'image')->orderBy('order', 'asc')->limit(1);
            }]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('booking_type')) {
            $query->where('booking_type', $request->booking_type);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->where('check_in_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('check_out_date', '<=', $request->date_to);
        }

        // Search by property name or guest name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('property', function($propertyQuery) use ($search) {
                      $propertyQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Sort by most recent first
        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Generate SEO data
        $seoData = [
            'title' => 'My Bookings',
            'description' => 'Manage and view your property bookings.',
            'keywords' => 'bookings, property bookings, my bookings',
            'canonicalUrl' => route('my.bookings'),
            'ogImage' => asset('images/logo/Logo.png'),
        ];

        return Inertia::render('bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->all(),
            'seoData' => $seoData,
        ]);
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
                return back()->with('error', 'The selected dates are not available for this property.');
            }

            // Calculate total price (this could include discounts, fees, etc.)
            $totalPrice = $property->calculateTotalPrice($checkInDate, $checkOutDate);
            if ($totalPrice === null) {
                return back()->with('error', 'Unable to calculate price for the selected dates.');
            }

            // Check for discrepancy in total price
            if ($validated['total_price'] != $totalPrice) {
                Log::warning('Total price discrepancy detected during booking', [
                    'property_id' => $property->id,
                    'calculated_price' => $totalPrice,
                    'submitted_price' => $validated['total_price'],
                    'guest_email' => $validated['email'] ?? null,
                ]);
            }
            
            // Create the booking
            $booking = Booking::create([
                'property_id' => $property->id,
                'user_id' => $request->user()?->id,
                'source' => 'direct',
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
                'booking_type' => 'booking',
                'total_price' => $totalPrice,
                'special_requests' => $validated['special_requests'],
            ]);
            
            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'guest_email' => $booking->email,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'source' => $booking->source
            ]);
                            
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'property_id' => $property->id,
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while processing your booking. Please try again.');
        }

        // Else if booking created successfully, proceed to send confirmation emails
        // Queue Email booking confirmation
        if ($booking->email) {
            try {
                // Mail to user
                Mail::to($booking->email)->queue(new BookingConfirmationMail($booking));
                
                Log::info('Booking confirmation email queued', [
                    'booking_id' => $booking->id,
                    'guest_email' => $booking->email
                ]);

                // Mail to admin
                Mail::to(config('app.business_email'))->queue(new BookingConfirmationAdminMail($booking));
            } catch (\Exception $emailError) {
                // Don't fail the booking if email fails
                Log::error('Failed to queue booking confirmation email', [
                    'booking_id' => $booking->id,
                    'guest_email' => $booking->email,
                    'error' => $emailError->getMessage(),
                    'admin_email' => config('app.business_email')
                ]);
            }
        }

        return back()->with('success', 'Your booking request has been submitted successfully.');
    }

    public function withdraw(Request $request, Booking $booking)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'status' => 'required|string|max:255',
        ]);

        $originalData = $booking->getOriginal();

        // Check if user is logged in
        if (!$request->user()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to manage your bookings.');
        }

        // Check if user is authorized to withdraw this booking
        if ($request->user()->id !== $booking->user_id) {
            return back()->withErrors([
                'message' => 'You are not authorized to withdraw this booking.'
            ]);
        }

        // Check that status of booking is pending or confirmed
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->withErrors([
                'message' => 'Only pending or confirmed bookings can be withdrawn.'
            ]);
        }

        try {
            // Update the booking status to withdrawn
            $booking->update([
                'status' => $validated['status'],
            ]);

            // Check for important changes
            $changes = Booking::getImportantChanges($originalData, $booking->toArray());
            
            if (!empty($changes)) {
                $updateType = Booking::determineUpdateType($changes);
                
                // Send admin notification
                Mail::to(config('app.business_email'))
                    ->queue(new BookingUpdateAdminMail($booking, $changes, $updateType));
            }

            return back()->with('success', 'Booking withdrawn successfully.');

        } catch (\Exception $e) {
            Log::error('Booking withdrawal failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'user_id' => $request->user()->id,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'message' => 'An error occurred while processing your withdrawal. Please try again.'
            ]);
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
            'notes' => 'nullable|string|max:1000', // Added notes field
        ]);

        if (!auth()->check() || auth()->id() !== $booking->user_id) {
            return response()->json([
                'message' => 'Unauthorized to update this booking.'
            ], 403);
        }

        // Store original data before update
        $originalData = $booking->getOriginal();

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

            // Update booking fields - include all fields from the form
            $booking->update([
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'number_of_guests' => $validated['number_of_guests'],
                'number_of_rooms' => $validated['number_of_rooms'],
                'flexible_dates' => $validated['flexible_dates'] ?? false,
                'total_price' => $validated['total_price'],
                'special_requests' => $validated['special_requests'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Booking updated', [
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'guest_email' => $booking->email,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'source' => $booking->source,
                'updated_fields' => array_keys($validated)
            ]);

            // Check for important changes
            $changes = Booking::getImportantChanges($originalData, $booking->toArray());
            
            if (!empty($changes)) {
                $updateType = Booking::determineUpdateType($changes);
                
                // Send admin notification
                Mail::to(config('app.business_email'))
                    ->queue(new BookingUpdateAdminMail($booking, $changes, $updateType));
            }

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

    public function destroy(Booking $booking)
    {
        if (!auth()->check() || auth()->id() !== $booking->user_id) {
            return response()->json([
                'message' => 'Unauthorized to delete this booking.'
            ], 403);
        }
        try {
            $booking->delete();

            Log::info('Booking deleted', [
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'guest_email' => $booking->email,
            ]);

            return response()->json([
                'message' => 'Booking deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Booking deletion failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'email' => $booking->email,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while deleting the booking.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
