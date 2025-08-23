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
}
