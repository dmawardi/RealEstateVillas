<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'role', // Added role attribute
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Methods
    /**
     * Get the properties that the user has favorited.
     */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'favorites')
                    ->withTimestamps()
                    ->orderBy('favorites.created_at', 'desc');
    }

    /**
     * Check if the user has favorited a specific property.
     */
    public function hasFavorited(Property $property): bool
    {
        return $this->favorites()->wherePivot('property_id', $property->id)->exists();
    }

    /**
     * Favorite a property.
     */
    public function favorite(Property $property): void
    {
        if (!$this->hasFavorited($property)) {
            $this->favorites()->attach($property->id);
        }
    }

    /**
     * Unfavorite a property.
     */
    public function unfavorite(Property $property): bool
    {
        return $this->favorites()->detach($property->id) > 0;
    }

    /**
     * Toggle favorite status for a property.
     */
    public function toggleFavorite(Property $property): bool
    {
        if ($this->hasFavorited($property)) {
            $this->unfavorite($property);
            return false; // Unfavorited
        } else {
            $this->favorite($property);
            return true; // Favorited
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
