<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Property relationship
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            
            // Source tracking - key for your business model
            $table->enum('source', ['direct', 'airbnb', 'booking_com', 'agoda', 'owner_blocked', 'maintenance', 'other'])
                  ->default('direct');
            $table->string('external_booking_id')->nullable(); // For tracking external platform bookings
            
            // Guest information
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Booking details
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_guests')->default(1);
            $table->integer('number_of_rooms')->nullable();
            $table->boolean('flexible_dates')->default(false);
            
            // Booking status and type
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'blocked'])
                  ->default('pending');
            $table->enum('booking_type', ['booking', 'inquiry', 'blocked', 'maintenance'])
                  ->default('booking');
            
            // Financial tracking
            $table->integer('total_price')->default(0);
            $table->decimal('commission_rate', 5, 2)->nullable(); // Your commission percentage
            $table->integer('commission_amount')->nullable();
            $table->boolean('commission_paid')->default(false);
            
            // Additional information
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable(); // Internal notes

            // Indexes for performance
            $table->index(['property_id', 'check_in_date', 'check_out_date']);
            $table->index(['source', 'status']);
            $table->index('external_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
