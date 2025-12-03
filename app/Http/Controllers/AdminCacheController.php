<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminCacheController extends Controller
{
    // Display the cache management page
    public function index()
    {

        return Inertia::render('admin/cache/Index');
    }

    // Clear application cache
    public function clearAppCache(Request $request)
    {
        try {
            // Clear various caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            Log::info('Application cache cleared by user ID: ' . $request->user()->id);

            // Use back() instead of redirect()->route() to stay on same page
            return back()->with('success', 'Application cache cleared successfully!');

        } catch (\Exception $e) {
            Log::error('Cache clearing failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to clear cache. Please try again.');
        }
    }
}
