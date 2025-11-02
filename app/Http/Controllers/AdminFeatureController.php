<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFeatureController extends Controller
{
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

    public function destroy(Feature $feature)
    {
        $feature->delete();

        return redirect()->route('admin.features.index')
            ->with('success', 'Feature deleted successfully.');
    }
}
