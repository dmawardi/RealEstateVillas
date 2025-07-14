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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Basic Property Information
            $table->string('title');
            $table->text('description');
            $table->enum('property_type', ['house', 'apartment', 'townhouse', 'villa', 'land', 'commercial', 'guest_house', 'other'])->default('house');
            $table->enum('listing_type', ['for_sale', 'for_rent', 'sold', 'off_market']);
            $table->enum('status', ['active', 'pending', 'sold', 'withdrawn'])->default('active');
            
            // Pricing
            $table->integer('price')->nullable(); // in rupiah
            $table->enum('price_type', ['fixed', 'negotiable', 'auction', 'poa'])->default('fixed');
            $table->integer('rental_price_weekly')->nullable();
            $table->integer('rental_price_monthly')->nullable();
            
            // Address Information - Bali specific
            $table->string('street_number')->nullable();
            $table->string('street_name');
            $table->string('village')->nullable(); // Desa/Kelurahan
            $table->string('district'); // Kecamatan
            $table->string('regency'); // Kabupaten/Kota
            $table->string('state')->default('Bali'); // Provinsi
            $table->string('postcode');
            $table->string('country')->default('Indonesia');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Property Specifications
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('car_spaces')->nullable();
            $table->decimal('land_size', 10, 2)->nullable(); // in square meters (are)
            $table->decimal('floor_area', 10, 2)->nullable(); // in square meters
            $table->year('year_built')->nullable();
            
            // Amenities
            $table->json('amenities')->nullable(); // nearby schools, transport, etc.
            // Example structure for amenities JSON:
            // {
            //   "schools_nearby": ["Melbourne Grammar (0.8km)", "Richmond Primary (0.3km)"],
            //   "transport": ["Richmond Station (0.5km)", "Tram Stop (0.2km)"],
            //   "shopping": ["Chadstone SC (3.2km)", "Chapel Street (1.1km)"],
            //   "parks": ["Royal Botanic Gardens (2.1km)", "Yarra Park (1.2km)"],
            //   "medical": ["Epworth Hospital (1.5km)"]
            // }

            // Property Details
            $table->string('zoning')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->date('available_date')->nullable();
            $table->text('inspection_times')->nullable();
            
            // Media
            $table->string('floor_plan')->nullable(); // path to floor plan
            $table->string('virtual_tour_url')->nullable();
            $table->string('video_url')->nullable();
            
            // Agent Information
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // listing agent
            $table->string('agent_name');
            $table->string('agent_phone')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('agency_name')->nullable();
            
            // Listing Management
            $table->string('property_id')->unique(); // custom property reference
            $table->timestamp('listed_at')->nullable();
            $table->integer('days_on_market')->default(0);
            $table->integer('view_count')->default(0);
                        
            // Indexes for better performance
            $table->index(['property_type', 'listing_type']);
            $table->index(['district', 'regency']);
            $table->index(['price', 'bedrooms', 'bathrooms']);
            $table->index(['latitude', 'longitude']);
            $table->index('listed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
