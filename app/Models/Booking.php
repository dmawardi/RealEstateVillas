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

    public function user()
    {
        return $this->belongsTo(User::class);
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

    // Helper methods for Mail
    // Get important changes between two sets of booking data
    public static function getImportantChanges(array $originalData, array $newData): array
    {
        $importantFields = [
            'check_in_date',
            'check_out_date', 
            'status',
            'total_price',
            'number_of_guests',
            'number_of_rooms'
        ];

        $dateFields = ['check_in_date', 'check_out_date'];
        $changes = [];
        
        foreach ($importantFields as $field) {
            if (isset($originalData[$field]) && isset($newData[$field])) {
                // Handle date fields with proper comparison
                if (in_array($field, $dateFields)) {
                    $originalDate = \Carbon\Carbon::parse($originalData[$field])->format('Y-m-d');
                    $newDate = \Carbon\Carbon::parse($newData[$field])->format('Y-m-d');
                    
                    if ($originalDate !== $newDate) {
                        $changes[$field] = [
                            'old' => $originalDate,
                            'new' => $newDate
                        ];
                    }
                } else {
                    // Handle non-date fields with standard comparison
                    if ($originalData[$field] != $newData[$field]) {
                        $changes[$field] = [
                            'old' => $originalData[$field],
                            'new' => $newData[$field]
                        ];
                    }
                }
            }
        }

        return $changes;
    }

    // Determine the type of update based on changes
    public static function determineUpdateType(array $changes): string
    {
        if (isset($changes['status']) && $changes['status']['new'] === 'cancelled') {
            return 'cancellation';
        }
        
        if (isset($changes['status'])) {
            return 'status_change';
        }
        
        if (isset($changes['check_in_date']) || isset($changes['check_out_date'])) {
            return 'date_change';
        }
        
        return 'modification';
    }
}
