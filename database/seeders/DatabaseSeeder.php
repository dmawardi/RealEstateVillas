<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Property;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Make 20 features
        Feature::factory(20)->create();

        // Make 50 properties
       Property::factory(50)
            ->withFeatures() // This will attach 1-5 random features to each property
            ->create();
    }
}
