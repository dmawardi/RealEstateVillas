<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminCacheController;
use App\Http\Controllers\AdminFeatureController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminPropertyController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\SupportController;

Route::get('/', [BaseController::class, 'home'])->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');

Route::get('/contact', [BaseController::class, 'contact'])->name('contact');
Route::post('/contact', [BaseController::class, 'submitContactForm'])->name('contact.submit');

Route::post('/newsletter/subscribe', [BaseController::class, 'subscribe'])->name('newsletter.subscribe');

// Support Routes
Route::get('/support/faq', [SupportController::class, 'faq'])->name('support.faq');
Route::get('/support/terms-of-service', [SupportController::class, 'termsOfService'])->name('support.termsOfService');
Route::get('/support/cookie-policy', [SupportController::class, 'cookiePolicy'])->name('support.cookiePolicy');
Route::get('/support/privacy-policy', [SupportController::class, 'privacyPolicy'])->name('support.privacyPolicy');

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

    // Favorites
    Route::get('/my-favorites', [PropertyController::class, 'favorites'])->name('my.favorites');
    Route::post('/properties/{property}/toggle-favorite', [PropertyController::class, 'toggleFavorite'])->name('properties.toggle-favorite');
});



// ADMIN ROUTES
Route::middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    // User Management
    // Export
    Route::get('admin/users/export', [AdminUserController::class, 'export'])->name('users.export');
    // CRUD
    Route::get('admin/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('admin/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::get('admin/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('admin/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('admin/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    // Impersonate User
    Route::post('admin/users/{user}/impersonate', [AdminUserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('admin/users/stop-impersonation', [AdminUserController::class, 'stopImpersonation'])->name('users.stopImpersonation');
    // Email verification
    Route::patch('admin/users/{user}/toggle-email-verification', [AdminUserController::class, 'toggleEmailVerification'])->name('users.toggle-email-verification');
    // Resend email
    Route::post('admin/users/{user}/resend-verification-email', [AdminUserController::class, 'resendEmailVerification'])->name('users.resend-email-verification');

    // Properties
    Route::get('admin/properties', [AdminPropertyController::class, 'index'])->name('properties.index');
    Route::get('admin/properties/create', [AdminPropertyController::class, 'create'])->name('properties.create');
    Route::get('admin/properties/{property}', [AdminPropertyController::class, 'show'])->name('properties.show');
    Route::post('admin/properties', [AdminPropertyController::class, 'store'])->name('properties.store');
    Route::get('admin/properties/{property}/edit', [AdminPropertyController::class, 'edit'])->name('properties.edit');
    Route::put('admin/properties/{property}', [AdminPropertyController::class, 'update'])->name('properties.update');
    Route::delete('admin/properties/{property}', [AdminPropertyController::class, 'destroy'])->name('properties.destroy');

    // Property Notes Management
    Route::patch('admin/properties/{property}/notes', [AdminPropertyController::class, 'updateNotes'])->name('properties.notes.update');

    // Property Features CRUD routes
    Route::get('admin/features', [AdminFeatureController::class, 'index'])->name('features.index');
    Route::get('admin/features/create', [AdminFeatureController::class, 'create'])->name('features.create');
    Route::post('admin/features', [AdminFeatureController::class, 'store'])->name('features.store');
    Route::get('admin/features/{feature}/edit', [AdminFeatureController::class, 'edit'])->name('features.edit');
    Route::put('admin/features/{feature}', [AdminFeatureController::class, 'update'])->name('features.update');
    Route::delete('admin/features/{feature}', [AdminFeatureController::class, 'destroy'])->name('features.destroy');
    
    // Cache Management
    Route::get('admin/cache', [AdminCacheController::class, 'index'])->name('cache.index');
    Route::post('admin/cache/clear', [AdminCacheController::class, 'clearAppCache'])->name('cache.clear');
    // Property feature pivot management routes
    Route::patch('admin/properties/{property}/features', [AdminPropertyController::class, 'updateFeatures'])
        ->name('properties.features.update');
    Route::post('admin/properties/{property}/features', [AdminPropertyController::class, 'attachFeature'])
        ->name('properties.features.attach');
    Route::delete('admin/properties/{property}/features/{feature}', [AdminPropertyController::class, 'detachFeature'])
        ->name('properties.features.detach');
    Route::patch('admin/properties/{property}/features/{feature}', [AdminPropertyController::class, 'updatePropertyFeature'])
        ->name('properties.features.update-single');

    // Booking Management
    Route::get('admin/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('admin/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('admin/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::get('admin/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('admin/bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
    Route::put('admin/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::delete('admin/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
    
    // Property Attachments
    Route::get('admin/properties/{property}/attachments', [\App\Http\Controllers\AdminPropertyAttachmentController::class, 'index'])->name('properties.attachments.index');
    Route::post('admin/properties/{property}/attachments', [\App\Http\Controllers\AdminPropertyAttachmentController::class, 'store'])->name('properties.attachments.store');
    Route::put('admin/attachments/{attachment}', [\App\Http\Controllers\AdminPropertyAttachmentController::class, 'update'])->name('attachments.update');
    Route::delete('admin/attachments/{attachment}', [\App\Http\Controllers\AdminPropertyAttachmentController::class, 'destroy'])->name('attachments.destroy');
    
    // Property Pricing
    Route::get('admin/properties/{property}/pricing', [\App\Http\Controllers\AdminPropertyPriceController::class, 'index'])->name('properties.pricing.index');
    Route::post('admin/properties/{property}/pricing', [\App\Http\Controllers\AdminPropertyPriceController::class, 'store'])->name('properties.pricing.store');
    Route::put('admin/pricing/{pricing}', [\App\Http\Controllers\AdminPropertyPriceController::class, 'update'])->name('pricing.update');
    Route::delete('admin/pricing/{pricing}', [\App\Http\Controllers\AdminPropertyPriceController::class, 'destroy'])->name('pricing.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
