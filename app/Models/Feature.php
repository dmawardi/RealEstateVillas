<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
