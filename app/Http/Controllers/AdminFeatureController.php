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
            'icon' => 'nullable|string|max:100',
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
            'icon' => 'sometimes|nullable|string|max:100',
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
}
