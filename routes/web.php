<?php

use App\Http\Controllers\AdminFeatureController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminPropertyController;
use App\Http\Controllers\BaseController;

Route::get('/', [BaseController::class, 'home'])->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');

// Bookings
Route::post('properties/{property}/bookings', [BookingController::class, 'store'])->name('properties.bookings.store');


// Logged in routes
// My Bookings
Route::get('my-bookings', [BookingController::class, 'index'])->middleware(['auth', 'verified'])->name('my.bookings');

// Dashboard
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/bookings/{booking}/withdraw', [BookingController::class, 'withdraw'])
        ->name('bookings.withdraw');

// ADMIN ROUTES
// 
// Properties
Route::get('admin/properties', [AdminPropertyController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('admin.properties.index');
Route::get('admin/properties/create', [AdminPropertyController::class, 'create'])->middleware(['auth', 'verified', 'admin'])->name('admin.properties.create');
Route::get('admin/properties/{property}', [AdminPropertyController::class, 'show'])->middleware(['auth', 'verified', 'admin'])->name('admin.properties.show');
Route::post('admin/properties', [AdminPropertyController::class, 'store'])->middleware(['auth', 'verified', 'admin'])->name('admin.properties.store');
Route::get('admin/properties/{property}/edit', [AdminPropertyController::class, 'edit'])->middleware(['auth', 'verified', 'admin'])->name('admin.properties.edit');
Route::put('admin/properties/{property}', [AdminPropertyController::class, 'update'])->middleware(['auth', 'verified', 'admin'])->name('admin.properties.update');

// 
// Property Features
Route::get('/admin/features', [AdminFeatureController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('admin.features.index');
Route::get('/admin/features/create', [AdminFeatureController::class, 'create'])->middleware(['auth', 'verified', 'admin'])->name('admin.features.create');
Route::post('/admin/features', [AdminFeatureController::class, 'store'])->middleware(['auth', 'verified', 'admin'])->name('admin.features.store');
Route::get('admin/features/{feature}/edit', [AdminFeatureController::class, 'edit'])->middleware(['auth', 'verified', 'admin'])->name('admin.features.edit');
Route::put('/admin/features/{feature}', [AdminFeatureController::class, 'update'])->middleware(['auth', 'verified', 'admin'])->name('admin.features.update');
Route::delete('/admin/features/{feature}', [AdminFeatureController::class, 'destroy'])->middleware(['auth', 'verified', 'admin'])->name('admin.features.destroy');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
