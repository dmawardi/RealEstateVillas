<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminFeatureController extends Controller
{
    /**
     * Cache key for available features
     */
    private const FEATURES_CACHE_KEY = 'available_features_for_filtering';
    
    /**
     * Cache duration in seconds (24 hours)
     */
    private const CACHE_DURATION = 60 * 60 * 6;

    public function index(Request $request)
    {
        // Build query with eager loading for performance
        $query = Feature::orderBy('created_at', 'desc');

        // Apply search filter across multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $features = $query->paginate(15)->withQueryString();

        
        return Inertia::render('admin/features/Index', [
            'features' => $features,
            'filters' => request()->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/features/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:features,slug',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:65535', // Allow large SVG content
            'is_quantifiable' => 'required|boolean',
            'is_active' => 'required|boolean',
        ]);

        Feature::create($validated);

        return redirect()->route('admin.features.index')
            ->with('success', 'Feature created successfully.');
    }

    public function edit(Feature $feature)
    {
        return Inertia::render('admin/features/Edit', [
            'feature' => $feature,
        ]);
    }

    public function update(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:features,slug,' . $feature->id,
            'category' => 'sometimes|string|max:100',
            'description' => 'sometimes|nullable|string',
            'icon' => 'sometimes|nullable|string|max:65535', // Allow large SVG content
            'is_quantifiable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $feature->update($validated);

        return redirect()->route('admin.features.index')
            ->with('success', 'Feature updated successfully.');
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();

        return redirect()->route('admin.features.index')
            ->with('success', 'Feature deleted successfully.');
    }

    /**
     * Get all available features (used by properties that are active) for filtering
     */
    public function getAvailableFeatures()
    {
        try {
            // Try to get features from cache first
            $features = Cache::remember(self::FEATURES_CACHE_KEY, self::CACHE_DURATION, function () {
                
                return Feature::where('is_active', true)
                ->select('id', 'name', 'slug', 'category', 'icon', 'is_quantifiable')
                ->orderBy('category')
                ->orderBy('name')
                ->groupBy('category') // Group by category for better organization
                ->get();
            });

            return response()->json($features)
                ->header('Cache-Control', 'public, max-age=' . self::CACHE_DURATION)
                ->header('X-Cache-Status', Cache::has(self::FEATURES_CACHE_KEY) ? 'HIT' : 'MISS');

        } catch (\Exception $e) {
            Log::error('Failed to fetch available features', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load features',
                'message' => 'Please try again later'
            ], 500);
        }
    }

    /**
     * Clear the features cache
     * This should be called when features or properties are updated
     */
    public function clearFeaturesCache(): bool
    {
        return Cache::forget(self::FEATURES_CACHE_KEY);
    }

    /**
     * Refresh the features cache
     * Useful for warming up the cache after updates
     */
    public function refreshFeaturesCache()
    {
        try {
            // Clear existing cache
            $this->clearFeaturesCache();
            
            // Warm up the cache
            $this->getAvailableFeatures();
            
            return response()->json([
                'message' => 'Features cache refreshed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to refresh features cache', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to refresh cache'
            ], 500);
        }
    }

    /**
     * Get cache information for debugging
     */
    public function getCacheInfo()
    {
        $cacheExists = Cache::has(self::FEATURES_CACHE_KEY);
        $cacheSize = $cacheExists ? strlen(serialize(Cache::get(self::FEATURES_CACHE_KEY))) : 0;
        
        return response()->json([
            'cache_key' => self::FEATURES_CACHE_KEY,
            'cache_exists' => $cacheExists,
            'cache_size_bytes' => $cacheSize,
            'cache_duration_seconds' => self::CACHE_DURATION,
        ]);
    }
}
