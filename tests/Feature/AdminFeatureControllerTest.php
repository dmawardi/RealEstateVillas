<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}