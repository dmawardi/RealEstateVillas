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
        // Update the property_pricing table to add hardcoded rates
        Schema::table('property_pricing', function (Blueprint $table) {
            $table->decimal('weekly_rate', 10, 2)->nullable();
            $table->decimal('monthly_rate', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_pricing', function (Blueprint $table) {
            $table->dropColumn('weekly_rate');
            $table->dropColumn('monthly_rate');
        });
    }
};
