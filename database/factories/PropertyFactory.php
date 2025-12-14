<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propertyTypes = ['house', 'apartment', 'townhouse', 'villa', 'land', 'commercial', 'guest_house', 'other'];
        $listingTypes = ['for_sale', 'for_rent'];
        $statuses = ['active', 'sold'];
        $priceTypes = ['fixed', 'negotiable', 'auction', 'poa'];
        
        $propertyType = $this->faker->randomElement($propertyTypes);
        $listingType = $this->faker->randomElement($listingTypes);
        
        // Generate realistic Bali locations
        $locationData = $this->getBaliLocation();
        
        // Generate price based on property type and listing type
        $price = null;
        
        if ($listingType === 'for_sale' || $listingType === 'sold') {
            // Bali property prices - adjusted for local market
            $price = $this->faker->numberBetween(1000000000, 25000000000); // 1B - 25B IDR
        }
        
        // Generate bedrooms/bathrooms based on property type
        $bedrooms = $propertyType === 'land' ? null : $this->faker->numberBetween(1, 6);
        $bathrooms = $propertyType === 'land' ? null : $this->faker->numberBetween(1, $bedrooms + 1);
        
        $listedAt = $this->faker->dateTimeBetween('-2 years', 'now');
        $daysOnMarket = \Carbon\Carbon::parse($listedAt)->diffInDays(now(), false);
        $daysOnMarket = max(0, $daysOnMarket);
        $title = $this->generatePropertyTitle($propertyType, $locationData, $bedrooms);
        
        return [
            // Basic Property Information
            'title' => $title,
            'description' => $this->faker->paragraphs(3, true),
            'slug' => $this->faker->unique()->slug(8),
            'property_type' => $propertyType,
            'listing_type' => $listingType,
            'status' => $this->faker->randomElement($statuses),
            
            // Pricing
            'price' => $price,
            'price_type' => $this->faker->randomElement($priceTypes),
            
            // Address Information - Bali specific
            'street_number' => $this->faker->buildingNumber(),
            'street_name' => $this->faker->streetName(),
            'village' => $locationData['village'],
            'district' => $locationData['district'],
            'regency' => $locationData['regency'],
            'state' => 'Bali',
            'postcode' => $locationData['postcode'],
            'country' => 'Indonesia',
            'latitude' => $this->faker->latitude(-8.9, -8.1), // Bali's latitude range
            'longitude' => $this->faker->longitude(114.4, 115.7), // Bali's longitude range
            
            // Property Specifications
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'car_spaces' => $this->faker->numberBetween(0, 4),
            'land_size' => $this->faker->numberBetween(100, 5000), // Larger plots common in Bali
            'floor_area' => $propertyType === 'land' ? null : $this->faker->numberBetween(50, 800),
            'year_built' => $this->faker->year('now'),
            
            // Amenities
            'amenities' => $this->generateBaliAmenities(),
            
            // Property Details
            'zoning' => $this->faker->randomElement(['residential', 'commercial', 'mixed_use', 'tourism']),
            'is_featured' => $this->faker->boolean(20),
            'is_premium' => $this->faker->boolean(10),
            'available_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+6 months'),
            'inspection_times' => $this->faker->optional(0.8)->sentence(),
            
            // Media
            'floor_plan' => $this->faker->optional(0.6)->filePath(),
            'virtual_tour_url' => $this->faker->optional(0.3)->url(),
            'video_url' => $this->faker->optional(0.2)->url(),
            
            // Agent Information
            'agent_name' => $this->faker->name(),
            'agent_phone' => $this->faker->phoneNumber(),
            'agent_email' => $this->faker->email(),
            'agency_name' => $this->faker->company() . ' Bali Real Estate',
            
            // Listing Management
            'property_id' => 'BALI-' . $this->faker->unique()->numerify('######'),
            'listed_at' => $listedAt,
            'days_on_market' => $daysOnMarket,
            'view_count' => $this->faker->numberBetween(0, 1000),
        ];
    }


    /**
     * Generate realistic property titles based on type and location
     */
    private function generatePropertyTitle(string $propertyType, array $locationData, ?int $bedrooms): string
    {
        $titles = [];
        
        switch ($propertyType) {
            case 'villa':
                $titles = [
                    "Luxury {$bedrooms}-Bedroom Villa in {$locationData['village']}",
                    "Stunning Modern Villa with Pool - {$locationData['district']}",
                    "Traditional Balinese Villa in {$locationData['village']}",
                    "Private Villa with Rice Field Views - {$locationData['district']}",
                    "Tropical Villa Paradise in {$locationData['village']}",
                    "Contemporary {$bedrooms}BR Villa - {$locationData['district']}",
                    "Villa with Private Pool in {$locationData['village']}",
                    "Authentic Bali Villa Experience - {$locationData['district']}",
                ];
                break;
                
            case 'house':
                $titles = [
                    "Beautiful {$bedrooms}-Bedroom House in {$locationData['village']}",
                    "Modern Family Home - {$locationData['district']}",
                    "Charming Traditional House in {$locationData['village']}",
                    "Spacious {$bedrooms}BR House with Garden",
                    "Cozy Family Home in {$locationData['district']}",
                    "Renovated House in Prime {$locationData['village']} Location",
                    "Two-Story House with Mountain Views",
                    "Perfect Starter Home in {$locationData['district']}",
                ];
                break;
                
            case 'apartment':
                $titles = [
                    "Modern {$bedrooms}-Bedroom Apartment in {$locationData['village']}",
                    "Luxury Penthouse - {$locationData['district']}",
                    "Furnished Studio Apartment in {$locationData['village']}",
                    "Ocean View {$bedrooms}BR Apartment",
                    "Contemporary Apartment with Balcony",
                    "High-Rise Living in {$locationData['district']}",
                    "Serviced Apartment in {$locationData['village']}",
                    "Executive Apartment with Pool Access",
                ];
                break;
                
            case 'townhouse':
                $titles = [
                    "Stylish {$bedrooms}-Bedroom Townhouse in {$locationData['village']}",
                    "Modern Townhouse Complex - {$locationData['district']}",
                    "Gated Community Townhouse",
                    "Family-Friendly Townhouse with Yard",
                    "Contemporary {$bedrooms}BR Townhouse",
                    "Secure Townhouse in {$locationData['village']}",
                    "Two-Level Townhouse - {$locationData['district']}",
                    "Townhouse with Private Parking",
                ];
                break;
                
            case 'land':
                $titles = [
                    "Prime Development Land in {$locationData['village']}",
                    "Freehold Land with Rice Field Views",
                    "Investment Land Opportunity - {$locationData['district']}",
                    "Beachfront Land for Sale in {$locationData['village']}",
                    "Mountain View Land Plot",
                    "Commercial Land in {$locationData['district']}",
                    "Residential Building Plot in {$locationData['village']}",
                    "Strategic Location Land Investment",
                ];
                break;
                
            case 'commercial':
                $titles = [
                    "Prime Commercial Space in {$locationData['village']}",
                    "Restaurant/Cafe Opportunity - {$locationData['district']}",
                    "Retail Shop in Busy {$locationData['village']} Area",
                    "Office Space for Lease",
                    "Commercial Building - {$locationData['district']}",
                    "Shop House in {$locationData['village']}",
                    "Business Premises with High Foot Traffic",
                    "Commercial Investment Property",
                ];
                break;
                
            case 'guest_house':
                $titles = [
                    "Boutique Guest House in {$locationData['village']}",
                    "Profitable Guest House Business",
                    "Homestay Property - {$locationData['district']}",
                    "Tourist Accommodation in {$locationData['village']}",
                    "Established Guest House with Pool",
                    "Backpacker Hostel Opportunity",
                    "Family-Run Guest House - {$locationData['district']}",
                    "Guest House with Restaurant",
                ];
                break;
                
            default: // 'other'
                $titles = [
                    "Unique Property in {$locationData['village']}",
                    "Special Purpose Building - {$locationData['district']}",
                    "Mixed-Use Property Opportunity",
                    "Warehouse/Storage Facility",
                    "Development Project in {$locationData['village']}",
                    "Investment Property - {$locationData['district']}",
                    "Multi-Purpose Building",
                    "Property with Potential in {$locationData['village']}",
                ];
                break;
        }
        
        return $this->faker->randomElement($titles);
    }

    /**
     * Generate realistic Bali-specific amenities JSON
     */
    private function generateBaliAmenities(): array
    {
        $schools = [
            'SD Negeri 1 Sanur (0.5km)',
            'SMP Negeri 4 Denpasar (0.8km)', 
            'SMA Negeri 1 Denpasar (1.2km)',
            'Green School Bali (3.5km)',
            'Canggu Community School (2.1km)',
        ];
        
        $transport = [
            'Ngurah Rai Airport (5.2km)',
            'Trans Sarbagita Bus Stop (0.8km)',
            'Ojek/Grab Point (0.1km)',
            'Sanur Harbor (2.3km)',
        ];
        
        $shopping = [
            'Sanur Plaza (1.2km)',
            'Pasar Sindhu (0.8km)',
            'Bintang Supermarket (0.5km)',
            'Hardy\'s Supermarket (1.1km)',
            'Seminyak Square (3.2km)',
        ];
        
        $medical = [
            'BIMC Hospital Kuta (4.2km)',
            'Sanglah Hospital (2.8km)',
            'Kimia Farma Pharmacy (0.4km)',
            'Guardian Pharmacy (0.6km)',
        ];
        
        $recreation = [
            'Sanur Beach (0.3km)',
            'Bali Beach Golf Course (2.1km)',
            'Waterbom Bali (4.5km)',
            'Bali Safari (15.2km)',
            'Pura Besakih (25.8km)',
        ];
        
        $dining = [
            'Warung Local (0.2km)',
            'Seminyak Restaurant Strip (3.1km)',
            'Jimbaran Seafood (8.5km)',
            'Ubud Organic Cafes (12.3km)',
        ];
        
        return [
            'schools_nearby' => $this->faker->randomElements($schools, $this->faker->numberBetween(1, 3)),
            'transport' => $this->faker->randomElements($transport, $this->faker->numberBetween(1, 3)),
            'shopping' => $this->faker->randomElements($shopping, $this->faker->numberBetween(1, 3)),
            'medical' => $this->faker->randomElements($medical, $this->faker->numberBetween(1, 2)),
            'recreation' => $this->faker->randomElements($recreation, $this->faker->numberBetween(1, 3)),
            'dining' => $this->faker->randomElements($dining, $this->faker->numberBetween(0, 2)),
        ];
    }

    /**
     * Get realistic Bali location data
     */
    private function getBaliLocation(): array
    {
        $locations = [
            // Denpasar
            ['village' => 'Sanur Kaja', 'district' => 'Denpasar Selatan', 'regency' => 'Denpasar', 'postcode' => '80228'],
            ['village' => 'Renon', 'district' => 'Denpasar Selatan', 'regency' => 'Denpasar', 'postcode' => '80235'],
            ['village' => 'Kesiman Kertalangu', 'district' => 'Denpasar Timur', 'regency' => 'Denpasar', 'postcode' => '80237'],
            
            // Badung (Seminyak, Canggu, Uluwatu)
            ['village' => 'Seminyak', 'district' => 'Kuta', 'regency' => 'Badung', 'postcode' => '80361'],
            ['village' => 'Canggu', 'district' => 'Kuta Utara', 'regency' => 'Badung', 'postcode' => '80351'],
            ['village' => 'Pecatu', 'district' => 'Kuta Selatan', 'regency' => 'Badung', 'postcode' => '80364'],
            ['village' => 'Jimbaran', 'district' => 'Kuta Selatan', 'regency' => 'Badung', 'postcode' => '80361'],
            ['village' => 'Ungasan', 'district' => 'Kuta Selatan', 'regency' => 'Badung', 'postcode' => '80364'],
            
            // Gianyar (Ubud)
            ['village' => 'Ubud', 'district' => 'Ubud', 'regency' => 'Gianyar', 'postcode' => '80571'],
            ['village' => 'Mas', 'district' => 'Ubud', 'regency' => 'Gianyar', 'postcode' => '80571'],
            ['village' => 'Tegallalang', 'district' => 'Tegallalang', 'regency' => 'Gianyar', 'postcode' => '80561'],
            
            // Tabanan (Tanah Lot area)
            ['village' => 'Beraban', 'district' => 'Kediri', 'regency' => 'Tabanan', 'postcode' => '82121'],
            ['village' => 'Canggu', 'district' => 'Kediri', 'regency' => 'Tabanan', 'postcode' => '82171'],
        ];
        
        return $this->faker->randomElement($locations);
    }


    // Methods to change the state of the property
    // 
    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the property is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    /**
     * Set the user for the property.
     *
     * @param  \App\Models\User  $user
     * @return static
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Attach random features to the property with quantities and notes.
     *
     * @param  int  $count
     * @return static
     */
    public function withFeatures(?int $count = null): static
    {
        return $this->afterCreating(function ($property) use ($count) {
            // Get random features from existing features
            $featureCount = $count ?? rand(1, 5);
            $features = Feature::inRandomOrder()->limit($featureCount)->get();
            
            // Attach each feature with random quantity and optional notes
            $features->each(function ($feature) use ($property) {
                $property->features()->attach($feature->id, [
                    'quantity' => rand(1, 3),
                    'notes' => fake()->optional(0.3)->sentence(),
                ]);
            });
        });
    }
    /**
     * Attach a random property price.
     *
     * @return static
     */
    public function withPricing(): static
    {
        return $this->afterCreating(function ($property) {
            $property->pricing()->save(\App\Models\PropertyPrice::factory()->make([
                'property_id' => $property->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ]));
        });
    }

    /**
     * Attach image property attachments.
     *
     * @param  int  $count
     * @return static
     */
    public function withAttachments(?int $count = null): static
    {
        return $this->afterCreating(function ($property) use ($count) {
            $attachmentCount = $count ?? rand(1, 5);
            \App\Models\PropertyAttachment::factory($attachmentCount)->create([
                'property_id' => $property->id,
            ]);
        });
    }

    /**
     * Attach a random number of bookings.
     */
    public function withBookings(?int $count = null): static
    {
        return $this->afterCreating(function ($property) use ($count) {
            $bookingCount = $count ?? rand(1, 3);
            \App\Models\Booking::factory($bookingCount)->create([
                'property_id' => $property->id,
                'total_price' => $this->faker->numberBetween(10000000, 50000000000), // 10M - 50B IDR
            ]);
        });
    }
}
