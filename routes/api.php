<?php

use App\Http\Controllers\PropertyAttachmentController;
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