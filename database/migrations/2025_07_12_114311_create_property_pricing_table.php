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
        Schema::create('property_pricing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('nightly_rate')->nullable();
            $table->bigInteger('weekly_rate')->nullable();
            $table->bigInteger('monthly_rate')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('name')->nullable(); // e.g., "Summer Season", "Holiday Rates"
            $table->decimal('weekly_discount_percent', 5, 2)->default(0); // % discount for weekly stays
            $table->decimal('monthly_discount_percent', 5, 2)->default(0); // % discount for monthly stays
            $table->decimal('weekend_premium_percent', 5, 2)->default(0); // % premium for weekend stays
            
            // Add minimum stay requirements
            $table->integer('min_days_for_weekly')->default(7);
            $table->integer('min_days_for_monthly')->default(30);
            $table->boolean('monthly_discount_active')->default(false); // Whether monthly discount is active
            $table->boolean('weekly_discount_active')->default(false); // Whether weekly discount is active
            $table->boolean('weekend_premium_active')->default(false); // Whether weekend premium
            
            // Foreign key to properties table
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            // Indexes for performance
            $table->index(['property_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_pricing');
    }
};
