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
}
