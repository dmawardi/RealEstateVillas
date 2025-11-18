<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Get all available features (used by properties that are active) for filtering
     */
    public function getAvailableFeatures()
    {
        // Get features that are actually used by active properties
        $features = Feature::whereHas('properties', function ($query) {
            $query->where('status', 'active');
        })
        ->where('is_active', true)
        ->select('id', 'name', 'slug', 'category', 'icon')
        ->orderBy('category')
        ->orderBy('name')
        ->get()
        ->groupBy('category'); // Group by category for better organization
    
        return response()->json($features);
    }
}
