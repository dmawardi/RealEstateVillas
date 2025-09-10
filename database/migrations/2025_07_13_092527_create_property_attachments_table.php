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
        Schema::create('property_attachments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
             // Foreign key to properties table
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            
            $table->string('title'); // Title or name of the image
            $table->string('path'); // Path to the stored image
            $table->string('original_filename')->nullable(); // Original filename
            $table->string('file_type'); // File MIME type
            $table->integer('file_size'); // File size in bytes
            $table->string('type')->default('image'); // Options: user_upload, admin_upload, internal,
            $table->string('caption')->nullable(); // Optional caption/description
            $table->boolean('is_visible_to_customer')->default(true); // Whether customer can see it
            $table->boolean('is_active')->default(true); // Soft delete flag
            $table->integer('order')->default(0); // Order of the attachment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_attachments');
    }
};
