<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeatureController extends Controller
{
    /**
     * Cache key for available features
     */
    private const FEATURES_CACHE_KEY = 'available_features_for_filtering';
    
    /**
     * Cache duration in seconds (24 hours)
     */
    private const CACHE_DURATION = 60 * 60 * 6;

    /**
     * Get all available features (used by properties that are active) for filtering
     */
    public function getAvailableFeatures()
    {
        try {
            // Try to get features from cache first
            $features = Cache::remember(self::FEATURES_CACHE_KEY, self::CACHE_DURATION, function () {
                
                return Feature::where('is_active', true)
                ->select('id', 'name', 'slug', 'category', 'icon')
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->groupBy('category'); // Group by category for better organization
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
