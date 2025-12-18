<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminBookingController extends Controller
{
    public function __construct(private AvailabilityService $availabilityService)
    {
    }

    /**
     * Display a paginated listing of all bookings with search and filtering capabilities.
     * 
     * Supports filtering by:
     * - Search term (guest name, email, booking ID, property title)
     * - Status (pending, confirmed, cancelled, completed, withdrawn)
     * - Source (direct, airbnb, booking_com, agoda, etc.)
     * - Booking type (booking, inquiry, blocked, maintenance)
     * - Date ranges (check-in, check-out, created dates)
     * - Property ID or property title
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Build query with eager loading for performance
        $query = Booking::with(['property' => function($query) {
            $query->select('id', 'title', 'property_id', 'street_name', 'district', 'regency');
        }])
        ->orderBy('created_at', 'desc');

        // Apply search filter across multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('external_booking_id', 'like', "%{$search}%")
                  ->orWhereHas('property', function($propertyQuery) use ($search) {
                      $propertyQuery->where('title', 'like', "%{$search}%")
                                   ->orWhere('property_id', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // Filter by source
        if ($request->filled('source')) {
            $sources = is_array($request->source) ? $request->source : explode(',', $request->source);
            $query->whereIn('source', $sources);
        }

        // Filter by booking type
        if ($request->filled('booking_type')) {
            $bookingTypes = is_array($request->booking_type) ? $request->booking_type : explode(',', $request->booking_type);
            $query->whereIn('booking_type', $bookingTypes);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by check-in date range
        if ($request->filled('check_in_from')) {
            $query->where('check_in_date', '>=', $request->check_in_from);
        }

        if ($request->filled('check_in_to')) {
            $query->where('check_in_date', '<=', $request->check_in_to);
        }

        // Filter by check-out date range
        if ($request->filled('check_out_from')) {
            $query->where('check_out_date', '>=', $request->check_out_from);
        }

        if ($request->filled('check_out_to')) {
            $query->where('check_out_date', '<=', $request->check_out_to);
        }

        // Filter by booking creation date range
        if ($request->filled('created_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->created_from)->startOfDay());
        }

        if ($request->filled('created_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->created_to)->endOfDay());
        }

        // Filter by guest count
        if ($request->filled('min_guests')) {
            $query->where('number_of_guests', '>=', $request->min_guests);
        }

        if ($request->filled('max_guests')) {
            $query->where('number_of_guests', '<=', $request->max_guests);
        }

        // Filter by total price range
        if ($request->filled('min_price')) {
            $query->where('total_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('total_price', '<=', $request->max_price);
        }

        // Filter by commission status
        if ($request->filled('commission_paid')) {
            $query->where('commission_paid', $request->boolean('commission_paid'));
        }

        // Paginate results
        $bookings = $query->paginate(20)->withQueryString();

        // Add calculated fields to each booking
        $bookings->getCollection()->transform(function ($booking) {
            // Calculate nights
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);
            $booking->nights = $checkIn->diffInDays($checkOut);
            
            // Calculate days until check-in
            $booking->days_until_checkin = now()->diffInDays($checkIn, false);
            
            return $booking;
        });

        return Inertia::render('admin/bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only([
                'search', 'status', 'source', 'booking_type', 'property_id',
                'check_in_from', 'check_in_to', 'check_out_from', 'check_out_to',
                'created_from', 'created_to', 'min_guests', 'max_guests',
                'min_price', 'max_price', 'commission_paid'
            ]),
            'statusOptions' => $this->getStatusOptions(),
            'sourceOptions' => $this->getSourceOptions(),
            'bookingTypeOptions' => $this->getBookingTypeOptions(),
            'properties' => $this->getPropertiesForFilter(),
            'stats' => $this->getBookingStats($request),
        ]);
    }

    /**
     * Show the form for creating a new booking.
     *
     * @return \Inertia\Response
     */
    public function show(Booking $booking)
    {
        // Load the booking with its relationships
        $booking->load('property', 'user');

        return Inertia::render('admin/bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function create()
    {
        // Get properties for selection
        $properties = Property::select('id', 'title', 'property_id')
            ->where('status', 'active')
            ->orderBy('title')
            ->get();
        return Inertia::render('admin/bookings/Create', [
            'properties' => $properties
        ]);
    }

    /**
     * Store a newly created booking in the database.
     * 
     * Admin version allows more control over booking creation including:
     * - Setting any status (not just pending)
     * - Creating bookings for past dates
     * - Setting commission details
     * - Adding admin notes
     * - Bypassing some validation for administrative purposes
     * - Creates or finds user based on email
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Admin validation - more permissive than public booking
        $validated = $request->validate([
            // Property selection
            'property_id' => 'required|exists:properties,id',
            
            // Guest Information
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            
            // Booking Details
            'check_in_date' => 'required|date', // Admin can book past dates
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1|max:50',
            'number_of_rooms' => 'nullable|integer|min:1|max:20',
            'total_price' => 'required|numeric|min:0',
            
            // Booking Configuration
            'status' => 'required|in:pending,confirmed,cancelled,completed,withdrawn',
            'source' => 'required|in:direct,airbnb,booking_com,agoda,owner_blocked,maintenance,other',
            'booking_type' => 'required|in:booking,inquiry,blocked,maintenance',
            'external_booking_id' => 'nullable|string|max:255',
            
            // Commission Details
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_paid' => 'nullable|boolean',
            
            // Additional Details
            'flexible_dates' => 'nullable|boolean',
            'special_requests' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000', // Admin can add longer notes
            
            // Admin specific fields
            'send_confirmation_email' => 'nullable|boolean',
            'override_availability_check' => 'nullable|boolean',
        ]);

        // Parse dates and check availability BEFORE starting transaction
        $property = Property::findOrFail($validated['property_id']);
        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);
        
        // Check availability unless admin overrides
        if (!$request->boolean('override_availability_check')) {
            $isAvailable = $this->availabilityService->isPropertyAvailable(
                $property,
                $checkInDate,
                $checkOutDate
            );
            
            if (!$isAvailable) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'The selected dates are not available for this property.',
                        'errors' => [
                            'check_in_date' => ['The selected dates are not available for this property.']
                        ]
                    ], 422);
                }
                return back()->with('error', 'The selected dates are not available for this property.')
                    ->withInput();
            }
        }

        DB::beginTransaction();
        
        try {

            // Handle user creation or lookup
            $userId = null;
            if (!empty($validated['email'])) {
                $user = $this->createOrFindUser([
                    'email' => $validated['email'],
                    'first_name' => $validated['first_name'] ?? null,
                    'last_name' => $validated['last_name'] ?? null,
                    'phone' => $validated['phone'] ?? null
                ]);
                $userId = $user->id;
            }

            // Calculate commission amount if rate is provided and amount is not explicitly set
            $commissionAmount = $validated['commission_amount'] ?? null;
            if (isset($validated['commission_rate']) && $validated['total_price'] && !$commissionAmount) {
                $commissionAmount = ($validated['total_price'] * $validated['commission_rate']) / 100;
            }
            
            // Create the booking
            $booking = Booking::create([
                'property_id' => $property->id,
                'user_id' => $userId, // Add user_id relationship
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'number_of_guests' => $validated['number_of_guests'],
                'number_of_rooms' => $validated['number_of_rooms'] ?? null,
                'total_price' => $validated['total_price'],
                'status' => $validated['status'],
                'source' => $validated['source'],
                'booking_type' => $validated['booking_type'],
                'external_booking_id' => $validated['external_booking_id'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? null,
                'commission_amount' => $commissionAmount,
                'commission_paid' => $validated['commission_paid'] ?? false,
                'flexible_dates' => $validated['flexible_dates'] ?? false,
                'special_requests' => $validated['special_requests'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            Log::info('Admin booking created', [
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'user_id' => $userId,
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $booking->email,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'source' => $booking->source,
                'status' => $booking->status,
                'override_availability' => $request->boolean('override_availability_check')
            ]);

            // Send confirmation email if requested and email is provided
            if ($request->boolean('send_confirmation_email') && $booking->email) {
                try {
                    // Only import and send if email is requested
                    $mailClass = new \App\Mail\BookingConfirmationMail($booking);
                    \Illuminate\Support\Facades\Mail::to($booking->email)->queue($mailClass);
                    
                    Log::info('Admin booking confirmation email queued', [
                        'booking_id' => $booking->id,
                        'guest_email' => $booking->email
                    ]);
                } catch (\Exception $emailError) {
                    // Don't fail the booking if email fails
                    Log::error('Failed to queue admin booking confirmation email', [
                        'booking_id' => $booking->id,
                        'guest_email' => $booking->email,
                        'error' => $emailError->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Return appropriate response based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Booking created successfully.',
                    'booking' => $booking->load('property')
                ]);
            }

            return back()->with('success', 'Booking created successfully.');
                            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Admin booking creation failed', [
                'error' => $e->getMessage(),
                'property_id' => $request->property_id ?? null,
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $request->email ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An error occurred while creating the booking.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to create booking: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified booking.
     *
     * @param Booking $booking
     * @return \Inertia\Response
     */
    public function edit(Booking $booking)
    {
        // Load the booking with its relationships
        $booking->load('property', 'user');
        
        // Get all active properties for the dropdown
        $properties = Property::where('status', 'active')
            ->select('id', 'title', 'property_id', 'max_guests', 'max_rooms')
            ->orderBy('title')
            ->get();

        return Inertia::render('admin/bookings/Edit', [
            'booking' => $booking,
            'properties' => $properties
        ]);
    }

    /**
     * Update the specified booking in storage.
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Booking $booking)
    {
        // Similar validation as store but for updating
        $validated = $request->validate([
            // Property selection
            'property_id' => 'required|exists:properties,id',
            
            // Guest Information
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            
            // Booking Details
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1|max:50',
            'number_of_rooms' => 'nullable|integer|min:1|max:20',
            'total_price' => 'required|numeric|min:0',
            
            // Booking Configuration
            'status' => 'required|in:pending,confirmed,cancelled,completed,withdrawn',
            'source' => 'required|in:direct,airbnb,booking_com,agoda,owner_blocked,maintenance,other',
            'booking_type' => 'required|in:booking,inquiry,blocked,maintenance',
            'external_booking_id' => 'nullable|string|max:255',
            
            // Commission Details
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_paid' => 'nullable|boolean',
            
            // Additional Details
            'flexible_dates' => 'nullable|boolean',
            'special_requests' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Get the property and parse dates BEFORE starting transaction
        $property = Property::findOrFail($validated['property_id']);
        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);
        
        // Check availability BEFORE transaction for confirmed/completed bookings
        if (in_array($validated['status'], ['confirmed'])) {
            $isAvailable = $this->availabilityService->isPropertyAvailable(
                $property,
                $checkInDate,
                $checkOutDate,
            );
            Log::info('Admin booking availability check during update', [
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'is_available' => $isAvailable
            ]);
            
            if (!$isAvailable) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'The selected dates are not available for this property.',
                        'errors' => [
                            'check_in_date' => ['The selected dates are not available for this property.']
                        ]
                    ], 422);
                }
                return back()->with('error', 'The selected dates are not available for this property.')
                    ->withInput();
            }
        }

        DB::beginTransaction();
        
        try {
            // Handle user creation or lookup (only if email changed)
            $userId = $booking->user_id;
            if (!empty($validated['email']) && $validated['email'] !== $booking->email) {
                $user = $this->createOrFindUser([
                    'email' => $validated['email'],
                    'first_name' => $validated['first_name'] ?? null,
                    'last_name' => $validated['last_name'] ?? null,
                    'phone' => $validated['phone'] ?? null
                ]);
                $userId = $user->id;
            }

            Log::info('Admin booking update attempt', [
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'user_id' => $userId,
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $validated['email'] ?? null,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'status' => $validated['status']
            ]);

            // Calculate commission amount if rate is provided and amount is not explicitly set
            $commissionAmount = $validated['commission_amount'] ?? null;
            if (isset($validated['commission_rate']) && $validated['total_price'] && !$commissionAmount) {
                $commissionAmount = ($validated['total_price'] * $validated['commission_rate']) / 100;
            }
            
            // Update the booking
            $booking->update([
                'property_id' => $property->id,
                'user_id' => $userId,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'number_of_guests' => $validated['number_of_guests'],
                'number_of_rooms' => $validated['number_of_rooms'] ?? null,
                'total_price' => $validated['total_price'],
                'status' => $validated['status'],
                'source' => $validated['source'],
                'booking_type' => $validated['booking_type'],
                'external_booking_id' => $validated['external_booking_id'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? null,
                'commission_amount' => $commissionAmount,
                'commission_paid' => $validated['commission_paid'] ?? false,
                'flexible_dates' => $validated['flexible_dates'] ?? false,
                'special_requests' => $validated['special_requests'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            Log::info('Admin booking updated', [
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'user_id' => $userId,
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $booking->email,
                'dates' => $checkInDate->format('Y-m-d') . ' to ' . $checkOutDate->format('Y-m-d'),
                'status' => $booking->status,
                'changes' => $booking->getChanges()
            ]);

            DB::commit();

            // Return appropriate response based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Booking updated successfully.',
                    'booking' => $booking->load('property')
                ]);
            }

            return back()->with('success', 'Booking updated successfully.');
                            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Admin booking update failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'property_id' => $validated['property_id'] ?? null,
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An error occurred while updating the booking.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update booking: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified booking from storage.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Booking $booking)
    {
        try {
            $booking->delete();

            Log::info('Admin booking deleted', [
                'booking_id' => $booking->id,
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $booking->email,
            ]);

            return back()
                ->with('success', 'Booking deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Admin booking deletion failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'admin_user_id' => auth()->id() ?? null,
                'guest_email' => $booking->email,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to delete booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Get booking status options from migration enum.
     * 
     * @return array
     */
    private function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            'withdrawn' => 'Withdrawn',
        ];
    }

    /**
     * Get booking source options from migration enum.
     * 
     * @return array
     */
    private function getSourceOptions(): array
    {
        return [
            'direct' => 'Direct',
            'airbnb' => 'Airbnb',
            'booking_com' => 'Booking.com',
            'agoda' => 'Agoda',
            'owner_blocked' => 'Owner Blocked',
            'maintenance' => 'Maintenance',
            'other' => 'Other',
        ];
    }

    /**
     * Get booking type options from migration enum.
     * 
     * @return array
     */
    private function getBookingTypeOptions(): array
    {
        return [
            'booking' => 'Booking',
            'inquiry' => 'Inquiry',
            'blocked' => 'Blocked',
            'maintenance' => 'Maintenance',
        ];
    }

    /**
     * Get simplified property list for filter dropdown.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPropertiesForFilter()
    {
        return Property::select('id', 'title', 'property_id')
            ->where('status', 'active')
            ->orderBy('title')
            ->get();
    }

    /**
     * Get booking statistics for dashboard/summary.
     * 
     * @param Request $request
     * @return array
     */
    private function getBookingStats(Request $request): array
    {
        $query = Booking::query();
        
        // Apply same filters as main query for consistent stats
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

        return [
            'total_bookings' => (clone $query)->count(),
            'pending_bookings' => (clone $query)->where('status', 'pending')->count(),
            'confirmed_bookings' => (clone $query)->where('status', 'confirmed')->count(),
            'total_revenue' => (clone $query)->where('status', 'confirmed')->sum('total_price'),
            'commission_owed' => (clone $query)->where('status', 'confirmed')->where('commission_paid', false)->sum('commission_amount'),
        ];
    }

    // Helpers
    /**
     * Create or find user based on email and guest information.
     * 
     * @param array $guestData
     * @return \App\Models\User
     */
    private function createOrFindUser(array $guestData): \App\Models\User
    {
        // First try to find existing user by email
        $user = \App\Models\User::where('email', $guestData['email'])->first();
        
        // Return existing user if found
        if ($user) {
            return $user;
        }
        
        $fullName = trim(($guestData['first_name'] ?? '') . ' ' . ($guestData['last_name'] ?? ''));
        // Create new user if doesn't exist
        $user = \App\Models\User::create([
            'name' => $fullName ?: 'Guest',
            'email' => $guestData['email'],
            'phone' => $guestData['phone'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)), // Random password
            'email_verified_at' => null, // User needs to verify email
            'role' => 'user', // Default role for booking users
        ]);
        
        Log::info('New user created from booking', [
            'user_id' => $user->id,
            'email' => $user->email,
            'created_by_admin' => auth()->id()
        ]);
        
        return $user;
    }
}
