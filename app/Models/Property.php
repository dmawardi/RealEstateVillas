<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        // For rental properties, fetch the current active pricing for the day
        if ($this->listing_type == 'for_rent') {
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
            } else {
                // If not for_rent, return base price
                return [];
            }
    }

    /**
     * Retrieve the current pricing records within the date range.
     *
     * @return \Illuminate\Database\Eloquent\Model|null The current pricing model instance, or null if none is active.
     */
    public function getPricingForDateRange($startDate, $endDate)
    {
        $checkIn = \Carbon\Carbon::parse($startDate);
        $checkOut = \Carbon\Carbon::parse($endDate);
        
        return $this->pricing()
            ->where(function($query) use ($checkIn, $checkOut) {
                // Find pricing periods that overlap with our date range
                $query->where(function($q) use ($checkOut) {
                    // Pricing starts before or on our checkout date
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $checkOut->format('Y-m-d'));
                })
                ->where(function($q) use ($checkIn) {
                    // Pricing ends after or on our checkin date
                    $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $checkIn->format('Y-m-d'));
                });
            })
            ->orderBy('start_date', 'asc')
            ->get();
    }

    public function calculateTotalPrice($startDate, $endDate)
    {
        $checkIn = \Carbon\Carbon::parse($startDate);
        $checkOut = \Carbon\Carbon::parse($endDate);
        $nights = $checkIn->diffInDays($checkOut);
        if ($nights <= 0) {
            return null; // Invalid date range
        }
        
        // Get all pricing records that cover the date range
        $pricingPeriods = $this->getPricingForDateRange($startDate, $endDate);
        if ($pricingPeriods->isEmpty()) {
            return null; // No pricing available
        }
        
        $total = 0;
        $currentDate = $checkIn->copy();

        while ($currentDate < $checkOut) {
            // Find applicable pricing for the current date
            $applicablePrice = null;
            $pricingPeriods->each(function($price) use ($currentDate, &$applicablePrice) {
                $startDate = $price->start_date ? \Carbon\Carbon::parse($price->start_date) : null;
                $endDate = $price->end_date ? \Carbon\Carbon::parse($price->end_date) : null;

                if (($startDate === null || $currentDate >= $startDate) &&
                    ($endDate === null || $currentDate <= $endDate)) {
                    $applicablePrice = $price;
                    return false; // Break the loop
                }
            });

            // If no pricing found for the date, return null
            if (!$applicablePrice) {
                Log::warning("No pricing found for date {$currentDate} in property ID {$this->id}");
                return null; // No pricing available
            }

            // Determine rate to apply (daily rate with applied discounts and premiums)
            $rate = $this->calculateDailyRate($applicablePrice, $nights, $currentDate);

            // Apply rate to total
            $total += $rate;

            // Move to next day
            $currentDate->addDay();
        }

        return $total;
    }

    /**
     * Calculate the daily rate with applied discounts and premiums
     */
    private function calculateDailyRate($pricing, int $totalNights, $date)
    {
        // Determine base rate based on total booking duration
        $baseRate = $pricing->nightly_rate;
        $discountPercent = 0; // Determined by long-stay discounts
        
        // Apply weekend premium if applicable
        if ($this->isWeekend($date->format('Y-m-d')) && ($pricing->weekend_premium_percent ?? 0) > 0 && $pricing->weekend_premium_active) {
            $baseRate += $baseRate * ($pricing->weekend_premium_percent / 100); // Add weekend premium
        }

        // Figure out if weekly or monthly discount applies
        if ($totalNights >= ($pricing->min_days_for_monthly ?? 30) && $pricing->monthly_discount_active) {
            $discountPercent = $pricing->monthly_discount_percent ?? 0;
        }
        elseif ($totalNights >= ($pricing->min_days_for_weekly ?? 7) && $pricing->weekly_discount_active) {
            $discountPercent = $pricing->weekly_discount_percent ?? 0;
        }
        // Apply discount
        if ($discountPercent > 0) {
            $baseRate = $baseRate * (1 - ($discountPercent / 100));
        }

        return $baseRate;
    }

    /**
     * Check if a date falls on a weekend.
     *
     * @param string $date The date to check in 'Y-m-d' format.
     * @return bool True if the date is a weekend, false otherwise.
     */
    private function isWeekend($date): bool
    {
        // 0 is Sunday, 6 is Saturday
        return in_array(\Carbon\Carbon::parse($date)->dayOfWeek, [0, 6]);
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
