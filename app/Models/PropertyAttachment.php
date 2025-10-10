<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
     * Get the full URL for the attachment
     */
    public function getUrlAttribute(): string
    {
        // For public files, generate the URL using Storage facade
        return Storage::url($this->path);
    }

    /**
     * Get a temporary signed URL (for private files)
     */
    public function getSignedUrl($expiration = '+1 hour'): string
    {
        return Storage::temporaryUrl(
            $this->path, 
            now()->modify($expiration)
        );
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
