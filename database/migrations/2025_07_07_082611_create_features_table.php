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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name'); // Unique name for the feature eg. "Swimming Pool", "Air Conditioning"
            $table->string('slug')->unique(); // e.g., "ensuite", "air-conditioning", "swimming-pool"
            $table->text('description')->nullable(); // Optional description of the feature
            $table->string('category'); // e.g., "interior", "exterior", "security", "accessibility"
            $table->string('icon')->nullable(); // Optional icon for the feature, can be a font-awesome class or similar
            $table->boolean('is_quantifiable')->default(false); // can this feature have a quantity/count?
            $table->boolean('is_active')->default(true); // Whether the feature is currently active or not

            // Indexes for performance
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
