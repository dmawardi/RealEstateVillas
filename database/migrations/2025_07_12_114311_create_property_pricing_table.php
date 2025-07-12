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
            $table->decimal('nightly_rate', 8, 2)->nullable();
            $table->decimal('weekly_rate', 8, 2)->nullable();
            $table->decimal('monthly_rate', 8, 2)->nullable();
            $table->string('currency', 3)->default('AUD');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Foreign key to properties table
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
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
