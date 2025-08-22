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

    // Handle comma-separated location filters with OR logic
    $hasLocationFilter = $request->filled('villages') || $request->filled('districts') || $request->filled('regencies');

    if ($hasLocationFilter) {
        $query->where(function ($locationQuery) use ($request) {
            // Keep track of if another location condition is already used
            $hasCondition = false;
            
            if ($request->filled('villages')) {
                $villages = explode(',', $request->villages);
                $locationQuery->whereIn('village', $villages);
                $hasCondition = true;
            }
            
            if ($request->filled('districts')) {
                $districts = explode(',', $request->districts);
                if ($hasCondition) {
                    $locationQuery->orWhereIn('district', $districts);
                } else {
                    $locationQuery->whereIn('district', $districts);
                }
                $hasCondition = true;
            }
            
            if ($request->filled('regencies')) {
                $regencies = explode(',', $request->regencies);
                if ($hasCondition) {
                    $locationQuery->orWhereIn('regency', $regencies);
                } else {
                    $locationQuery->whereIn('regency', $regencies);
                }
            }
        });
    }

    // Handle comma-separated property types
    if ($request->filled('property_type')) {
        $propertyTypes = explode(',', $request->property_type);
        $query->whereIn('property_type', $propertyTypes);
    }

    // Handle listing type
    if ($request->filled('listing_type')) {
        $query->where('listing_type', $request->listing_type);
    }

    // Handle bedrooms filter
    if ($request->filled('bedrooms')) {
        $query->where('bedrooms', '>=', $request->bedrooms);
    }

    // Handle bathrooms filter
    if ($request->filled('bathrooms')) {
        $query->where('bathrooms', '>=', $request->bathrooms);
    }

    // Handle land size filters
    if ($request->filled('min_land_size')) {
        $query->where('land_size', '>=', $request->min_land_size);
    }

    if ($request->filled('max_land_size')) {
        $query->where('land_size', '<=', $request->max_land_size);
    }

    // Handle car spaces filter
    if ($request->filled('car_spaces')) {
        $query->where('car_spaces', '>=', $request->car_spaces);
    }


    // Only show active properties by default if no status filter is applied
    $query->where('status', 'active');

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

    // Paginate the results
    $properties = $query->latest('listed_at')->paginate(10)->withQueryString();

    // Get current filters for the frontend (including the new ones)
    $filters = $request->only([
        'property_type', 
        'listing_type', 
        'bedrooms', 
        'bathrooms',
        'min_price',
        'max_price', 
        'price_rate',
        'min_land_size',
        'max_land_size',
        'car_spaces',
        'villages', 
        'districts', 
        'regencies',
        'search',
        'status',
        'check_in_date', 
        'check_out_date'
    ]);

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
        $property->load(['user', 'features', 'attachments', 'pricing']);

        // Get current pricing
        $currentPricing = $property->getCurrentPricing();

         // Pre-load availability for next 6 months for the booking calendar
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(6);

        // Logic to retrieve and display a specific property
        return Inertia::render('properties/Show', [
            'property' => $property,
            'current_pricing' => $currentPricing,
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


    // If check-in and check-out dates are provided, check availability for that range and return boolean
    // Else, return unavailable periods
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

    // New method to get all unique locations (villages) of properties
    public function getAllLocations()
    {
        // Get all unique villages, districts, or regencies from active properties
        $villages = Property::where('status', 'active')
            ->distinct()
            ->pluck('village')
            ->filter()
            ->values();

        $districts = Property::where('status', 'active')
            ->distinct()
            ->pluck('district')
            ->filter()
            ->values();

        $regencies = Property::where('status', 'active')
            ->distinct()
            ->pluck('regency')
            ->filter()
            ->values();

        return response()->json([
            'villages' => $villages,
            'districts' => $districts,
            'regencies' => $regencies,
        ]);
    }
}