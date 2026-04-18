<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPrice extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    // Eloquent model for property pricing
    protected $table = 'property_pricing';
    protected $guarded = [];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the property that owns the pricing.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Calculate total price based on nights stayed
    public function calculateTotalPrice(int $nights): array
    {
        $result = [
            'total_price' => 0,
            'rate_used' => 'nightly',
            'rate_per_night' => 0,
            'total_nights' => $nights
        ];
        
        // Determine rate based on stay duration (industry standard)
        if ($nights >= $this->min_days_for_monthly && $this->monthly_rate) {
            $result['total_price'] = $nights * $this->monthly_rate;
            $result['rate_used'] = 'monthly';
            $result['rate_per_night'] = $this->monthly_rate;
        } 
        elseif ($nights >= $this->min_days_for_weekly && $this->weekly_rate) {
            $result['total_price'] = $nights * $this->weekly_rate;
            $result['rate_used'] = 'weekly';
            $result['rate_per_night'] = $this->weekly_rate;
        } 
        else {
            $result['total_price'] = $nights * $this->nightly_rate;
            $result['rate_used'] = 'nightly';
            $result['rate_per_night'] = $this->nightly_rate;
        }
        
        return $result;
    }
    private function calculateRates(
        float $nightlyRate, 
        float $weeklyDiscountPercent = 0, 
        float $monthlyDiscountPercent = 0, 
        bool $weeklyDiscountActive = false, 
        bool $monthlyDiscountActive = false, 
    ): array {
        $weeklyRate = null;
        $monthlyRate = null;

        if ($weeklyDiscountActive) {
            $weeklyRate = round($nightlyRate * 7 * (1 - $weeklyDiscountPercent / 100), 2);
        }

        if ($monthlyDiscountActive) {
            $monthlyRate = round($nightlyRate * 30 * (1 - $monthlyDiscountPercent / 100), 2);
        }

        return [
            'weekly' => $weeklyRate,
            'monthly' => $monthlyRate,
        ];
    }

    // Lifecycle hook to ensure rates are calculated before saving
    protected static function booted()
    {
        static::saving(function ($pricing) {
            if ($pricing->isDirty(['nightly_rate', 'weekly_discount_percent', 'monthly_discount_percent', 'weekend_premium_percent', 'weekly_discount_active', 'monthly_discount_active', 'weekend_premium_active'])) {
                $rates = $pricing->calculateRates(
                    $pricing->nightly_rate, 
                    $pricing->weekly_discount_percent ?? 0, 
                    $pricing->monthly_discount_percent ?? 0, 
                    $pricing->weekly_discount_active ?? false, 
                    $pricing->monthly_discount_active ?? false, 
                );
                $pricing->weekly_rate = round($rates['weekly']);
                $pricing->monthly_rate = round($rates['monthly']);
            }
        });
    }
}
