<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Property;
use App\Models\PropertyAttachment;
use App\Models\PropertyPrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $property;
    protected $properties;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users for testing
        $this->user = User::factory()->create(['role' => 'user']);
        
        // Create properties for testing
        $this->property = Property::factory()->create([
            'status' => 'active',
            'listing_type' => 'for_rent',
            'property_type' => 'villa',
            'village' => 'Ubud',
            'district' => 'Ubud',
            'regency' => 'Gianyar',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'car_spaces' => 1,
            'land_size' => 500,
            'title' => 'Beautiful Villa in Ubud',
            'slug' => 'beautiful-villa-ubud'
        ]);

        $this->properties = Property::factory()->count(5)->create([
            'status' => 'active'
        ]);

        // Clear cache before each test
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    #[Test]
    public function test_index_returns_properties_listing_page()
    {
        $response = $this->get(route('properties.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('properties/Index')
                ->has('properties')
                ->has('filters')
                ->has('seoData')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_property_type()
    {
        Property::factory()->create([
            'property_type' => 'villa',
            'status' => 'active'
        ]);
        Property::factory()->create([
            'property_type' => 'land',
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', ['property_type' => 'villa']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_listing_type()
    {
        Property::factory()->create([
            'listing_type' => 'for_rent',
            'status' => 'active'
        ]);
        Property::factory()->create([
            'listing_type' => 'for_sale',
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', ['listing_type' => 'for_rent']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_bedrooms()
    {
        Property::factory()->create([
            'bedrooms' => 2,
            'status' => 'active'
        ]);
        Property::factory()->create([
            'bedrooms' => 4,
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', ['bedrooms' => 3]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_bathrooms()
    {
        Property::factory()->create([
            'bathrooms' => 1,
            'status' => 'active'
        ]);
        Property::factory()->create([
            'bathrooms' => 3,
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', ['bathrooms' => 2]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_villages()
    {
        Property::factory()->create([
            'village' => 'Ubud',
            'status' => 'active'
        ]);
        Property::factory()->create([
            'village' => 'Canggu',
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', ['villages' => 'Ubud,Canggu']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_land_size()
    {
        Property::factory()->create([
            'land_size' => 300,
            'status' => 'active'
        ]);
        Property::factory()->create([
            'land_size' => 800,
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', [
            'min_land_size' => 400,
            'max_land_size' => 700
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_filters_properties_by_features()
    {
        $feature1 = Feature::factory()->create(['name' => 'Pool']);
        $feature2 = Feature::factory()->create(['name' => 'Garden']);
        
        $property1 = Property::factory()->create(['status' => 'active']);
        $property2 = Property::factory()->create(['status' => 'active']);
        
        $property1->features()->attach($feature1);
        $property2->features()->attach([$feature1->id, $feature2->id]);

        $response = $this->get(route('properties.index', [
            'features' => $feature1->id
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_only_shows_active_properties()
    {
        Property::factory()->create(['status' => 'pending']);
        Property::factory()->create(['status' => 'withdrawn']);
        Property::factory()->create(['status' => 'active']);

        $response = $this->get(route('properties.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_show_displays_property_details()
    {
        // Create property with attachments and features
        $property = Property::factory()->create([
            'status' => 'active',
            'slug' => 'test-property'
        ]);

        PropertyAttachment::factory()->create([
            'property_id' => $property->id,
            'type' => 'image'
        ]);

        PropertyPrice::factory()->create([
            'property_id' => $property->id
        ]);

        $response = $this->get(route('properties.show', $property));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('properties/Show')
                ->has('property')
                ->has('current_pricing')
                ->has('seoData')
                ->has('businessPhone')
                ->has('businessEmail')
        );
    }

    #[Test]
    public function test_show_increments_view_count()
    {
        $initialViewCount = $this->property->view_count;

        $this->get(route('properties.show', $this->property));

        $this->property->refresh();
        $this->assertEquals($initialViewCount + 1, $this->property->view_count);
    }

    #[Test]
    public function test_show_returns_404_for_nonexistent_property()
    {
        $response = $this->get(route('properties.show', 'nonexistent-slug'));

        $response->assertStatus(404);
    }

    #[Test]
    public function test_show_adds_favorite_status_for_authenticated_user()
    {
        $this->actingAs($this->user)
            ->get(route('properties.show', $this->property))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->has('property.is_favorited')
            );
    }

    #[Test]
    public function test_show_caches_property_data()
    {
        $cacheKey = 'property_detail_' . $this->property->id;
        
        $this->assertNull(Cache::get($cacheKey));

        $this->get(route('properties.show', $this->property));

        $this->assertNotNull(Cache::get($cacheKey));
    }

    #[Test]
    public function test_favorites_requires_authentication()
    {
        $response = $this->get(route('my.favorites'));

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_favorites_displays_user_favorite_properties()
    {
        $favoriteProperty = Property::factory()->create(['status' => 'active']);
        
        $this->user->favorites()->attach($favoriteProperty);

        $response = $this->actingAs($this->user)
            ->get(route('my.favorites'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('favorites/Index')
                ->has('favorites')
                ->has('filters')
                ->has('seoData')
        );
    }

    #[Test]
    public function test_favorites_filters_by_search_term()
    {
        $favoriteProperty = Property::factory()->create([
            'status' => 'active',
            'title' => 'Beautiful Villa'
        ]);
        
        $this->user->favorites()->attach($favoriteProperty);

        $response = $this->actingAs($this->user)
            ->get(route('my.favorites', ['search' => 'Beautiful']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('favorites.data')
        );
    }

    #[Test]
    public function test_favorites_filters_by_property_type()
    {
        $favoriteProperty = Property::factory()->create([
            'status' => 'active',
            'property_type' => 'villa'
        ]);
        
        $this->user->favorites()->attach($favoriteProperty);

        $response = $this->actingAs($this->user)
            ->get(route('my.favorites', ['property_type' => 'villa']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('favorites.data')
        );
    }

    #[Test]
    public function test_favorites_sorts_by_newest_by_default()
    {
        $olderFavorite = Property::factory()->create(['status' => 'active']);
        $newerFavorite = Property::factory()->create(['status' => 'active']);
        
        $this->user->favorites()->attach($olderFavorite, ['created_at' => now()->subDays(2)]);
        $this->user->favorites()->attach($newerFavorite, ['created_at' => now()->subDay()]);

        $response = $this->actingAs($this->user)
            ->get(route('my.favorites'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('favorites.data')
        );
    }

    #[Test]
    public function test_toggle_favorite_requires_authentication()
    {
        $response = $this->post(route('properties.toggle-favorite', $this->property));

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_toggle_favorite_adds_property_to_favorites()
    {
        $this->assertFalse($this->user->hasFavorited($this->property));

        $response = $this->actingAs($this->user)
            ->post(route('properties.toggle-favorite', $this->property));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Property added to favorites.');
        
        $this->assertTrue($this->user->hasFavorited($this->property));
    }

    #[Test]
    public function test_toggle_favorite_removes_property_from_favorites()
    {
        // Add to favorites first
        $this->user->favorites()->attach($this->property);

        $this->assertTrue($this->user->hasFavorited($this->property));

        $response = $this->actingAs($this->user)
            ->post(route('properties.toggle-favorite', $this->property));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Property removed from favorites.');
        
        $this->assertFalse($this->user->hasFavorited($this->property));
    }

    #[Test]
    public function test_get_availability_for_specific_dates_returns_availability_status()
    {
        $checkIn = now()->addDays(7);
        $checkOut = now()->addDays(10);

        $response = $this->get("/api/properties/{$this->property->id}/availability?" . http_build_query([
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'available',
            'check_in_date',
            'check_out_date'
        ]);
    }

    #[Test]
    public function test_get_availability_for_calendar_returns_unavailable_periods()
    {
        $startDate = now();
        $endDate = now()->addMonth();

        $response = $this->get("/api/properties/{$this->property->id}/availability?" . http_build_query([
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'period_start',
            'period_end',
            'unavailable_periods'
        ]);
    }

    #[Test]
    public function test_get_availability_validates_required_fields()
    {
        $response = $this->getJson("/api/properties/{$this->property->id}/availability");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start', 'end']);
    }

    #[Test]
    public function test_calculate_price_returns_pricing_details()
    {
        // Create pricing for the property
        PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'nightly_rate' => 1000000,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth()
        ]);

        $checkIn = now()->addDays(7);
        $checkOut = now()->addDays(10);

        $response = $this->get("/api/properties/{$this->property->id}/price?" . http_build_query([
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_price',
            'original_price',
            'savings',
            'discount_percentage',
            'nights',
            'rate_used',
            'rate_per_night',
            'original_rate_per_night',
            'currency',
            'check_in_date',
            'check_out_date'
        ]);
    }

    #[Test]
    public function test_calculate_price_validates_required_fields()
    {
        $response = $this->getJson("/api/properties/{$this->property->id}/price");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['check_in_date', 'check_out_date']);
    }

    #[Test]
    public function test_calculate_price_validates_date_constraints()
    {
        $response = $this->getJson("/api/properties/{$this->property->id}/price?" . http_build_query([
            'check_in_date' => now()->subDay()->format('Y-m-d'),
            'check_out_date' => now()->format('Y-m-d')
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['check_in_date']);
    }

    #[Test]
    public function test_calculate_price_returns_error_for_invalid_checkout_date()
    {
        $checkIn = now()->addDays(5);
        $checkOut = now()->addDays(3); // Before check-in

        $response = $this->getJson("/api/properties/{$this->property->id}/price?" . http_build_query([
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d')
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['check_out_date']);
    }

    #[Test]
    public function test_get_all_locations_returns_cached_location_data()
    {
        // Create properties with different locations
        Property::factory()->create([
            'village' => 'Ubud',
            'district' => 'Ubud',
            'regency' => 'Gianyar',
            'status' => 'active'
        ]);
        Property::factory()->create([
            'village' => 'Canggu',
            'district' => 'Kuta Utara',
            'regency' => 'Badung',
            'status' => 'active'
        ]);

        $response = $this->get('/api/properties/locations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'villages',
            'districts',
            'regencies'
        ]);
    }

    #[Test]
    public function test_get_all_locations_caches_data()
    {
        $cacheKey = 'property_locations';
        
        $this->assertNull(Cache::get($cacheKey));

        $this->get('/api/properties/locations');

        $this->assertNotNull(Cache::get($cacheKey));
    }

    #[Test]
    public function test_api_endpoints_return_404_for_nonexistent_property()
    {
        $nonExistentId = 99999;

        // Test availability endpoint
        $response = $this->get("/api/properties/{$nonExistentId}/availability?start=2024-01-01&end=2024-01-31");
        $response->assertStatus(404);

        // Test price calculation endpoint
        $response = $this->get("/api/properties/{$nonExistentId}/price?check_in_date=" . now()->addDays(1)->format('Y-m-d') . "&check_out_date=" . now()->addDays(3)->format('Y-m-d'));
        $response->assertStatus(404);
    }

    #[Test]
    public function test_index_handles_complex_location_filters()
    {
        Property::factory()->create([
            'village' => 'Ubud',
            'district' => 'Ubud',
            'regency' => 'Gianyar',
            'status' => 'active'
        ]);
        Property::factory()->create([
            'village' => 'Canggu',
            'district' => 'Kuta Utara',
            'regency' => 'Badung',
            'status' => 'active'
        ]);

        $response = $this->get(route('properties.index', [
            'villages' => 'Ubud',
            'districts' => 'Kuta Utara',
            'regencies' => 'Badung'
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data')
        );
    }

    #[Test]
    public function test_index_preserves_query_string_in_pagination()
    {
        // Create many properties to trigger pagination
        Property::factory()->count(15)->create(['status' => 'active']);

        $response = $this->get(route('properties.index', [
            'property_type' => 'villa',
            'page' => 2
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.links')
        );
    }

    #[Test]
    public function test_show_generates_proper_seo_data()
    {
        $property = Property::factory()->create([
            'title' => 'Luxury Villa in Ubud',
            'district' => 'Ubud',
            'regency' => 'Gianyar',
            'property_type' => 'villa',
            'listing_type' => 'for_rent',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'slug' => 'luxury-villa-ubud'
        ]);

        $response = $this->get(route('properties.show', $property));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('seoData')
                ->has('seoData.title')
                ->has('seoData.description')
                ->has('seoData.keywords')
                ->has('seoData.canonicalUrl')
                ->has('seoData.ogImage')
        );
    }
}
