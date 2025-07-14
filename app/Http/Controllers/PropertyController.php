<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    /**
     * Display a listing of the properties.
     */
    public function index()
    {
        $properties = Property::with(['user', 'features'])
            ->latest('listed_at')
            ->paginate(12);
        // Logic to retrieve and display properties
        return Inertia::render('properties/Index', compact('properties'));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        // Logic to show the property creation form
        return view('properties.create');
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
    {
        // Logic to validate and store the new property
        // ...
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property)
    {
        $property->load(['user', 'features']);
        // Logic to retrieve and display a specific property
        return Inertia::render('properties/Show', compact('property'));
    }
    /**
     * Show the form for editing the specified property.
     */
    public function edit($id)
    {
        // Logic to show the property edit form
        return view('properties.edit', compact('id'));  
    }
    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, $id)
    {
        // Logic to validate and update the property
        // ...
    }
    /**
     * Remove the specified property from storage.
     */
    public function destroy($id)
    {        // Logic to delete the property
        // ...
        return redirect()->route('properties.index')->with('success', 'Property deleted successfully.');    
    }
}
