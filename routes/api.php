<?php

use App\Http\Controllers\PropertyAttachmentController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

// Property API routes
// 

// Return all locations for search autocomplete
Route::get('/properties/locations', [PropertyController::class, 'getAllLocations']);

// Returns calculated price by accepting check in and checkout date
// Automatically applies any relevant discounts or promotions
Route::get('/properties/{property}/price', [PropertyController::class, 'calculatePrice']);

// Property availability
Route::get('/properties/{property}/availability', [PropertyController::class, 'getAvailability']);

// Property Attachments
// Index
Route::get('/properties/{property}/attachments', [PropertyAttachmentController::class, 'index']);
// Create
Route::post('/properties/{property}/attachments', [PropertyAttachmentController::class, 'store']);
// Update
Route::put('/attachments/{attachment}', [PropertyAttachmentController::class, 'update']);
// Delete
Route::delete('/attachments/{attachment}', [PropertyAttachmentController::class, 'destroy']);

// Property Bookings
Route::post('/properties/{property}/bookings', [\App\Http\Controllers\BookingController::class, 'store']);
Route::get('/properties/{property}/bookings', [\App\Http\Controllers\BookingController::class, 'index']);
Route::get('/properties/{property}/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show']);
Route::put('/properties/{property}/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'update']);
Route::delete('/properties/{property}/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy']);