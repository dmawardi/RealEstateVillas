<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminFeatureControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create a regular user
        $this->user = User::factory()->create([
            'role' => 'user'
        ]);
    }

    #[Test]
    public function test_index_displays_features_list()
    {
        // Arrange
        $features = Feature::factory()->count(3)->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.features.index'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/features/Index')
                ->has('features.data', 3)
        );
    }

    #[Test]
    public function test_index_filters_by_search_term()
    {
        // Arrange
        $feature1 = Feature::factory()->create([
            'name' => 'Swimming Pool',
            'description' => 'Large outdoor pool',
            'slug' => 'swimming-pool'
        ]);
        
        $feature2 = Feature::factory()->create([
            'name' => 'Air Conditioning',
            'description' => 'Central air system',
            'slug' => 'air-conditioning'
        ]);
        
        // Act - search by name
        $response = $this->actingAs($this->admin)
            ->get(route('admin.features.index', ['search' => 'Swimming']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('features.data', 1)
        );
    }

    #[Test]
    public function test_index_filters_by_category()
    {
        // Arrange
        Feature::factory()->create(['category' => 'interior']);
        Feature::factory()->create(['category' => 'interior']);
        Feature::factory()->create(['category' => 'exterior']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.features.index', ['category' => 'interior']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('features.data', 2)
        );
    }

    #[Test]
    public function test_create_displays_feature_creation_form()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.features.create'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/features/Create')
        );
    }

    #[Test]
    public function test_store_creates_feature_successfully()
    {
        // Arrange
        $featureData = [
            'name' => 'Swimming Pool',
            'slug' => 'swimming-pool',
            'category' => 'exterior',
            'description' => 'Large outdoor swimming pool',
            'icon' => 'fas fa-swimming-pool',
            'is_quantifiable' => false,
            'is_active' => true
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.features.store'), $featureData);
        
        // Assert
        $response->assertRedirect(route('admin.features.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('features', [
            'name' => 'Swimming Pool',
            'slug' => 'swimming-pool',
            'category' => 'exterior'
        ]);
    }

    #[Test]
    public function test_store_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.features.store'), []);
        
        // Assert
        $response->assertSessionHasErrors([
            'name',
            'slug',
            'category',
            'is_quantifiable',
            'is_active'
        ]);
    }

    #[Test]
    public function test_store_validates_unique_slug()
    {
        // Arrange
        Feature::factory()->create(['slug' => 'swimming-pool']);
        
        $featureData = [
            'name' => 'Another Pool',
            'slug' => 'swimming-pool', // Duplicate slug
            'category' => 'exterior',
            'is_quantifiable' => false,
            'is_active' => true
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.features.store'), $featureData);
        
        // Assert
        $response->assertSessionHasErrors(['slug']);
    }

    #[Test]
    public function test_edit_displays_feature_edit_form()
    {
        // Arrange
        $feature = Feature::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.features.edit', $feature));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/features/Edit')
                ->where('feature.id', $feature->id)
        );
    }

    #[Test]
    public function test_update_modifies_feature_successfully()
    {
        // Arrange
        $feature = Feature::factory()->create([
            'name' => 'Old Name',
            'category' => 'interior'
        ]);

        $updateData = [
            'name' => 'Updated Feature Name',
            'slug' => $feature->slug, // Keep existing slug
            'category' => 'exterior',
            'description' => 'Updated description',
            'icon' => 'fas fa-updated-icon',
            'is_quantifiable' => true,
            'is_active' => false
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.features.update', $feature), $updateData);
        
        // Assert
        $response->assertRedirect(route('admin.features.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'name' => 'Updated Feature Name',
            'category' => 'exterior',
            'is_quantifiable' => true,
            'is_active' => false
        ]);
    }

    #[Test]
    public function test_update_validates_unique_slug_except_current()
    {
        // Arrange
        $feature1 = Feature::factory()->create(['slug' => 'existing-slug']);
        $feature2 = Feature::factory()->create(['slug' => 'another-slug']);

        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'existing-slug', // Trying to use existing slug from another feature
            'category' => 'interior',
            'is_quantifiable' => false,
            'is_active' => true
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.features.update', $feature2), $updateData);
        
        // Assert
        $response->assertSessionHasErrors(['slug']);
    }

    #[Test]
    public function test_update_allows_keeping_same_slug()
    {
        // Arrange
        $feature = Feature::factory()->create([
            'slug' => 'existing-slug'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'existing-slug', // Same slug as current feature
            'category' => 'interior',
            'is_quantifiable' => false,
            'is_active' => true
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.features.update', $feature), $updateData);
        
        // Assert
        $response->assertRedirect(route('admin.features.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function test_destroy_deletes_feature()
    {
        // Arrange
        $feature = Feature::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.features.destroy', $feature));
        
        // Assert
        $response->assertRedirect(route('admin.features.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('features', [
            'id' => $feature->id
        ]);
    }

    #[Test]
    public function test_unauthorized_user_cannot_access_admin_routes()
    {
        // Act & Assert
        $this->actingAs($this->user) // Regular user, not admin
            ->get(route('admin.features.index'))
            ->assertStatus(302); // Redirect for unauthorized access
    }

    #[Test]
    public function test_store_handles_nullable_fields_correctly()
    {
        // Arrange - minimal required data, nullable fields omitted
        $featureData = [
            'name' => 'Basic Feature',
            'slug' => 'basic-feature',
            'category' => 'interior',
            // description and icon omitted (nullable)
            'is_quantifiable' => false,
            'is_active' => true
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.features.store'), $featureData);
        
        // Assert
        $response->assertRedirect(route('admin.features.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('features', [
            'name' => 'Basic Feature',
            'slug' => 'basic-feature',
            'category' => 'interior',
            'description' => null,
            'icon' => null
        ]);
    }

    #[Test]
    public function test_update_handles_nullable_fields_correctly()
    {
        // Arrange
        $feature = Feature::factory()->create([
            'description' => 'Old description',
            'icon' => 'old-icon'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'slug' => $feature->slug,
            'category' => 'interior',
            // description and icon omitted (nullable)
            'is_quantifiable' => false,
            'is_active' => true
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.features.update', $feature), $updateData);
        
        // Assert
        $response->assertRedirect(route('admin.features.index'));
        $response->assertSessionHas('success');
        // The nullable fields should remain as they were since not provided
        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'name' => 'Updated Name'
        ]);
    }

    #[Test]
    public function test_get_available_features_returns_active_features_grouped_by_category()
    {
        // Arrange - Clear cache first to ensure fresh test
        Cache::forget('available_features_for_filtering');
        
        $feature1 = Feature::factory()->create([
            'name' => 'WiFi',
            'slug' => 'wifi',
            'category' => 'amenities',
            'icon' => 'wifi-icon',
            'is_active' => true
        ]);
        
        $feature2 = Feature::factory()->create([
            'name' => 'Swimming Pool',
            'slug' => 'swimming-pool',
            'category' => 'amenities',
            'icon' => 'pool-icon',
            'is_active' => true
        ]);
        
        $feature3 = Feature::factory()->create([
            'name' => 'Gym',
            'slug' => 'gym',
            'category' => 'facilities',
            'icon' => 'gym-icon',
            'is_active' => true
        ]);
        
        // Create an inactive feature that should not be returned
        Feature::factory()->create([
            'name' => 'Inactive Feature',
            'slug' => 'inactive',
            'category' => 'amenities',
            'is_active' => false
        ]);

        // Act
        $response = $this->actingAs($this->admin)->getJson(route('properties.features'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'amenities' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'category',
                    'icon'
                ]
            ],
            'facilities' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'category',
                    'icon'
                ]
            ]
        ]);

        $responseData = $response->json();
        
        // Verify grouping by category
        $this->assertArrayHasKey('amenities', $responseData);
        $this->assertArrayHasKey('facilities', $responseData);
        
        // Verify amenities category has 2 features
        $this->assertCount(2, $responseData['amenities']);
        
        // Verify facilities category has 1 feature
        $this->assertCount(1, $responseData['facilities']);
        
        // Verify inactive feature is not included
        $allFeatures = collect($responseData)->flatten(1);
        $this->assertFalse($allFeatures->contains('name', 'Inactive Feature'));

        // Verify cache headers
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=21600', $cacheControl);
        $response->assertHeader('X-Cache-Status', 'HIT'); // Always HIT due to controller logic
    }

    #[Test]
    public function test_get_available_features_returns_cached_data_on_subsequent_requests()
    {
        // Arrange - Clear cache first to ensure fresh test
        Cache::forget('available_features_for_filtering');

        $feature = Feature::factory()->create([
            'name' => 'WiFi',
            'category' => 'amenities',
            'is_active' => true
        ]);

        // First request - should populate cache
        $response1 = $this->actingAs($this->admin)->getJson(route('properties.features'));
        $response1->assertStatus(200);
        
        // Verify cache is now populated
        $this->assertTrue(Cache::has('available_features_for_filtering'));
        
        // Store original cached data for comparison
        $cachedData = Cache::get('available_features_for_filtering');
        
        // Second request - should use cache
        $response2 = $this->actingAs($this->admin)->getJson(route('properties.features'));
        $response2->assertStatus(200);
        
        // Verify cache still exists after second request
        $this->assertTrue(Cache::has('available_features_for_filtering'));
        
        // Verify cached data hasn't changed
        $this->assertEquals($cachedData, Cache::get('available_features_for_filtering'));
        
        // Both responses should have the same structure and data
        $this->assertEquals($response1->json(), $response2->json());
    }

    #[Test]
    public function test_get_available_features_handles_empty_results()
    {
        // Act - No features in database
        $response = $this->actingAs($this->admin)->getJson(route('properties.features'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    #[Test]
    public function test_get_available_features_handles_exceptions()
    {
        // Arrange - Mock Log facade to capture error logs
        Log::shouldReceive('error')->once()->with(
            'Failed to fetch available features',
            \Mockery::type('array')
        );

        // Mock Cache::remember to throw an exception
        Cache::shouldReceive('remember')->andThrow(new \Exception('Database connection failed'));

        // Act
        $response = $this->actingAs($this->admin)->getJson(route('properties.features'));

        // Assert
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Failed to load features',
            'message' => 'Please try again later'
        ]);
    }

    #[Test]
    public function test_clear_features_cache_removes_cached_data()
    {
        // Arrange - Create feature and cache it
        Feature::factory()->create(['is_active' => true]);
        $this->actingAs($this->admin)->getJson(route('properties.features')); // This caches the data

        // Verify cache exists
        $this->assertTrue(Cache::has('available_features_for_filtering'));

        // Act - Clear cache using correct DELETE method and route name
        $response = $this->actingAs($this->admin)->deleteJson(route('admin.cache.features.clear'));

        // Assert - Verify the response is successful
        $response->assertStatus(200);
        
        // Assert - Cache should be cleared
        $this->assertFalse(Cache::has('available_features_for_filtering'));
    }

    #[Test]
    public function test_refresh_features_cache_clears_and_rebuilds_cache()
    {
        // Arrange
        Feature::factory()->create([
            'name' => 'WiFi',
            'category' => 'amenities',
            'is_active' => true
        ]);

        // Cache some data first
        $this->actingAs($this->admin)->getJson(route('properties.features'));
        $this->assertTrue(Cache::has('available_features_for_filtering'));

        // Act
        $response = $this->actingAs($this->admin)->postJson(route('admin.cache.features.refresh'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Features cache refreshed successfully'
        ]);

        // Verify cache still exists (was refreshed, not just cleared)
        $this->assertTrue(Cache::has('available_features_for_filtering'));
    }

    #[Test]
    public function test_refresh_features_cache_handles_exceptions()
    {
        // Arrange - Mock Log facade to capture error logs
        Log::shouldReceive('error')->once()->with(
            'Failed to refresh features cache',
            \Mockery::type('array')
        );

        // Mock Cache::forget to throw an exception
        Cache::shouldReceive('forget')->andThrow(new \Exception('Cache service unavailable'));

        // Act
        $response = $this->actingAs($this->admin)->postJson(route('admin.cache.features.refresh'));

        // Assert
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Failed to refresh cache'
        ]);
    }

    #[Test]
    public function test_get_cache_info_returns_cache_details_when_cache_exists()
    {
        // Arrange - Create feature and cache it
        Feature::factory()->create(['is_active' => true]);
        $this->actingAs($this->admin)->getJson(route('properties.features')); // This caches the data

        // Act
        $response = $this->actingAs($this->admin)->getJson(route('admin.cache.features.info'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cache_key',
            'cache_exists',
            'cache_size_bytes',
            'cache_duration_seconds'
        ]);

        $responseData = $response->json();
        $this->assertEquals('available_features_for_filtering', $responseData['cache_key']);
        $this->assertTrue($responseData['cache_exists']);
        $this->assertGreaterThan(0, $responseData['cache_size_bytes']);
        $this->assertEquals(21600, $responseData['cache_duration_seconds']); // 6 hours
    }

    #[Test]
    public function test_get_cache_info_returns_cache_details_when_cache_empty()
    {
        // Act - No cached data
        $response = $this->actingAs($this->admin)->getJson(route('admin.cache.features.info'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cache_key',
            'cache_exists',
            'cache_size_bytes',
            'cache_duration_seconds'
        ]);

        $responseData = $response->json();
        $this->assertEquals('available_features_for_filtering', $responseData['cache_key']);
        $this->assertFalse($responseData['cache_exists']);
        $this->assertEquals(0, $responseData['cache_size_bytes']);
        $this->assertEquals(21600, $responseData['cache_duration_seconds']);
    }

    #[Test]
    public function test_features_are_ordered_by_category_and_name()
    {
        // Arrange - Create features in different orders
        Feature::factory()->create([
            'name' => 'Zebra Feature',
            'category' => 'facilities',
            'is_active' => true
        ]);
        
        Feature::factory()->create([
            'name' => 'Alpha Feature',
            'category' => 'facilities',
            'is_active' => true
        ]);
        
        Feature::factory()->create([
            'name' => 'Beta Feature',
            'category' => 'amenities',
            'is_active' => true
        ]);

        // Act
        $response = $this->actingAs($this->admin)->getJson(route('properties.features'));

        // Assert
        $response->assertStatus(200);
        $responseData = $response->json();

        // Verify categories are ordered (amenities comes before facilities)
        $categories = array_keys($responseData);
        $this->assertEquals(['amenities', 'facilities'], $categories);

        // Verify features within facilities category are ordered by name
        $facilitiesFeatures = $responseData['facilities'];
        $this->assertEquals('Alpha Feature', $facilitiesFeatures[0]['name']);
        $this->assertEquals('Zebra Feature', $facilitiesFeatures[1]['name']);
    }

    #[Test]
    public function test_only_required_fields_are_returned()
    {
        // Arrange
        Feature::factory()->create([
            'name' => 'Test Feature',
            'slug' => 'test-feature',
            'category' => 'amenities',
            'icon' => 'test-icon',
            'description' => 'This description should not be returned',
            'created_at' => now(),
            'updated_at' => now(),
            'is_active' => true
        ]);

        // Act
        $response = $this->actingAs($this->admin)->getJson('/api/admin/properties/available-features');

        // Assert
        $response->assertStatus(200);
        $responseData = $response->json();
        
        $feature = $responseData['amenities'][0];
        
        // Verify only required fields are present
        $this->assertArrayHasKey('id', $feature);
        $this->assertArrayHasKey('name', $feature);
        $this->assertArrayHasKey('slug', $feature);
        $this->assertArrayHasKey('category', $feature);
        $this->assertArrayHasKey('icon', $feature);
        
        // Verify unwanted fields are not present
        $this->assertArrayNotHasKey('description', $feature);
        $this->assertArrayNotHasKey('created_at', $feature);
        $this->assertArrayNotHasKey('updated_at', $feature);
        $this->assertArrayNotHasKey('is_active', $feature);
    }
}