<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Property;
use App\Models\PropertyAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminPropertyControllerTest extends TestCase
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

        // Set up fake storage for file upload tests
        Storage::fake('public');
    }

    #[Test]
    public function test_index_displays_properties_list()
    {
        // Arrange
        $properties = Property::factory()->count(3)->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/properties/Index')
                ->has('properties.data', 3)
        );
    }

    #[Test]
    public function test_index_filters_by_search_term()
    {
        // Arrange
        $property1 = Property::factory()->create([
            'title' => 'Beautiful Villa in Canggu',
            'property_id' => 'PROP-2025-ABC123'
        ]);
        
        $property2 = Property::factory()->create([
            'title' => 'Modern Apartment in Seminyak',
            'property_id' => 'PROP-2025-DEF456',
            'property_type' => 'apartment'
        ]);
        
        // Act - search by title
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['search' => 'Villa']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('admin/properties/Index')
            ->has('properties.data', 1)
        );
    }

    #[Test]
    public function test_index_filters_by_property_type()
    {
        // Arrange
        Property::factory()->create(['property_type' => 'villa']);
        Property::factory()->create(['property_type' => 'villa']);
        Property::factory()->create(['property_type' => 'apartment']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['property_type' => 'villa']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data', 2)
        );
    }

    #[Test]
    public function test_index_filters_by_listing_type()
    {
        // Arrange
        Property::factory()->create(['listing_type' => 'for_sale']);
        Property::factory()->create(['listing_type' => 'for_rent']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['listing_type' => 'for_sale']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data', 1)
        );
    }

    #[Test]
    public function test_index_filters_by_status()
    {
        // Arrange
        Property::factory()->create(['status' => 'active']);
        Property::factory()->create(['status' => 'pending']);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['status' => 'active']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data', 1)
        );
    }

    #[Test]
    public function test_index_filters_by_bedrooms()
    {
        // Arrange
        Property::factory()->create(['bedrooms' => 2]);
        Property::factory()->create(['bedrooms' => 4]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['bedrooms' => 3]));
        
        // Assert - should show properties with 3 or more bedrooms
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('properties.data', 1)
        );
    }

    #[Test]
    public function test_create_displays_property_creation_form()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.create'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/properties/Create')
                ->has('propertyTypes')
                ->has('listingTypes')
                ->has('priceTypes')
                ->has('statusOptions')
        );
    }

    #[Test]
    public function test_store_creates_property_successfully()
    {
        // Arrange
        $propertyData = [
            'title' => 'Beautiful Villa',
            'slug' => 'beautiful-villa',
            'description' => 'A stunning villa with ocean views',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price' => 1000000,
            'price_type' => 'fixed',
            'street_name' => 'Jalan Pantai',
            'district' => 'Canggu',
            'regency' => 'Badung',
            'postcode' => '80361',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'land_size' => 500,
            'agent_name' => 'John Doe'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Villa',
            'slug' => 'beautiful-villa',
            'property_type' => 'villa'
        ]);
    }

    #[Test]
    public function test_store_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), []);
        
        // Assert
        $response->assertSessionHasErrors([
            'title',
            'slug',
            'description',
            'property_type',
            'listing_type',
            'status',
            'price_type',
            'street_name',
            'district',
            'regency',
            'postcode',
            'agent_name'
        ]);
    }

    #[Test]
    public function test_store_validates_unique_slug()
    {
        // Arrange
        Property::factory()->create(['slug' => 'villa-canggu']);
        
        $propertyData = [
            'title' => 'Another Villa',
            'slug' => 'villa-canggu', // Duplicate slug
            'description' => 'Another beautiful villa',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Jalan Test',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertSessionHasErrors(['slug']);
    }

    #[Test]
    public function test_store_handles_floor_plan_upload()
    {
        // Arrange
        $floorPlan = UploadedFile::fake()->create('floor-plan.pdf', 1000, 'application/pdf');

        $propertyData = [
            'title' => 'Villa with Floor Plan',
            'slug' => 'villa-with-floor-plan',
            'description' => 'A villa with uploaded floor plan',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Jalan Test',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent',
            'floor_plan' => $floorPlan
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check database
        $property = Property::where('title', 'Villa with Floor Plan')->first();
        $this->assertNotNull($property);
        
        // Check floor plan was uploaded
        $this->assertNotNull($property->floor_plan);
        Storage::disk('public')->assertExists($property->floor_plan);
    }

    #[Test]
    public function test_store_handles_attachment_uploads() 
    {
        // Arrange
        $attachment1 = UploadedFile::fake()->image('living-room.jpg');
        $attachment2 = UploadedFile::fake()->image('bedroom.png');

        $propertyData = [
            'title' => 'Villa with Attachments',
            'slug' => 'villa-with-attachments',
            'description' => 'A villa with uploaded attachments',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Jalan Test',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent',
            'images' => [$attachment1, $attachment2] // Controller expects 'images' field
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check database
        $property = Property::where('title', 'Villa with Attachments')->first();
        $this->assertNotNull($property);
        
        // Check attachments were uploaded
        $this->assertCount(2, $property->attachments);
    }

    #[Test]
    public function test_store_without_file_uploads_works()
    {
        // Test that property creation works without files to isolate the file upload issue
        $propertyData = [
            'title' => 'Villa without Files',
            'slug' => 'villa-without-files',
            'description' => 'A villa without uploaded files',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Jalan Test',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check database
        $property = Property::where('title', 'Villa without Files')->first();
        $this->assertNotNull($property);
        $this->assertEquals('villa', $property->property_type);
    }

    #[Test]
    public function test_show_displays_specific_property()
    {
        // Arrange
        $property = Property::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.show', $property));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/properties/Show')
                ->where('property.id', $property->id)
        );
    }

    #[Test]
    public function test_edit_displays_property_edit_form()
    {
        // Arrange
        $property = Property::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.edit', $property));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/properties/Edit')
                ->where('property.id', $property->id)
                ->has('propertyTypes')
                ->has('listingTypes')
        );
    }

    #[Test]
    public function test_update_modifies_property_successfully()
    {
        // Arrange
        $property = Property::factory()->create([
            'title' => 'Old Title',
            'status' => 'pending'
        ]);

        $updateData = [
            'title' => 'Updated Villa Title',
            'slug' => $property->slug, // Keep existing slug
            'description' => $property->description,
            'property_type' => $property->property_type,
            'listing_type' => $property->listing_type,
            'status' => 'active',
            'price_type' => $property->price_type,
            'street_name' => $property->street_name,
            'district' => $property->district,
            'regency' => $property->regency,
            'postcode' => $property->postcode,
            'agent_name' => $property->agent_name
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.properties.update', $property), $updateData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Villa Title',
            'status' => 'active'
        ]);
    }

    #[Test]
    public function test_destroy_deletes_property()
    {
        // Arrange
        $property = Property::factory()->create();
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.properties.destroy', $property));
        
        // Assert
        $response->assertRedirect(route('admin.properties.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('properties', [
            'id' => $property->id
        ]);
    }

    #[Test]
    public function test_attach_feature_adds_feature_to_property()
    {
        // Arrange
        $property = Property::factory()->create();
        $feature = Feature::factory()->create(['is_quantifiable' => true]);

        $featureData = [
            'feature_id' => $feature->id,
            'quantity' => 2,
            'notes' => 'Large pool'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.features.attach', $property), $featureData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('feature_property', [
            'property_id' => $property->id,
            'feature_id' => $feature->id,
            'quantity' => 2,
            'notes' => 'Large pool'
        ]);
    }

    #[Test]
    public function test_detach_feature_removes_feature_from_property()
    {
        // Arrange
        $property = Property::factory()->create();
        $feature = Feature::factory()->create();
        $property->features()->attach($feature->id, ['quantity' => 1]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.properties.features.detach', [$property, $feature->id]));
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('feature_property', [
            'property_id' => $property->id,
            'feature_id' => $feature->id
        ]);
    }

    #[Test]
    public function test_unauthorized_user_cannot_access_admin_routes()
    {
        // Act & Assert
        $this->actingAs($this->user) // Regular user, not admin
            ->get(route('admin.properties.index'))
            ->assertStatus(302); // Redirect for unauthorized access
    }

    #[Test]
    public function test_store_handles_nullable_fields_correctly()
    {
        // Arrange - minimal required data, nullable fields omitted
        $propertyData = [
            'title' => 'Basic Property',
            'slug' => 'basic-property',
            'description' => 'Basic description',
            'property_type' => 'house',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Test Street',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent'
            // Many nullable fields omitted
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('properties', [
            'title' => 'Basic Property',
            'slug' => 'basic-property',
            'bedrooms' => null,
            'bathrooms' => null,
            'car_spaces' => null
        ]);
    }

    #[Test]
    public function test_validate_coordinates_within_range()
    {
        // Arrange
        $propertyData = [
            'title' => 'Property with Invalid Coords',
            'slug' => 'property-coords',
            'description' => 'Test property',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Test Street',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent',
            'latitude' => 91, // Invalid - outside range
            'longitude' => 181 // Invalid - outside range
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    #[Test]
    public function test_validates_year_built_range()
    {
        // Arrange
        $propertyData = [
            'title' => 'Property with Invalid Year',
            'slug' => 'property-year',
            'description' => 'Test property',
            'property_type' => 'villa',
            'listing_type' => 'for_sale',
            'status' => 'active',
            'price_type' => 'fixed',
            'street_name' => 'Test Street',
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'postcode' => '12345',
            'agent_name' => 'Test Agent',
            'year_built' => 1799 // Invalid - too old
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.store'), $propertyData);
        
        // Assert
        $response->assertSessionHasErrors(['year_built']);
    }
}