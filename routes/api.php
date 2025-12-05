<?php

use App\Http\Controllers\AdminPropertyController;
use App\Http\Controllers\PropertyAttachmentController;
use App\Http\Controllers\AdminPropertyPriceController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

// Property API routes
// 

// Return all locations for search autocomplete
Route::get('/properties/locations', [PropertyController::class, 'getAllLocations'])->name('properties.locations');

// Returns calculated price by accepting check in and checkout date
// Automatically applies any relevant discounts or promotions
Route::get('/properties/{property}/price', [PropertyController::class, 'calculatePrice'])->name('properties.price');

// Property availability
Route::get('/properties/{property}/availability', [PropertyController::class, 'getAvailability'])->name('properties.availability');

// Property Attachments
// Index
Route::get('/properties/{property}/attachments', [PropertyAttachmentController::class, 'index'])->name('properties.attachments.index');
// Create
Route::post('/properties/{property}/attachments', [PropertyAttachmentController::class, 'store'])->name('properties.attachments.store');
// Update
Route::put('/attachments/{attachment}', [PropertyAttachmentController::class, 'update'])->name('attachments.update');
// Delete
Route::delete('/attachments/{attachment}', [PropertyAttachmentController::class, 'destroy'])->name('attachments.destroy');

// Property Bookings
Route::post('/properties/{property}/bookings', [\App\Http\Controllers\BookingController::class, 'store']);
Route::get('/properties/{property}/bookings', [\App\Http\Controllers\BookingController::class, 'index'])->name('properties.bookings.index');
Route::get('/properties/{property}/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show'])->name('properties.bookings.show');
Route::put('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'update'])->name('properties.bookings.update');
Route::delete('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy'])->name('properties.bookings.destroy');

// Property Pricing
Route::get('/properties/{property}/pricing', [\App\Http\Controllers\AdminPropertyPriceController::class, 'index'])->name('properties.pricing.index');
Route::post('/properties/{property}/pricing', [\App\Http\Controllers\AdminPropertyPriceController::class, 'store'])->name('properties.pricing.store');
Route::put('/pricing/{pricing}', [\App\Http\Controllers\AdminPropertyPriceController::class, 'update'])->name('properties.pricing.update');
Route::delete('/pricing/{pricing}', [\App\Http\Controllers\AdminPropertyPriceController::class, 'destroy'])->name('properties.pricing.destroy');

// Property feature management routes
// Admin routes
Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('admin/properties/available-features', [FeatureController::class, 'getAvailableFeatures'])->name('properties.features');
    // Feature cache management routes
    Route::delete('/admin/cache/features', [FeatureController::class, 'clearFeaturesCache'])->name('cache.features.clear');
    Route::post('/admin/cache/features/refresh', [FeatureController::class, 'refreshFeaturesCache'])->name('cache.features.refresh');
    Route::get('/admin/cache/features/info', [FeatureController::class, 'getCacheInfo'])->name('cache.features.info');

    // Property Detail refresh route
    Route::post('/admin/properties/{property}/refresh-details', [AdminPropertyController::class, 'clearPropertyDetailCache'])->name('properties.refresh-details');
    // Location refresh route
    Route::post('/admin/locations/{location}/refresh-details', [AdminPropertyController::class, 'clearLocationsCache'])->name('locations.refresh-details');
});