<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Property extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [    
        'id', // Primary key, not mass assignable
        'created_at', // Timestamps are managed by Eloquent
        'updated_at',
    ];

    /**
     * The features that belong to the property.
     */
    public function features()
    {
        return $this->belongsToMany(Feature::class)
            ->withPivot('quantity', 'notes')
            ->withTimestamps();
    }

    /**
     * The pricing information for the property.
     */
    public function pricing()
    {
        return $this->hasMany(PropertyPrice::class, 'property_id');
    }

    /**
     * The attachments for the property.
     */
    public function attachments()
    {
        return $this->hasMany(PropertyAttachment::class, 'property_id');
    }

    /**
     * The bookings associated with the property.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'property_id');
    }

    /**
     * Get users who have favorited this property.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
                    ->withTimestamps()
                    ->orderBy('favorites.created_at', 'desc');
    }

    
    // Helper methods for favorites
    /**
     * Get the favorites count for this property.
     */
    public function getFavoritesCountAttribute(): int
    {
        return $this->favoritedBy()->count();
    }

    /**
     * Check if a specific user has favorited this property.
     */
    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->favoritedBy()->where('user_id', $user->id)->exists();
    }

    // Helper methods for bookings
    /**
     * Get all confirmed bookings for the property.
     */
    public function confirmedBookings()
    {
        return $this->hasMany(Booking::class)->where('status', 'confirmed');
    }

    /**
     * Check if property is available for given dates
     */
    public function isAvailableForDates($checkInDate, $checkOutDate)
    {
        return !$this->bookings()
            ->where('status', 'confirmed')
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('check_in_date', '<', $checkOutDate)
                    ->where('check_out_date', '>', $checkInDate);
            })
            ->exists();
    }

    // Helper method to get current pricing
    /**
     * Retrieve the current active pricing for the property.
     *
     * This method queries the related pricing records and returns the first pricing entry
     * that is currently valid. A pricing is considered current if:
     * - Its 'start_date' is either null (no start restriction) or less than or equal to the current date/time.
     * - Its 'end_date' is either null (no end restriction) or greater than or equal to the current date/time.
     *
     * @return \Illuminate\Database\Eloquent\Model|null The current pricing model instance, or null if none is active.
     */
    public function getCurrentPricing()
    {
        return $this->pricing()
            ->where(function($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();
    }

    public function getPricingForDateRange($startDate, $endDate)
    {
        return $this->pricing()
            ->where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $endDate)
            ->first();
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'rental_price_weekly' => 'integer',
            'rental_price_monthly' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'land_size' => 'decimal:2',
            'floor_area' => 'decimal:2',
            'amenities' => 'json', // This is correct
            'listed_at' => 'datetime',
            'available_date' => 'date', // Add this cast
            'year_built' => 'integer',
            'is_featured' => 'boolean', // Add this cast
            'is_premium' => 'boolean', // Add this cast
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'car_spaces' => 'integer',
            'days_on_market' => 'integer', // Add this cast
            'view_count' => 'integer', // Add this cast
        ];
    }

    protected static function booted()
    {
        static::saved(function ($property) {
            // Clear property detail cache
            Cache::forget('property_detail_' . $property->id);
            
            // Clear locations cache if location fields changed
            if ($property->isDirty(['village', 'district', 'regency', 'status'])) {
                Cache::forget('property_locations');
            }

            // Only clear feature availability cache if status changed to/from active
            if ($property->isDirty('status') && 
                (in_array($property->getOriginal('status'), ['active']) || 
                in_array($property->status, ['active']))) {
                Cache::forget('available_features_for_filtering');
            }
        });

        static::deleted(function ($property) {
            // Clear all related caches
            Cache::forget('property_detail_' . $property->id);
            Cache::forget('property_locations');
            // Only clear if deleted property was active
            if ($property->status === 'active') {
                Cache::forget('available_features_for_filtering');
            }
        });
    }
}
