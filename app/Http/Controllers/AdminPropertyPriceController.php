<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminPropertyPriceController extends Controller
{
    // Cache constants - match PropertyController
    private const PROPERTY_DETAIL_CACHE_PREFIX = 'property_detail_';
    /**
     * Store a newly created property pricing in storage.
     */
    public function store(Request $request, Property $property)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'nightly_rate' => 'required|numeric|min:0',
            'weekly_discount_percent' => 'nullable|numeric|min:0|max:100',
            'monthly_discount_percent' => 'nullable|numeric|min:0|max:100',
            'weekend_premium_percent' => 'nullable|numeric|min:0|max:100',
            'weekly_discount_active' => 'nullable|boolean',
            'monthly_discount_active' => 'nullable|boolean',
            'weekend_premium_active' => 'nullable|boolean',
            'currency' => 'nullable|string|size:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_days_for_weekly' => 'nullable|integer|min:1',
            'min_days_for_monthly' => 'nullable|integer|min:1',
        ]);

        try {
            // Parse dates to ensure proper formatting
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            // Check for overlapping pricing periods for the same property
            $overlapping = PropertyPrice::where('property_id', $property->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        // Check if new start date falls within existing range
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $startDate);
                    })
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        // Check if new end date falls within existing range  
                        $q->where('start_date', '<=', $endDate)
                          ->where('end_date', '>=', $endDate);
                    })
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        // Check if new range completely encompasses existing range
                        $q->where('start_date', '>=', $startDate)
                          ->where('end_date', '<=', $endDate);
                    });
                })
                ->exists();

            if ($overlapping) {
                return back()->withErrors([
                    'dates' => 'The selected dates overlap with an existing pricing period.'
                ])->withInput();
            }

            // Create the pricing record
            $pricing = PropertyPrice::create([
                'property_id' => $property->id,
                'name' => $validated['name'] ?? null,
                'nightly_rate' => $validated['nightly_rate'],
                'weekly_discount_percent' => $validated['weekly_discount_percent'] ?? 0,
                'monthly_discount_percent' => $validated['monthly_discount_percent'] ?? 0,
                'weekend_premium_percent' => $validated['weekend_premium_percent'] ?? 0,
                'weekly_discount_active' => $validated['weekly_discount_active'] ?? false,
                'monthly_discount_active' => $validated['monthly_discount_active'] ?? false,
                'weekend_premium_active' => $validated['weekend_premium_active'] ?? false,
                'currency' => $validated['currency'] ?? 'IDR',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'min_days_for_weekly' => $validated['min_days_for_weekly'] ?? 7,
                'min_days_for_monthly' => $validated['min_days_for_monthly'] ?? 30,
            ]);

            Log::info('Property pricing created', [
                'pricing_id' => $pricing->id,
                'property_id' => $property->id,
                'name' => $pricing->name,
                'nightly_rate' => $pricing->nightly_rate,
                'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
                'currency' => $pricing->currency
            ]);

            // Clear property detail cache to ensure frontend gets updated pricing
            $this->clearPropertyDetailCache($property->id);

            return back()->with('success', 'Property pricing created successfully.');

        } catch (\Exception $e) {
            Log::error('Property pricing creation failed', [
                'error' => $e->getMessage(),
                'property_id' => $property->id,
                'name' => $validated['name'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while creating the property pricing.')->withInput();
        }
    }

    public function update(Request $request, PropertyPrice $pricing)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'nightly_rate' => 'required|numeric|min:0',
            'weekly_discount_percent' => 'nullable|numeric|min:0|max:100',
            'monthly_discount_percent' => 'nullable|numeric|min:0|max:100',
            'weekend_premium_percent' => 'nullable|numeric|min:0|max:100',
            'weekly_discount_active' => 'nullable|boolean',
            'monthly_discount_active' => 'nullable|boolean',
            'weekend_premium_active' => 'nullable|boolean',
            'currency' => 'nullable|string|size:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_days_for_weekly' => 'nullable|integer|min:1',
            'min_days_for_monthly' => 'nullable|integer|min:1',
        ]);

        try {
            // Parse dates to ensure proper formatting
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            // Check for overlapping pricing periods for the same property (excluding current record)
            $overlapping = PropertyPrice::where('property_id', $pricing->property_id)
                ->where('id', '!=', $pricing->id) // Exclude the current pricing record
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        // Check if new start date falls within existing range
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $startDate);
                    })
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        // Check if new end date falls within existing range  
                        $q->where('start_date', '<=', $endDate)
                          ->where('end_date', '>=', $endDate);
                    })
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        // Check if new range completely encompasses existing range
                        $q->where('start_date', '>=', $startDate)
                          ->where('end_date', '<=', $endDate);
                    });
                })
                ->exists();

            // Debug logging
            Log::info('Overlap check', [
                'property_id' => $pricing->property_id,
                'pricing_id' => $pricing->id,
                'new_start_date' => $startDate->toDateString(),
                'new_end_date' => $endDate->toDateString(),
                'existing_pricing' => PropertyPrice::where('property_id', $pricing->property_id)
                    ->where('id', '!=', $pricing->id)->get()->toArray(),
                'overlap_found' => $overlapping
            ]);

            if ($overlapping) {
                return back()->withErrors([
                    'dates' => 'The selected dates overlap with an existing pricing period.'
                ])->withInput();
            }

            // Update the pricing record
            $pricing->update([
                'name' => $validated['name'] ?? $pricing->name,
                'nightly_rate' => $validated['nightly_rate'],
                'weekly_discount_percent' => $validated['weekly_discount_percent'] ?? $pricing->weekly_discount_percent ?? 0,
                'monthly_discount_percent' => $validated['monthly_discount_percent'] ?? $pricing->monthly_discount_percent ?? 0,
                'weekend_premium_percent' => $validated['weekend_premium_percent'] ?? $pricing->weekend_premium_percent ?? 0,
                'weekly_discount_active' => $validated['weekly_discount_active'] ?? $pricing->weekly_discount_active ?? false,
                'monthly_discount_active' => $validated['monthly_discount_active'] ?? $pricing->monthly_discount_active ?? false,
                'weekend_premium_active' => $validated['weekend_premium_active'] ?? $pricing->weekend_premium_active ?? false,
                'currency' => $validated['currency'] ?? $pricing->currency ?? 'IDR',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'min_days_for_weekly' => $validated['min_days_for_weekly'] ?? $pricing->min_days_for_weekly ?? 7,
                'min_days_for_monthly' => $validated['min_days_for_monthly'] ?? $pricing->min_days_for_monthly ?? 30,
            ]);

            Log::info('Property pricing updated', [
                'pricing_id' => $pricing->id,
                'property_id' => $pricing->property_id,
                'name' => $pricing->name,
                'nightly_rate' => $pricing->nightly_rate,
                'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
                'currency' => $pricing->currency,
                'updated_fields' => array_keys($validated)
            ]);

            // Clear property detail cache to ensure frontend gets updated pricing
            $this->clearPropertyDetailCache($pricing->property_id);

            return back()->with('success', 'Property pricing updated successfully.');

        } catch (\Exception $e) {
            Log::error('Property pricing update failed', [
                'error' => $e->getMessage(),
                'pricing_id' => $pricing->id,
                'property_id' => $pricing->property_id,
                'name' => $validated['name'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while updating the property pricing.')->withInput();
        }
    }

    public function destroy(PropertyPrice $pricing)
    {
        try {
            // Store property ID before deletion
            $propertyId = $pricing->property_id;
            
            $pricing->delete();

            Log::info('Property pricing deleted', [
                'pricing_id' => $pricing->id,
                'property_id' => $propertyId,
                'name' => $pricing->name,
            ]);

            // Clear property detail cache to ensure frontend gets updated pricing
            $this->clearPropertyDetailCache($propertyId);

            return back()->with('success', 'Property pricing deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Property pricing deletion failed', [
                'error' => $e->getMessage(),
                'pricing_id' => $pricing->id,
                'property_id' => $pricing->property_id,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while deleting the property pricing.');
        }
    }

    /**
     * Clear property detail cache for a specific property.
     * This ensures the frontend gets updated pricing information immediately.
     * 
     * @param int $propertyId
     * @return bool
     */
    private function clearPropertyDetailCache(int $propertyId): bool
    {
        $cacheKey = self::PROPERTY_DETAIL_CACHE_PREFIX . $propertyId;
        
        Log::info('Clearing property detail cache', [
            'property_id' => $propertyId,
            'cache_key' => $cacheKey
        ]);
        
        return Cache::forget($cacheKey);
    }
}
