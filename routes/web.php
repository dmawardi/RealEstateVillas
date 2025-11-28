<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminFeatureController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminPropertyController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\SupportController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;

Route::get('/', [BaseController::class, 'home'])->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');

Route::get('/contact', [BaseController::class, 'contact'])->name('contact');
Route::post('/contact', [BaseController::class, 'submitContactForm'])->name('contact.submit');

// Support Routes
Route::get('/support/faq', [SupportController::class, 'faq'])->name('support.faq');
Route::get('/support/terms-of-service', [SupportController::class, 'termsOfService'])->name('support.termsOfService');
Route::get('/support/cookie-policy', [SupportController::class, 'cookiePolicy'])->name('support.cookiePolicy');
Route::get('/support/privacy-policy', [SupportController::class, 'privacyPolicy'])->name('support.privacyPolicy');

// EMAIL VERIFICATION
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Marks user as verified
    return redirect('/dashboard'); // or wherever you want to send them
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Logged in routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [BaseController::class, 'dashboard'])->name('dashboard');
    
    // Create Booking Request
    Route::post('properties/{property}/bookings', [BookingController::class, 'store'])->name('properties.bookings.store');
    // My Bookings
    Route::get('my-bookings', [BookingController::class, 'index'])->name('my.bookings');
    // Withdraw booking
    Route::post('/bookings/{booking}/withdraw', [BookingController::class, 'withdraw'])
        ->name('bookings.withdraw');
});

// ADMIN ROUTES
Route::middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    // Properties
    Route::get('admin/properties', [AdminPropertyController::class, 'index'])->name('properties.index');
    Route::get('admin/properties/create', [AdminPropertyController::class, 'create'])->name('properties.create');
    Route::get('admin/properties/{property}', [AdminPropertyController::class, 'show'])->name('properties.show');
    Route::post('admin/properties', [AdminPropertyController::class, 'store'])->name('properties.store');
    Route::get('admin/properties/{property}/edit', [AdminPropertyController::class, 'edit'])->name('properties.edit');
    Route::put('admin/properties/{property}', [AdminPropertyController::class, 'update'])->name('properties.update');
    Route::delete('admin/properties/{property}', [AdminPropertyController::class, 'destroy'])->name('properties.destroy');
    // Property Features CRUD routes
    Route::get('/admin/features', [AdminFeatureController::class, 'index'])->name('features.index');
    Route::get('/admin/features/create', [AdminFeatureController::class, 'create'])->name('features.create');
    Route::post('/admin/features', [AdminFeatureController::class, 'store'])->name('features.store');
    Route::get('admin/features/{feature}/edit', [AdminFeatureController::class, 'edit'])->name('features.edit');
    Route::put('/admin/features/{feature}', [AdminFeatureController::class, 'update'])->name('features.update');
    Route::delete('/admin/features/{feature}', [AdminFeatureController::class, 'destroy'])->name('features.destroy');

    
    // Property feature pivot management routes
    Route::patch('/properties/{property}/features', [AdminPropertyController::class, 'updateFeatures'])
        ->name('properties.features.update');
    Route::post('/properties/{property}/features', [AdminPropertyController::class, 'attachFeature'])
        ->name('properties.features.attach');
    Route::delete('/properties/{property}/features/{feature}', [AdminPropertyController::class, 'detachFeature'])
        ->name('properties.features.detach');
    Route::patch('/properties/{property}/features/{feature}', [AdminPropertyController::class, 'updatePropertyFeature'])
        ->name('properties.features.update-single');

    // Booking Management
    Route::get('admin/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/admin/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('/admin/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::get('admin/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('admin/bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
    Route::put('admin/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::delete('admin/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
