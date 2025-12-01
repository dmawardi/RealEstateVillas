<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Property;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Creating features...');
        // Make 20 features
        Feature::factory(20)->create();

        $this->command->info('Creating users...');
        // Create some users first (if you don't have them already)
        User::factory(25)->create();

        $this->command->info('Creating properties...');
        // Make 50 properties
        $properties = Property::factory(50)
            ->withFeatures() // This will attach 1-5 random features to each property
            ->withPricing() // This will create pricing for each property
            ->withAttachments(10) // This will create attachments for each property
            ->withBookings(3) // This will create bookings for each property
            ->create();

        $this->command->info('Creating favorites...');
        // Add favorites after all users and properties exist
        $this->seedFavorites();
    }

    /**
     * Seed favorites for users and properties.
     */
    private function seedFavorites(): void
    {
        $users = User::where('role', 'user')->get(); // Only regular users
        $properties = Property::where('status', 'active')->get(); // Only active properties

        // Check if there are users and properties to favorite
        if ($users->isEmpty()) {
            $this->command->warn('No regular users found. Skipping favorite seeding.');
            return;
        }
        if ($properties->isEmpty()) {
            $this->command->warn('No active properties found. Skipping favorite seeding.');
            return;
        }

        // Each user favorites 2-8 random properties
        foreach ($users as $user) {
            $favoriteCount = rand(2, 8);
            $randomProperties = $properties->random(min($favoriteCount, $properties->count()));

            foreach ($randomProperties as $property) {
                $user->favorite($property);
            }

            $this->command->info("Created {$randomProperties->count()} favorites for user: {$user->name}");
        }

        // Make some properties extra popular (favorited by many users)
        $popularProperties = $properties->random(min(5, $properties->count()));
        foreach ($popularProperties as $property) {
            $additionalUsers = $users->random(rand(8, 15));
            foreach ($additionalUsers as $user) {
                $user->favorite($property);
            }
            
            $totalFavorites = $property->favoritedBy()->count();
            $this->command->info("Made property '{$property->title}' popular with {$totalFavorites} total favorites");
        }

        $totalFavorites = DB::table('favorites')->count();
        $this->command->info("✅ Created {$totalFavorites} total favorites!");
    }
}
