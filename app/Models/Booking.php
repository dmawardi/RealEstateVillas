<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $guarded = [];
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'flexible_dates' => 'boolean',
        'commission_paid' => 'boolean',
    ];

    /**
     * Get the property associated with the booking.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Query scopes for business logic
    // Used by calling model::Confirmed(), model::ForProperty($propertyId), etc.
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeCommissionable($query)
    {
        return $query->where('source', 'direct')
                    ->where('booking_type', 'booking')
                    ->where('status', 'confirmed');
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('check_in_date', [$startDate, $endDate])
              ->orWhereBetween('check_out_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('check_in_date', '<=', $startDate)
                     ->where('check_out_date', '>=', $endDate);
              });
        });
    }

    // Business methods
    public function calculateCommission(): void
    {
        if ($this->source === 'direct' && $this->total_price && $this->commission_rate) {
            $this->commission_amount = ($this->total_price * $this->commission_rate) / 100;
            $this->save();
        }
    }

    public function isCommissionable(): bool
    {
        return $this->source === 'direct' && 
               $this->booking_type === 'booking' && 
               $this->status === 'confirmed';
    }

    public function getNights(): int
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    public function getGuestName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
