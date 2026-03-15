<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Feature extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', // Primary key, not mass assignable
        'created_at', // Timestamps are managed by Eloquent
        'updated_at',
    ];

    /**
     * The properties that belong to the feature.
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class)
            ->withPivot('quantity', 'notes')
            ->withTimestamps();
    }
    
    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean', // Cast is_active to boolean
        ];
    }

    /**
     * Cache key for available features (should match controller)
     */
    private const FEATURES_CACHE_KEY = 'available_features_for_filtering';

    protected static function booted()
    {
        // Clear cache when a feature is saved or deleted
        static::saved(function ($feature) {
            Cache::forget(self::FEATURES_CACHE_KEY);
        });
        static::deleted(function ($feature) {
            Cache::forget(self::FEATURES_CACHE_KEY);
        });
    }
}
