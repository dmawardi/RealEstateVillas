<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPropertyPriceController extends Controller
{
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
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'message' => 'The date range overlaps with an existing pricing period.',
                    'errors' => [
                        'dates' => ['The selected dates overlap with an existing pricing period.']
                    ]
                ], 422);
            }

            // Create the pricing record
            $pricing = PropertyPrice::create([
                'property_id' => $property->id,
                'name' => $validated['name'],
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

            return response()->json([
                'message' => 'Property pricing created successfully.',
                'pricing' => $pricing
            ]);

        } catch (\Exception $e) {
            Log::error('Property pricing creation failed', [
                'error' => $e->getMessage(),
                'property_id' => $property->id,
                'name' => $validated['name'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the property pricing.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Property $property, PropertyPrice $pricing)
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
            $overlapping = PropertyPrice::where('property_id', $property->id)
                ->where('id', '!=', $pricing->id) // Exclude the current pricing record
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'message' => 'The date range overlaps with an existing pricing period.',
                    'errors' => [
                        'dates' => ['The selected dates overlap with an existing pricing period.']
                    ]
                ], 422);
            }

            // Update the pricing record
            $pricing->update([
                'name' => $validated['name'],
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
                'property_id' => $property->id,
                'name' => $pricing->name,
                'nightly_rate' => $pricing->nightly_rate,
                'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
                'currency' => $pricing->currency,
                'updated_fields' => array_keys($validated)
            ]);

            return response()->json([
                'message' => 'Property pricing updated successfully.',
                'pricing' => $pricing->fresh() // Return updated pricing data
            ]);

        } catch (\Exception $e) {
            Log::error('Property pricing update failed', [
                'error' => $e->getMessage(),
                'pricing_id' => $pricing->id,
                'property_id' => $property->id,
                'name' => $validated['name'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the property pricing.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Property $property, PropertyPrice $pricing)
    {
        try {
            $pricing->delete();

            Log::info('Property pricing deleted', [
                'pricing_id' => $pricing->id,
                'property_id' => $property->id,
                'name' => $pricing->name,
            ]);

            return response()->json([
                'message' => 'Property pricing deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Property pricing deletion failed', [
                'error' => $e->getMessage(),
                'pricing_id' => $pricing->id,
                'property_id' => $property->id,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while deleting the property pricing.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
