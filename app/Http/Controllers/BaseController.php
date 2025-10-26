<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BaseController extends Controller
{
    public function home()
    {
        $all = Cache::get('properties:featured_premium');
        Log::info('Fetching featured and premium properties', ['count' => $all]);

        if (!$all) {
            Log::info('Cache miss for featured and premium properties, querying database.');
            $all = Property::where('is_featured', true)
            ->orWhere('is_premium', true)
            ->get();
            Log::info('Queried featured and premium properties from database.');

            Cache::put('properties:featured_premium', $all, 60);
        }

        $featured = $all->where('is_featured', true)->values()->toArray();
        $premium  = $all->where('is_premium', true)->values()->toArray();
        Log::info('Prepared featured and premium properties for view.', ['featured_count' => count($featured), 'premium_count' => count($premium)]);

        return Inertia::render('Welcome', [
            'featured' => $featured,
            'premium' => $premium
        ]);
    }
}
