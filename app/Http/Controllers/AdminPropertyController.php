<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPrice;
use App\Models\PropertyAttachment;
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
        $query = Property::with(['user', 'pricing', 'attachments'])
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

        // Apply property type filter
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Apply listing type filter
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        return Inertia::render('Admin/Properties/Create', [
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

            // Create PropertyPrice record for rental properties
            if ($validated['listing_type'] === 'for_rent' && 
                ($validated['nightly_rate'] || $validated['weekly_rate'] || $validated['monthly_rate'])) {
                
                PropertyPrice::create([
                    'property_id' => $property->id,
                    'nightly_rate' => $validated['nightly_rate'],
                    'weekly_rate' => $validated['weekly_rate'],
                    'monthly_rate' => $validated['monthly_rate'],
                    'currency' => 'IDR',
                    'start_date' => now(),
                    'end_date' => null,
                ]);
            }

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
            'user', 
            'pricing', 
            'attachments' => function($query) {
                $query->orderBy('order');
            },
            'bookings' => function($query) {
                $query->where('status', 'confirmed')
                      ->orderBy('check_in_date');
            }
        ]);

        return Inertia::render('Admin/Properties/Show', [
            'property' => $property,
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

        return Inertia::render('Admin/Properties/Edit', [
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
            'weekly_rate' => 'nullable|integer|min:0',
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
                $existingCount = $property->attachments()->count();
                
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('property-images', 'public');
                    
                    PropertyAttachment::create([
                        'property_id' => $property->id,
                        'title' => "Property Image " . ($existingCount + $index + 1),
                        'path' => Storage::url($path),
                        'type' => 'image',
                        'order' => $existingCount + $index,
                    ]);
                }
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