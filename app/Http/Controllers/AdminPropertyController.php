<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPrice;
use App\Models\PropertyAttachment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminPropertyController extends Controller
{
    /**
     * Display a paginated listing of all properties with search and filtering capabilities.
     * 
     * Supports filtering by:
     * - Search term (title, description, property_id, street_name, district)
     * - Property type (house, apartment, villa, etc.)
     * - Listing type (for_sale, for_rent, sold, off_market)
     * - Status (active, pending, sold, withdrawn)
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Build query with eager loading for performance
        $query = Property::with(['pricing', 'attachments', 'features'])
            ->orderBy('created_at', 'desc');

        // Apply search filter across multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('property_id', 'like', "%{$search}%")
                ->orWhere('street_name', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // Handle comma-separated location filters with OR logic
        $hasLocationFilter = $request->filled('villages') || $request->filled('districts') || $request->filled('regencies');

        if ($hasLocationFilter) {
            $query->where(function ($locationQuery) use ($request) {
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

        // Apply listing type filter
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Apply status filter (admin can see all statuses, not just active)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Paginate results
        $properties = $query->paginate(15)->withQueryString();

            return Inertia::render('admin/properties/Index', [
                'properties' => $properties,
                'filters' => $request->only(['search', 'property_type', 'listing_type', 'status']),
                'propertyTypes' => $this->getPropertyTypes(),
                'listingTypes' => $this->getListingTypes(),
                'statusOptions' => $this->getStatusOptions(),
            ]);
    }

    /**
     * Show the form for creating a new property.
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('admin/properties/Create', [
            'propertyTypes' => $this->getPropertyTypes(),
            'listingTypes' => $this->getListingTypes(),
            'priceTypes' => $this->getPriceTypes(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Store a newly created property in the database.
     * 
     * Handles:
     * - Property creation with all fields from migration
     * - File uploads (floor plans and images)
     * - PropertyPrice creation for rental properties
     * - PropertyAttachment creation for images
     * - Database transactions for data integrity
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate all property fields based on migration schema
        $validated = $request->validate([
            // Basic Information (required fields from migration)
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|in:house,apartment,townhouse,villa,land,commercial,guest_house,other',
            'listing_type' => 'required|in:for_sale,for_rent,sold,off_market',
            'status' => 'required|in:active,pending,sold,withdrawn',
            
            // Pricing Information
            'price' => 'nullable|integer|min:0',
            'price_type' => 'required|in:fixed,negotiable,auction,poa',
            
            // Address Information (Bali-specific structure)
            'street_number' => 'nullable|string|max:50',
            'street_name' => 'required|string|max:255',
            'village' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'regency' => 'required|string|max:255',
            'state' => 'string|max:255',
            'postcode' => 'required|string|max:10',
            'country' => 'string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Property Specifications
            'bedrooms' => 'nullable|integer|min:0|max:50',
            'bathrooms' => 'nullable|integer|min:0|max:50',
            'car_spaces' => 'nullable|integer|min:0|max:50',
            'land_size' => 'nullable|numeric|min:0',
            'floor_area' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1800|max:' . date('Y'),
            
            // Amenities (JSON structure from migration)
            'amenities' => 'nullable|array',
            'amenities.schools_nearby' => 'nullable|array',
            'amenities.transport' => 'nullable|array',
            'amenities.shopping' => 'nullable|array',
            'amenities.parks' => 'nullable|array',
            'amenities.medical' => 'nullable|array',
            
            // Property Details
            'zoning' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_premium' => 'boolean',
            'available_date' => 'nullable|date|after_or_equal:today',
            'inspection_times' => 'nullable|string',
            
            // Media
            'floor_plan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'virtual_tour_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            
            // Agent Information
            'agent_name' => 'required|string|max:255',
            'agent_phone' => 'nullable|string|max:20',
            'agent_email' => 'nullable|email|max:255',
            'agency_name' => 'nullable|string|max:255',
            
            // Rental Pricing (for PropertyPrice model)
            'nightly_rate' => 'nullable|integer|min:0',
            'weekly_rate' => 'nullable|integer|min:0',
            'monthly_rate' => 'nullable|integer|min:0',
            
            // Image Uploads
            'images' => 'nullable|array|max:20',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Use database transaction to ensure data integrity
        DB::beginTransaction();
        
        try {
            // Set default values for required fields
            $validated['property_id'] = $this->generatePropertyId();
            $validated['user_id'] = Auth::id();
            $validated['listed_at'] = now();
            $validated['state'] = $validated['state'] ?? 'Bali';
            $validated['country'] = $validated['country'] ?? 'Indonesia';
            $validated['days_on_market'] = 0;
            $validated['view_count'] = 0;

            // Handle floor plan file upload
            if ($request->hasFile('floor_plan')) {
                $validated['floor_plan'] = $request->file('floor_plan')->store('property-documents', 'public');
            }

            // Create the property record
            $property = Property::create($validated);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('property-images', 'public');
                    
                    PropertyAttachment::create([
                        'property_id' => $property->id,
                        'title' => "Property Image " . ($index + 1),
                        'path' => Storage::url($path),
                        'type' => 'image',
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.properties.show', $property)
                ->with('success', 'Property created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to create property: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified property with all related data.
     * 
     * @param Property $property
     * @return \Inertia\Response
     */
    public function show(Property $property)
    {
        // Load all relationships for complete property view
        $property->load([
            'pricing', 
            'attachments' => function($query) {
                $query->orderBy('order');
            },
            'features',
            'bookings' => function($query) {
                $query
                    ->orderBy('check_in_date', 'desc');
            }
        ]);

        // Map through attachments to generate signed URLs for secure access
        $attachmentsWithUrls = $property->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'title' => $attachment->title,
                'path' => $attachment->path,              // Keep for storage reference
                'url' => $attachment->url,                // ✅ This uses getUrlAttribute()
                'original_filename' => $attachment->original_filename,
                'file_type' => $attachment->file_type,
                'file_size' => $attachment->file_size,
                'type' => $attachment->type,
                'caption' => $attachment->caption,
                'is_visible_to_customer' => $attachment->is_visible_to_customer,
                'order' => $attachment->order,
                'created_at' => $attachment->created_at,
                'updated_at' => $attachment->updated_at,
            ];
        });

        // Get current pricing for rental properties
        $currentPricing = $property->pricing()
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        return Inertia::render('admin/properties/Show', [
            'property' => $property,
            'attachments' => $attachmentsWithUrls,
            'current_pricing' => $currentPricing,
            'map_api_key' => config('services.google.maps_api_key'),
            'propertyTypes' => $this->getPropertyTypes(),
            'listingTypes' => $this->getListingTypes(),
            'statusOptions' => $this->getStatusOptions(),
            'priceTypes' => $this->getPriceTypes(),
        ]);
    }

    /**
     * Show the form for editing the specified property.
     * 
     * @param Property $property
     * @return \Inertia\Response
     */
    public function edit(Property $property)
    {
        // Load relationships needed for editing
        $property->load(['pricing', 'attachments' => function($query) {
            $query->orderBy('order');
        }]);

        return Inertia::render('admin/properties/Edit', [
            'property' => $property,
            'propertyTypes' => $this->getPropertyTypes(),
            'listingTypes' => $this->getListingTypes(),
            'priceTypes' => $this->getPriceTypes(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Update the specified property in the database.
     * 
     * Handles the same operations as store() but for existing properties.
     * 
     * @param Request $request
     * @param Property $property
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Property $property)
    {
        // Use same validation rules as store method
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|in:house,apartment,townhouse,villa,land,commercial,guest_house,other',
            'listing_type' => 'required|in:for_sale,for_rent,sold,off_market',
            'status' => 'required|in:active,pending,sold,withdrawn',
            'price' => 'nullable|integer|min:0',
            'price_type' => 'required|in:fixed,negotiable,auction,poa',
            'street_number' => 'nullable|string|max:50',
            'street_name' => 'required|string|max:255',
            'village' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'regency' => 'required|string|max:255',
            'state' => 'string|max:255',
            'postcode' => 'required|string|max:10',
            'country' => 'string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'bedrooms' => 'nullable|integer|min:0|max:50',
            'bathrooms' => 'nullable|integer|min:0|max:50',
            'car_spaces' => 'nullable|integer|min:0|max:50',
            'land_size' => 'nullable|numeric|min:0',
            'floor_area' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1800|max:' . date('Y'),
            'amenities' => 'nullable|array',
            'zoning' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_premium' => 'boolean',
            'available_date' => 'nullable|date|after_or_equal:today',
            'inspection_times' => 'nullable|string',
            'floor_plan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'virtual_tour_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'agent_name' => 'required|string|max:255',
            'agent_phone' => 'nullable|string|max:20',
            'agent_email' => 'nullable|email|max:255',
            'agency_name' => 'nullable|string|max:255',
            'nightly_rate' => 'nullable|integer|min:0',
            'monthly_rate' => 'nullable|integer|min:0',
            'images' => 'nullable|array|max:20',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        DB::beginTransaction();
        
        try {
            // Handle floor plan replacement
            if ($request->hasFile('floor_plan')) {
                // Delete old floor plan if exists
                if ($property->floor_plan) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $property->floor_plan));
                }
                $validated['floor_plan'] = $request->file('floor_plan')->store('property-documents', 'public');
            }

            // Update the property
            $property->update($validated);

            // Update or create PropertyPrice for rental properties
            if ($validated['listing_type'] === 'for_rent') {
                $pricing = $property->pricing()->first();
                
                if ($pricing) {
                    // Update existing pricing
                    $pricing->update([
                        'nightly_rate' => $validated['nightly_rate'] ?? null,
                        'weekly_rate' => $validated['weekly_rate'] ?? null,
                        'monthly_rate' => $validated['monthly_rate'] ?? null,
                    ]);
                } else {
                    // Create new pricing record
                    PropertyPrice::create([
                        'property_id' => $property->id,
                        'nightly_rate' => $validated['nightly_rate'] ?? null,
                        'weekly_rate' => $validated['weekly_rate'] ?? null,
                        'monthly_rate' => $validated['monthly_rate'] ?? null,
                        'currency' => 'IDR',
                        'start_date' => now(),
                    ]);
                }
            } else {
                // Remove pricing for non-rental properties
                $property->pricing()->delete();
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $this->handleAttachments($request->file('images'), $property);
            }

            DB::commit();

            return redirect()->route('admin.properties.show', $property)
                ->with('success', 'Property updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to update property: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified property from the database.
     * 
     * Handles cascading deletion of:
     * - Associated files (floor plans, images)
     * - PropertyPrice records (via model relationships)
     * - PropertyAttachment records (via model relationships)
     * - Booking records (via model relationships)
     * 
     * @param Property $property
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Property $property)
    {
        DB::beginTransaction();
        
        try {
            // Delete floor plan file if exists
            if ($property->floor_plan) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $property->floor_plan));
            }

            // Delete all property image files
            foreach ($property->attachments as $attachment) {
                if ($attachment->type === 'image') {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $attachment->path));
                }
            }

            // Delete the property (foreign key constraints will handle related records)
            $property->delete();

            DB::commit();

            return redirect()->route('admin.properties.index')
                ->with('success', 'Property deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to delete property: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle the featured status of a property.
     * 
     * @param Property $property
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleFeatured(Property $property)
    {
        $property->update(['is_featured' => !$property->is_featured]);

        $status = $property->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Property has been {$status} successfully.");
    }

    /**
     * Toggle the premium status of a property.
     * 
     * @param Property $property
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePremium(Property $property)
    {
        $property->update(['is_premium' => !$property->is_premium]);

        $status = $property->is_premium ? 'marked as premium' : 'removed from premium';
        return back()->with('success', "Property has been {$status} successfully.");
    }

    /**
     * Delete a specific image attachment from a property.
     * 
     * @param Property $property
     * @param PropertyAttachment $attachment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteImage(Property $property, PropertyAttachment $attachment)
    {
        // Verify the attachment belongs to this property
        if ($attachment->property_id !== $property->id) {
            abort(404, 'Image not found for this property.');
        }

        // Delete the file from storage
        if ($attachment->type === 'image') {
            Storage::disk('public')->delete(str_replace('/storage/', '', $attachment->path));
        }

        // Delete the database record
        $attachment->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Reorder property images based on provided order array.
     * 
     * @param Request $request
     * @param Property $property
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reorderImages(Request $request, Property $property)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:property_attachments,id',
            'images.*.order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Update order for each image
            foreach ($request->images as $imageData) {
                PropertyAttachment::where('id', $imageData['id'])
                    ->where('property_id', $property->id) // Security check
                    ->update(['order' => $imageData['order']]);
            }

            DB::commit();

            return back()->with('success', 'Image order updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to reorder images.']);
        }
    }

    /**
     * Generate a unique property ID in format: PROP-YYYY-XXXXXX
     * 
     * @return string
     */
    private function generatePropertyId(): string
    {
        do {
            $propertyId = 'PROP-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (Property::where('property_id', $propertyId)->exists());

        return $propertyId;
    }

    // S3 Helper functions
    // This method handles the S3 upload of an image
    private function handleS3Upload($file, $propertyId)
    {
        // Handle S3 upload logic here
        $path = $file->store('properties/' . $propertyId . "/attachments" , 's3');
        return $path;
    }
    // This method takes the validated request, the job request object, and user
    // and handles the image attachments
    private function handleAttachments($attachments, $property)
    {
        // Check if the request has images
        foreach ($attachments as $attachment) {
            // Handle S3 upload logic here
            $path = $this->handleS3Upload($attachment, $property->id);
            // Create a new PropertyAttachment instance
            $propertyAttachment = new PropertyAttachment();

            // Based on file type, determine category of attachment
            if (in_array($attachment->getClientMimeType(), ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                $type = 'document';
            } else {
                $type = 'image';
            }

            // Fill the attachment details
            $propertyAttachment->fill([
                'property_id' => $property->id,
                'title' => $attachment->getClientOriginalName(),
                'path' => $path,
                'original_filename' => $attachment->getClientOriginalName(),
                'file_type' => $attachment->getClientMimeType(),
                'file_size' => $attachment->getSize(),
                'type' => $type,
                'caption' => null,
                'is_visible_to_customer' => false,
                'is_active' => true,
            ]);
            
            $propertyAttachment->save();
        }
    }

    /**
     * Get property type options from migration enum.
     * 
     * @return array
     */
    private function getPropertyTypes(): array
    {
        return [
            'house' => 'House',
            'apartment' => 'Apartment',
            'townhouse' => 'Townhouse', 
            'villa' => 'Villa',
            'land' => 'Land',
            'commercial' => 'Commercial',
            'guest_house' => 'Guest House',
            'other' => 'Other',
        ];
    }

    /**
     * Get listing type options from migration enum.
     * 
     * @return array
     */
    private function getListingTypes(): array
    {
        return [
            'for_sale' => 'For Sale',
            'for_rent' => 'For Rent',
            'sold' => 'Sold',
            'off_market' => 'Off Market',
        ];
    }

    /**
     * Get price type options from migration enum.
     * 
     * @return array
     */
    private function getPriceTypes(): array
    {
        return [
            'fixed' => 'Fixed Price',
            'negotiable' => 'Negotiable',
            'auction' => 'Auction',
            'poa' => 'Price on Application',
        ];
    }

    /**
     * Get status options from migration enum.
     * 
     * @return array
     */
    private function getStatusOptions(): array
    {
        return [
            'active' => 'Active',
            'pending' => 'Pending',
            'sold' => 'Sold',
            'withdrawn' => 'Withdrawn',
        ];
    }
}