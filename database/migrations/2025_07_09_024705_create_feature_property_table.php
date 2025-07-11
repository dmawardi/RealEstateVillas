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
        Schema::create('feature_property', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->integer('quantity')->default(1); // how many of this feature (e.g., 3 ensuites)
            $table->text('notes')->nullable(); // additional details

            // Indexes for performance
            $table->unique(['property_id', 'feature_id']);
            $table->index(['property_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_property');
    }
};
