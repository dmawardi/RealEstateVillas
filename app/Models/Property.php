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
     * The user that manages the property.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
            'images' => 'json', // Add this cast
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
