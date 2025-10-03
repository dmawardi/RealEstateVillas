<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAttachment extends Model
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
     * Get the property that owns the attachment.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_visible_to_customer' => 'boolean',
        ];
    }
}
