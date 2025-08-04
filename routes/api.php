<?php

use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

// Property API routes
Route::get('/properties/locations', [PropertyController::class, 'getAllLocations']);
Route::get('/properties/{property}/availability', [PropertyController::class, 'getAvailability']);