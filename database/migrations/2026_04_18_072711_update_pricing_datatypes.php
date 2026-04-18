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
        // Convert weekly and monthly rate from decimal to big integer
        Schema::table('property_pricing', function (Blueprint $table) {
            $table->bigInteger('weekly_rate')->nullable()->change();
            $table->bigInteger('monthly_rate')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_pricing', function (Blueprint $table) {
            $table->decimal('weekly_rate', 10, 2)->nullable()->change();
            $table->decimal('monthly_rate', 10, 2)->nullable()->change();
        });
    }
};
