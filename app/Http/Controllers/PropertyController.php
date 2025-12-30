<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Property;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PropertyController extends Controller
{
    // Cache constants
    private const LOCATIONS_CACHE_KEY = 'property_locations';
    private const LOCATIONS_CACHE_DURATION = 60 * 60 * 6; // 6 hours
    
    private const PROPERTY_DETAIL_CACHE_PREFIX = 'property_detail_';
    private const PROPERTY_DETAIL_CACHE_DURATION = 60 * 30; // 30 minutes

    // Upon construction, inject the AvailabilityService
    public function __construct(
        private AvailabilityService $availabilityService
    ) {}
    
    /**
     * Display a listing of the properties.
     */
    public function index(Request $request)
    {
        $query = Property::with(['features', 'attachments' => function($q) {
            $q->orderBy('order')->where('type', 'image');
        }]);

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

        if ($request->filled('features')) {
            $features = explode(',', $request->features);
            
            Log::info('Filtering properties by features: ' . implode(',', $features));

            // Option 1: Properties that have ANY of the selected features (OR logic)
            // $query->whereHas('features', function ($featureQuery) use ($features) {
            //     $featureQuery->whereIn('features.id', $features);
            // });
            
            // Option 2: Properties that have ALL selected features (AND logic)
            foreach ($features as $featureId) {
                $query->whereHas('features', function ($featureQuery) use ($featureId) {
                    $featureQuery->where('features.id', $featureId);
                });
            }
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

        // Only show active properties by default if no status filter is applied
        $query->where('status', 'active');

        // Paginate the results
        $properties = $query->latest('listed_at')->paginate(10)->withQueryString();

        // Append URL for each attachment
        $properties->getCollection()->each(function ($property) {
            if ($property->attachments) {
                $property->attachments->each(function ($attachment) {
                    $attachment->makeVisible(['url']);
                    $attachment->append('url');
                });
            }
        });

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
            'check_out_date',
            'features',
        ]);

        // Generate SEO data for the listing page
        $seoData = [
            'title' => 'Explore Premium Properties in Bali - Villas & Land for Sale/Rent',
            'description' => 'Discover our curated selection of luxury villas and land in Bali. Whether you\'re looking to buy or rent, find your perfect property with our expert guidance and commission-based services.',
            'keywords' => 'bali property, villa for sale bali, villa rental bali, bali land for sale, luxury villas bali, property investment bali' 
                . ($hasLocationFilter && $request->filled('villages') ? ', villas in ' . explode(',', $request->villages)[0] : '')
                . ($hasLocationFilter && $request->filled('districts') && !$request->filled('villages') ? ', villas in ' . explode(',', $request->districts)[0] : '')
                . ($hasLocationFilter && $request->filled('regencies') && !$request->filled('villages') && !$request->filled('districts') ? ', villas in ' . explode(',', $request->regencies)[0] : ''),
            'canonicalUrl' => url('/properties'),
            // Grab first property's image as ogImage if available
            'ogImage' => $properties->first()?->attachments->first()?->url() ?? asset('images/logo/Logo.png'),
        ];

        $businessPhone = config('app.business_phone');
        $businessEmail = config('app.business_email');

        return Inertia::render('properties/Index', compact('properties', 'filters', 'seoData', 'businessPhone', 'businessEmail'));
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
        $cacheKey = self::PROPERTY_DETAIL_CACHE_PREFIX . $property->id;
        
        // Get cached property data or fetch fresh
        $propertyData = Cache::remember($cacheKey, self::PROPERTY_DETAIL_CACHE_DURATION, function () use ($property) {
            Log::info("Fetching property details from database (cache miss)", ['property_id' => $property->id]);
            
            // Load relationships
            $property->load(['features', 'attachments' => function($query) {
                $query->orderBy('order')->where('type', 'image');
            }, 'pricing']);
    
            // Map through attachments to generate URLs for secure access
            $property->attachments->each->append('url');
    
            // Get current pricing
            $currentPricing = $property->getCurrentPricing();
    
            return [
                'property' => $property,
                'current_pricing' => $currentPricing,
            ];
        });
    
        // Increment view count (don't cache this - it should always update)
        $property->increment('view_count');

        // Add favorite status for authenticated users
        if (auth()->check()) {
            $isFavorited = auth()->user()->hasFavorited($property);
            Log::info('Checking favorite status for user', ['user_id' => auth()->id(), 'property_id' => $property->id, 'is_favorited' => $isFavorited]);
            $propertyData['property']->is_favorited = $isFavorited;
        }

        // Build SEO data
        $seoData = $this->generatePropertySEO($propertyData['property'], $propertyData['current_pricing']);
        
        Log::info('SEO Data for property', ['seoData' => $seoData]);
        return Inertia::render('properties/Show', [
            'property' => $propertyData['property'],
            'current_pricing' => $propertyData['current_pricing'],
            'map_api_key' => config('services.google.maps_api_key'),
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
            'seoData' => $seoData,
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

    // Show user's favorite properties
    public function favorites(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        // Start with the user's favorited properties
        $query = $user->favorites()
            ->with(['features', 'attachments' => function($q) {
                $q->orderBy('order')->where('type', 'image');
            }, 'pricing']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->orWhere('village', 'like', "%{$searchTerm}%")
                ->orWhere('district', 'like', "%{$searchTerm}%")
                ->orWhere('regency', 'like', "%{$searchTerm}%");
            });
        }

        // Apply property type filter
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Apply listing type filter
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Apply location filter
        if ($request->filled('location')) {
            $location = $request->location;
            $query->where(function ($q) use ($location) {
                // Split the location if it's in "Village, District" format
                if (strpos($location, ',') !== false) {
                    [$village, $district] = array_map('trim', explode(',', $location, 2));
                    $q->where(function ($subQ) use ($village, $district) {
                        $subQ->where('village', $village)
                            ->where('district', $district);
                    });
                } else {
                    // If it's a single location, check all location fields
                    $q->where('village', $location)
                    ->orWhere('district', $location)
                    ->orWhere('regency', $location);
                }
            });
        }

        // Apply price filters (only for properties with pricing)
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('pricing', function ($pricingQuery) use ($request) {
                if ($request->filled('min_price')) {
                    $pricingQuery->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $pricingQuery->where('price', '<=', $request->max_price);
                }
            });
        }

        // Apply bedrooms filter
        if ($request->filled('bedrooms')) {
            $bedrooms = $request->bedrooms;
            if ($bedrooms == '4') {
                // Handle "4+ bedrooms" case
                $query->where('bedrooms', '>=', 4);
            } else {
                $query->where('bedrooms', '>=', $bedrooms);
            }
        }

        // Apply bathrooms filter
        if ($request->filled('bathrooms')) {
            $bathrooms = $request->bathrooms;
            if ($bathrooms == '3') {
                // Handle "3+ bathrooms" case
                $query->where('bathrooms', '>=', 3);
            } else {
                $query->where('bathrooms', '>=', $bathrooms);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('favorites.created_at', 'asc');
                break;
            case 'price_low':
                $query->leftJoin('property_prices', function($join) {
                    $join->on('properties.id', '=', 'property_prices.property_id')
                        ->where('property_prices.is_active', true);
                })->orderBy('property_prices.price', 'asc');
                break;
            case 'price_high':
                $query->leftJoin('property_prices', function($join) {
                    $join->on('properties.id', '=', 'property_prices.property_id')
                        ->where('property_prices.is_active', true);
                })->orderBy('property_prices.price', 'desc');
                break;
            case 'title':
                $query->orderBy('properties.title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('favorites.created_at', 'desc');
                break;
        }

        // Paginate the results
        $favorites = $query->paginate(20)->withQueryString();

        // Add favorite status to each property (they're all favorited by definition)
        $favorites->getCollection()->each(function ($property) {
            $property->is_favorited = true;
        });

        // Get current filters for the frontend
        $filters = $request->only([
            'search',
            'property_type',
            'listing_type',
            'location',
            'min_price',
            'max_price',
            'bedrooms',
            'bathrooms',
            'sort'
        ]);

        // Generate SEO data for favorites page
        $seoData = [
            'title' => 'My Favorite Properties - Saved Bali Villas & Land',
            'description' => 'Your saved properties in Bali. Manage your favorite villas, land, and investment opportunities with easy filtering and sorting options.',
            'keywords' => 'favorite properties bali, saved villas bali, property wishlist, bali property favorites',
            'canonicalUrl' => url('/my-favorites'),
            'ogImage' => asset('images/logo/Logo.png'),
        ];

        return Inertia::render('favorites/Index', [
            'favorites' => $favorites,
            'filters' => $filters,
            'seoData' => $seoData,
        ]);
    }

    public function toggleFavorite(Property $property)
    {
        // Ensure user is authenticated
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        // Toggle favorite status
        $user->toggleFavorite($property);
        $isFavorited = $user->hasFavorited($property);

        return back()->with('success', $isFavorited ? 'Property added to favorites.' : 'Property removed from favorites.');
    }


    // If check-in and check-out dates are provided, check availability for that range and return boolean
    // Else, return unavailable periods
    public function getAvailability(Request $request, int $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }
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

    
    /**
     * Calculate the total price for a property booking based on check-in and check-out dates.
     *
     * Validates the request, retrieves the appropriate pricing for the date range,
     * calculates the total price, original price, savings, and discount percentage.
     * Returns a JSON response with all relevant pricing details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculatePrice(Request $request, int $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }
        $request->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        Log::info('Calculating price for property', ['property_id' => $property->id, 'check_in' => $request->check_in_date, 'check_out' => $request->check_out_date]);

        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $nights = $checkIn->diffInDays($checkOut);

        // Get current valid pricing for the given date range
        $pricing = $property->getPricingForDateRange($checkIn, $checkOut);
        if ($pricing->isEmpty()) {
            return response()->json([
                'error' => 'No pricing available for selected dates'
            ], 400);
        }
        Log::info('Pricing retrieved for calculation', ['pricing' => $pricing->toArray()]);
        
        // Calculate original price (ALWAYS use first nightly rate for comparison)
        $originalTotal = $nights * $pricing[0]->nightly_rate;

        Log::info('Original total price calculated', ['original_total' => $originalTotal]);
        
        // Calculate savings - only when using weekly/monthly rates
        $actualTotal = $property->calculateTotalPrice($checkIn, $checkOut);
        $savings = $originalTotal - $actualTotal;
        $discountPercentage = $savings > 0 ? round(($savings / $originalTotal) * 100) : 0;

        Log::info('Final price calculation', [
            'actual_total' => $actualTotal,
            'savings' => $savings,
            'discount_percentage' => $discountPercentage
        ]);
        
        return response()->json([
            'total_price' => $actualTotal,
            'original_price' => $originalTotal,
            'savings' => max(0, $savings), // Ensure never negative
            'discount_percentage' => $discountPercentage,
            'nights' => $nights, // Use actual nights, not from calculation
            'rate_used' => $actualTotal / $nights,
            'rate_per_night' => $actualTotal / $nights,
            'original_rate_per_night' => $pricing[0]->nightly_rate,
            'currency' => $pricing[0]->currency,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
        ]);
    }

    // Method to get all unique locations (villages) of properties
    public function getAllLocations()
    {
        return Cache::remember(self::LOCATIONS_CACHE_KEY, self::LOCATIONS_CACHE_DURATION, function () {
            Log::info('Fetching locations from database (cache miss)');
            
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

            return [
                'villages' => $villages,
                'districts' => $districts,
                'regencies' => $regencies,
            ];
        });

    // No need for response()->json() here since Cache::remember returns the data directly
    // If JSON response needed, wrap it:
    // return response()->json($locations);
    }

    // Add a new method for generating property-specific SEO
    private function generatePropertySEO($property, $currentPricing = null): array
    {
        $isRental = $property->listing_type === 'for_rent';
        $isLand = $property->property_type === 'land';
        
        // Generate title based on property type and listing type
        if ($isLand) {
            $title = "{$property->title} - Premium Land for Sale in {$property->district}";
            if ($currentPricing) {
                if ($property->listing_type === 'for_rent') {
                    $title .= " | " . number_format($currentPricing->price / 1000000, 1) . "IDR";
                } elseif ($property->listing_type === 'for_sale') {
                    $title .= " | " . number_format($currentPricing / 1000000000, 1) . "IDR";
                }
            }
        } else {
            $bedrooms = $property->bedrooms ? "{$property->bedrooms}BR " : "";
            $action = $isRental ? "Villa Rental" : "Villa for Sale";
            $title = "{$property->title} - {$bedrooms}{$action} in {$property->district}";
            
            if ($currentPricing && $isRental) {
                $monthlyPrice = number_format($currentPricing->price / 1000000, 1);
                $title .= " | {$monthlyPrice}M IDR/month";
            }
        }
        
        // Generate description
        if ($isLand) {
            $description = "Premium {$property->land_size}m² land for sale in {$property->district}, {$property->regency}. ";
            $description .= "Investment opportunity with our commission-based service. ";
            $description .= "Expert guidance for foreign investors in Bali property market.";
        } else {
            $features = [];
            if ($property->bedrooms) $features[] = "{$property->bedrooms} bedrooms";
            if ($property->bathrooms) $features[] = "{$property->bathrooms} bathrooms";
            if ($property->land_size) $features[] = "{$property->land_size}m² land";
            
            $featureText = implode(', ', $features);
            
            if ($isRental) {
                $description = "Rent {$property->title} - luxury villa with {$featureText} in {$property->district}. ";
                $description .= "Partner villa with full booking support and local management. ";
                $description .= "Secure reservation with our commission-based rental service.";
            } else {
                $description = "Buy {$property->title} - {$featureText} in {$property->district}. ";
                $description .= "Investment property with our expert sales guidance. ";
                $description .= "Commission-based service for international buyers.";
            }
        }
        
        // Generate keywords
        $keywords = [
            $property->property_type . ' ' . ($isRental ? 'rental' : 'sale'),
            $property->district . ' ' . $property->property_type,
            'bali ' . $property->property_type,
            $property->regency . ' property'
        ];
        
        if ($isRental) {
            $keywords[] = 'villa booking bali';
            $keywords[] = 'bali villa rental commission';
        } else {
            $keywords[] = 'bali property investment';
            $keywords[] = 'buy property bali foreigner';
        }
        
        if ($isLand) {
            $keywords[] = 'bali land investment';
            $keywords[] = 'land for sale bali';
        }
        
        // Select best OG image
        $ogImage = asset('images/logo/Logo.png'); // Default image
        if ($property->attachments) {
            $imageAttachment = collect($property->attachments)
                ->firstWhere('type', 'image');
                
            if ($imageAttachment) {
                $ogImage = $imageAttachment->url();
            }
        }
        
        return [
            'title' => $title,
            'description' => $description,
            'keywords' => implode(', ', $keywords),
            'canonicalUrl' => url("/properties/{$property->slug}"),
            'ogImage' => $ogImage,
        ];
    }
 }