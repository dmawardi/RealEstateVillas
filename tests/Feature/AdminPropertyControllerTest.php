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
        Storage::fake('s3');
    }

    /** @test */
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

    /** @test */
    public function test_index_filters_by_search_term()
    {
        // Arrange
        $property1 = Property::factory()->create([
            'title' => 'Beautiful Villa in Canggu',
            'property_id' => 'PROP-2025-ABC123'
        ]);
        
        $property2 = Property::factory()->create([
            'title' => 'Modern Apartment in Seminyak',
            'property_id' => 'PROP-2025-DEF456'
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    // public function test_store_handles_file_uploads()
    // {
    //     // Arrange
    //     $floorPlan = UploadedFile::fake()->create('floor-plan.pdf', 1000, 'application/pdf');
    //     $images = [
    //         UploadedFile::fake()->image('image1.jpg', 800, 600),
    //         UploadedFile::fake()->image('image2.jpg', 800, 600)
    //     ];

    //     $propertyData = [
    //         'title' => 'Villa with Files',
    //         'slug' => 'villa-with-files',
    //         'description' => 'A villa with uploaded files',
    //         'property_type' => 'villa',
    //         'listing_type' => 'for_sale',
    //         'status' => 'active',
    //         'price_type' => 'fixed',
    //         'street_name' => 'Jalan Test',
    //         'district' => 'Test District',
    //         'regency' => 'Test Regency',
    //         'postcode' => '12345',
    //         'agent_name' => 'Test Agent',
    //         'floor_plan' => $floorPlan,
    //         'images' => $images
    //     ];
        
    //     // Act
    //     $response = $this->actingAs($this->admin)
    //         ->post(route('admin.properties.store'), $propertyData);
        
    //     // Assert
    //     $response->assertRedirect();
    //     $response->assertSessionHas('success');
        
    //     // Check database
    //     $property = Property::where('title', 'Villa with Files')->first();
    //     $this->assertNotNull($property);
    //     $this->assertNotNull($property->floor_plan_url);
        
    //     // Check attachments were created
    //     $this->assertDatabaseHas('property_attachments', [
    //         'property_id' => $property->id,
    //         'type' => 'image'
    //     ]);
    // }

//     /** @test */
//     public function test_show_displays_specific_property()
//     {
//         // Arrange
//         $property = Property::factory()->create();
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->get(route('properties.show', $property));
        
//         // Assert
//         $response->assertStatus(200);
//         $response->assertInertia(fn ($page) => 
//             $page->component('admin/properties/Show')
//                 ->where('property.id', $property->id)
//         );
//     }

//     /** @test */
//     public function test_edit_displays_property_edit_form()
//     {
//         // Arrange
//         $property = Property::factory()->create();
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->get(route('properties.edit', $property));
        
//         // Assert
//         $response->assertStatus(200);
//         $response->assertInertia(fn ($page) => 
//             $page->component('admin/properties/Edit')
//                 ->where('property.id', $property->id)
//                 ->has('propertyTypes')
//                 ->has('listingTypes')
//         );
//     }

//     /** @test */
//     public function test_update_modifies_property_successfully()
//     {
//         // Arrange
//         $property = Property::factory()->create([
//             'title' => 'Old Title',
//             'status' => 'pending'
//         ]);

//         $updateData = [
//             'title' => 'Updated Villa Title',
//             'slug' => $property->slug, // Keep existing slug
//             'description' => $property->description,
//             'property_type' => $property->property_type,
//             'listing_type' => $property->listing_type,
//             'status' => 'active',
//             'price_type' => $property->price_type,
//             'street_name' => $property->street_name,
//             'district' => $property->district,
//             'regency' => $property->regency,
//             'postcode' => $property->postcode,
//             'agent_name' => $property->agent_name
//         ];
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->put(route('properties.update', $property), $updateData);
        
//         // Assert
//         $response->assertRedirect();
//         $response->assertSessionHas('success');
//         $this->assertDatabaseHas('properties', [
//             'id' => $property->id,
//             'title' => 'Updated Villa Title',
//             'status' => 'active'
//         ]);
//     }

//     /** @test */
//     public function test_destroy_deletes_property()
//     {
//         // Arrange
//         $property = Property::factory()->create();
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->delete(route('properties.destroy', $property));
        
//         // Assert
//         $response->assertRedirect(route('properties.index'));
//         $response->assertSessionHas('success');
//         $this->assertDatabaseMissing('properties', [
//             'id' => $property->id
//         ]);
//     }

//     /** @test */
//     public function test_attach_feature_adds_feature_to_property()
//     {
//         // Arrange
//         $property = Property::factory()->create();
//         $feature = Feature::factory()->create();

//         $featureData = [
//             'feature_id' => $feature->id,
//             'quantity' => 2,
//             'custom_value' => 'Large pool'
//         ];
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->post(route('properties.features.attach', $property), $featureData);
        
//         // Assert
//         $response->assertRedirect();
//         $response->assertSessionHas('success');
//         $this->assertDatabaseHas('feature_property', [
//             'property_id' => $property->id,
//             'feature_id' => $feature->id,
//             'quantity' => 2,
//             'custom_value' => 'Large pool'
//         ]);
//     }

//     /** @test */
//     public function test_detach_feature_removes_feature_from_property()
//     {
//         // Arrange
//         $property = Property::factory()->create();
//         $feature = Feature::factory()->create();
//         $property->features()->attach($feature->id, ['quantity' => 1]);
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->delete(route('properties.features.detach', [$property, $feature->id]));
        
//         // Assert
//         $response->assertRedirect();
//         $response->assertSessionHas('success');
//         $this->assertDatabaseMissing('feature_property', [
//             'property_id' => $property->id,
//             'feature_id' => $feature->id
//         ]);
//     }

//     /** @test */
//     public function test_unauthorized_user_cannot_access_admin_routes()
//     {
//         // Act & Assert
//         $this->actingAs($this->user) // Regular user, not admin
//             ->get(route('properties.index'))
//             ->assertStatus(302); // Redirect for unauthorized access
//     }

//     /** @test */
//     public function test_store_handles_nullable_fields_correctly()
//     {
//         // Arrange - minimal required data, nullable fields omitted
//         $propertyData = [
//             'title' => 'Basic Property',
//             'slug' => 'basic-property',
//             'description' => 'Basic description',
//             'property_type' => 'house',
//             'listing_type' => 'for_sale',
//             'status' => 'active',
//             'price_type' => 'fixed',
//             'street_name' => 'Test Street',
//             'district' => 'Test District',
//             'regency' => 'Test Regency',
//             'postcode' => '12345',
//             'agent_name' => 'Test Agent'
//             // Many nullable fields omitted
//         ];
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->post(route('admin.properties.store'), $propertyData);
        
//         // Assert
//         $response->assertRedirect();
//         $response->assertSessionHas('success');
//         $this->assertDatabaseHas('properties', [
//             'title' => 'Basic Property',
//             'slug' => 'basic-property',
//             'bedrooms' => null,
//             'bathrooms' => null,
//             'car_spaces' => null
//         ]);
//     }

//     /** @test */
//     public function test_validate_coordinates_within_range()
//     {
//         // Arrange
//         $propertyData = [
//             'title' => 'Property with Invalid Coords',
//             'slug' => 'property-coords',
//             'description' => 'Test property',
//             'property_type' => 'villa',
//             'listing_type' => 'for_sale',
//             'status' => 'active',
//             'price_type' => 'fixed',
//             'street_name' => 'Test Street',
//             'district' => 'Test District',
//             'regency' => 'Test Regency',
//             'postcode' => '12345',
//             'agent_name' => 'Test Agent',
//             'latitude' => 91, // Invalid - outside range
//             'longitude' => 181 // Invalid - outside range
//         ];
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->post(route('properties.store'), $propertyData);
        
//         // Assert
//         $response->assertSessionHasErrors(['latitude', 'longitude']);
//     }

//     /** @test */
//     public function test_validates_year_built_range()
//     {
//         // Arrange
//         $propertyData = [
//             'title' => 'Property with Invalid Year',
//             'slug' => 'property-year',
//             'description' => 'Test property',
//             'property_type' => 'villa',
//             'listing_type' => 'for_sale',
//             'status' => 'active',
//             'price_type' => 'fixed',
//             'street_name' => 'Test Street',
//             'district' => 'Test District',
//             'regency' => 'Test Regency',
//             'postcode' => '12345',
//             'agent_name' => 'Test Agent',
//             'year_built' => 1799 // Invalid - too old
//         ];
        
//         // Act
//         $response = $this->actingAs($this->admin)
//             ->post(route('properties.store'), $propertyData);
        
//         // Assert
//         $response->assertSessionHasErrors(['year_built']);
//     }
}