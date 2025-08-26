<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
