<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    // Upon construction, inject the AvailabilityService
    public function __construct(
        private AvailabilityService $availabilityService
    ) {}
    /**
     * Display a listing of the properties.
     */
    public function index(Request $request)
    {
        $query = Property::with(['user', 'features']);
            

        // Apply filters
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->filled('village')) {
            $query->where('village', 'like', '%' . $request->village . '%');
        }

        // Apply availability filter if both check-in and check-out dates are provided
        if ($request->filled('check_in_date') && $request->filled('check_out_date')) {
            $checkInDate = Carbon::parse($request->check_in_date);
            $checkOutDate = Carbon::parse($request->check_out_date);
            
            // Exclude properties that have confirmed bookings overlapping with requested dates
            $query->whereDoesntHave('bookings', function ($bookingQuery) use ($checkInDate, $checkOutDate) {
                $bookingQuery->where('status', 'confirmed')
                    ->where(function ($q) use ($checkInDate, $checkOutDate) {
                        // Check if the booking overlaps with requested dates
                        $q->where(function ($q1) use ($checkInDate, $checkOutDate) {
                            // Booking starts before checkout and ends after checkin
                            $q1->where('check_in_date', '<', $checkOutDate)
                               ->where('check_out_date', '>', $checkInDate);
                        });
                    });
            });
        }

        // Only show active properties
        $query->where('status', 'active');

        // Paginate the results
        $properties = $query->latest('listed_at')->paginate(10)->withQueryString();

        // Get current filters for the frontend
        $filters = $request->only(['property_type', 'listing_type', 'bedrooms', 'village', 'check_in_date', 'check_out_date']);

        // Logic to retrieve and display properties
        return Inertia::render('properties/Index', compact('properties', 'filters'));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        // Logic to show the property creation form
        return view('properties.create');
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
    {
        // Logic to validate and store the new property
        // ...
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property)
    {
        $property->load(['user', 'features', 'attachments']);

         // Pre-load availability for next 6 months for the booking calendar
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(6);

        $unavailablePeriods = $this->availabilityService->getUnavailablePeriods(
            $property,
            $startDate,
            $endDate
        );

        // Logic to retrieve and display a specific property
        return Inertia::render('properties/Show', [
            'property' => $property,
            'availability' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'unavailable_periods' => $unavailablePeriods
            ],
        ]);
    }
    /**
     * Show the form for editing the specified property.
     */
    public function edit($id)
    {
        // Logic to show the property edit form
        return view('properties.edit', compact('id'));  
    }
    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, $id)
    {
        // Logic to validate and update the property
        // ...
    }
    /**
     * Remove the specified property from storage.
     */
    public function destroy($id)
    {        // Logic to delete the property
        // ...
        return redirect()->route('properties.index')->with('success', 'Property deleted successfully.');    
    }


    // Availability related methods
    public function getAvailability(Property $property, Request $request)
    {
        // For specific date range check
        if ($request->filled('check_in_date') && $request->filled('check_out_date')) {
            $checkIn = Carbon::parse($request->check_in_date);
            $checkOut = Carbon::parse($request->check_out_date);
            
            $isAvailable = $this->availabilityService->isPropertyAvailable($property, $checkIn, $checkOut);
            
            return response()->json([
                'available' => $isAvailable,
                'check_in_date' => $checkIn->format('Y-m-d'),
                'check_out_date' => $checkOut->format('Y-m-d')
            ]);
        }

        // For calendar display - return unavailable periods
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
        ]);

        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $unavailablePeriods = $this->availabilityService->getUnavailablePeriods(
            $property,
            $startDate,
            $endDate
        );

        return response()->json([
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'unavailable_periods' => $unavailablePeriods
        ]);
    }
}