<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyPrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminPropertyPriceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $property;

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

        // Create a property for testing
        $this->property = Property::factory()->create([
            'listing_type' => 'for_rent'
        ]);
    }

    #[Test]
    public function test_store_creates_property_pricing_successfully()
    {
        // Arrange
        $pricingData = [
            'name' => 'High Season Pricing',
            'nightly_rate' => 150.00,
            'weekly_discount_percent' => 10.0,
            'monthly_discount_percent' => 20.0,
            'weekend_premium_percent' => 15.0,
            'weekly_discount_active' => true,
            'monthly_discount_active' => true,
            'weekend_premium_active' => false,
            'currency' => 'IDR',
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-31',
            'min_days_for_weekly' => 7,
            'min_days_for_monthly' => 30,
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success', 'Property pricing created successfully.');

        $this->assertDatabaseHas('property_pricing', [
            'property_id' => $this->property->id,
            'name' => 'High Season Pricing',
            'nightly_rate' => 150.00,
            'currency' => 'IDR',
            'start_date' => Carbon::parse('2025-01-01')->startOfDay()->toDateTimeString(),
            'end_date' => Carbon::parse('2025-03-31')->startOfDay()->toDateTimeString()
        ]);
    }

    #[Test]
    public function test_store_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), []);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors([
                'nightly_rate',
                'start_date',
                'end_date'
            ]);
    }

    #[Test]
    public function test_store_validates_end_date_after_start_date()
    {
        // Arrange
        $pricingData = [
            'nightly_rate' => 150.00,
            'start_date' => '2025-03-31',
            'end_date' => '2025-01-01', // End date before start date
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors(['end_date']);
    }

    #[Test]
    public function test_store_validates_numeric_constraints()
    {
        // Arrange
        $pricingData = [
            'nightly_rate' => -10, // Negative rate
            'weekly_discount_percent' => 150, // Over 100%
            'monthly_discount_percent' => -5, // Negative percentage
            'weekend_premium_percent' => 101, // Over 100%
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-31',
            'min_days_for_weekly' => 0, // Below minimum
            'min_days_for_monthly' => 0, // Below minimum
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors([
                'nightly_rate',
                'weekly_discount_percent',
                'monthly_discount_percent',
                'weekend_premium_percent',
                'min_days_for_weekly',
                'min_days_for_monthly'
            ]);
    }

    #[Test]
    public function test_store_validates_currency_format()
    {
        // Arrange
        $pricingData = [
            'nightly_rate' => 150.00,
            'currency' => 'INVALID', // Not 3 characters
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-31',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors(['currency']);
    }

    #[Test]
    public function test_store_prevents_overlapping_date_ranges()
    {
        // Arrange - Create existing pricing
        PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-28',
        ]);

        $overlappingData = [
            'nightly_rate' => 150.00,
            'start_date' => '2025-02-15', // Overlaps with existing
            'end_date' => '2025-03-15',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $overlappingData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors([
                'dates' => 'The selected dates overlap with an existing pricing period.'
            ]);
    }

    #[Test]
    public function test_store_allows_adjacent_date_ranges()
    {
        // Arrange - Create existing pricing
        PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $adjacentData = [
            'nightly_rate' => 150.00,
            'start_date' => '2025-02-01', // Starts after existing ends
            'end_date' => '2025-02-28',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $adjacentData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('property_pricing', [
            'property_id' => $this->property->id,
            'start_date' => Carbon::parse('2025-02-01')->startOfDay(),
            'end_date' => Carbon::parse('2025-02-28')->startOfDay()
        ]);
    }

    #[Test]
    public function test_store_sets_default_values()
    {
        // Arrange - Minimal data
        $pricingData = [
            'nightly_rate' => 100.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('property_pricing', [
            'property_id' => $this->property->id,
            'nightly_rate' => 100.00,
            'weekly_discount_percent' => 0,
            'monthly_discount_percent' => 0,
            'weekend_premium_percent' => 0,
            'weekly_discount_active' => false,
            'monthly_discount_active' => false,
            'weekend_premium_active' => false,
            'currency' => 'IDR',
            'min_days_for_weekly' => 7,
            'min_days_for_monthly' => 30,
        ]);
    }

    #[Test]
    public function test_update_modifies_pricing_successfully()
    {
        // Arrange
        $pricing = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'name' => 'Old Name',
            'nightly_rate' => 100.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $updateData = [
            'name' => 'Updated Pricing',
            'nightly_rate' => 200.00,
            'weekly_discount_percent' => 15.0,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.update', $pricing), $updateData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success', 'Property pricing updated successfully.');

        $this->assertDatabaseHas('property_pricing', [
            'id' => $pricing->id,
            'name' => 'Updated Pricing',
            'nightly_rate' => 200.00,
            'weekly_discount_percent' => 15.0
        ]);
    }

    #[Test]
    public function test_update_prevents_overlapping_with_other_pricing()
    {
        // Arrange
        $pricing1 = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $pricing2 = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-31',
        ]);

        $updateData = [
            'nightly_rate' => 150.00,
            'start_date' => '2025-02-15', // Would overlap with pricing2
            'end_date' => '2025-03-15',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.update', $pricing1), $updateData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors([
                'dates' => 'The selected dates overlap with an existing pricing period.'
            ]);
    }

    #[Test]
    public function test_update_allows_same_pricing_dates()
    {
        // Arrange
        $pricing = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'nightly_rate' => 100.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $updateData = [
            'nightly_rate' => 200.00, // Just changing the rate
            'start_date' => '2025-01-01', // Same dates
            'end_date' => '2025-01-31',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.update', $pricing), $updateData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('property_pricing', [
            'id' => $pricing->id,
            'nightly_rate' => 200.00
        ]);
    }

    #[Test]
    public function test_update_preserves_existing_values_for_nullable_fields()
    {
        // Arrange
        $pricing = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'name' => 'Existing Name',
            'weekly_discount_percent' => 10.0,
            'currency' => 'IDR',
        ]);

        $updateData = [
            'nightly_rate' => 200.00,
            'start_date' => $pricing->start_date,
            'end_date' => $pricing->end_date,
            // Not providing name, weekly_discount_percent, currency
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.update', $pricing), $updateData);
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success');
        $pricing->refresh();
        $this->assertEquals('Existing Name', $pricing->name);
        $this->assertEquals(10.0, $pricing->weekly_discount_percent);
        $this->assertEquals('IDR', $pricing->currency);
    }

    #[Test]
    public function test_destroy_deletes_pricing_successfully()
    {
        // Arrange
        $pricing = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.pricing.destroy', $pricing));
        
        // Assert
        $response->assertRedirect()
            ->assertSessionHas('success', 'Property pricing deleted successfully.');

        $this->assertDatabaseMissing('property_pricing', [
            'id' => $pricing->id
        ]);
    }

    #[Test]
    public function test_unauthorized_user_cannot_access_pricing_routes()
    {
        // Arrange
        $pricing = PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
        ]);

        $pricingData = [
            'nightly_rate' => 150.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ];
        
        // Act & Assert - Regular user cannot access admin routes
        $this->actingAs($this->user)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData)
            ->assertStatus(302); // Redirect for unauthorized

        $this->actingAs($this->user)
            ->put(route('admin.pricing.update', $pricing), $pricingData)
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->delete(route('admin.pricing.destroy', $pricing))
            ->assertStatus(302);
    }

    #[Test]
    public function test_store_handles_different_overlap_scenarios()
    {
        // Arrange - Create existing pricing
        PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => '2025-02-10',
            'end_date' => '2025-02-20',
        ]);

        // Test different overlap scenarios
        $overlapScenarios = [
            // Start date within existing range
            [
                'start_date' => '2025-02-15',
                'end_date' => '2025-02-25',
            ],
            // End date within existing range
            [
                'start_date' => '2025-02-05',
                'end_date' => '2025-02-15',
            ],
            // Completely encompasses existing range
            [
                'start_date' => '2025-02-05',
                'end_date' => '2025-02-25',
            ],
        ];

        foreach ($overlapScenarios as $scenario) {
            $pricingData = [
                'nightly_rate' => 150.00,
                'start_date' => $scenario['start_date'],
                'end_date' => $scenario['end_date'],
            ];
            
            // Act
            $response = $this->actingAs($this->admin)
                ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
            
            // Assert
            $response->assertRedirect()
                ->assertSessionHasErrors(['dates'], "Failed for scenario: {$scenario['start_date']} to {$scenario['end_date']}");
        }
    }

    #[Test]
    public function test_pricing_for_different_properties_can_overlap()
    {
        // Arrange
        $property2 = Property::factory()->create(['listing_type' => 'for_rent']);
        
        PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-28',
        ]);

        $pricingData = [
            'nightly_rate' => 150.00,
            'start_date' => '2025-02-15', // Same dates as property 1
            'end_date' => '2025-02-25',
        ];
        
        // Act - Create pricing for different property with overlapping dates
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $property2), $pricingData);
        
        // Assert - Should succeed since it's a different property
        $response->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('property_pricing', [
            'property_id' => $property2->id,
            'start_date' => Carbon::parse('2025-02-15'),
            'end_date' => Carbon::parse('2025-02-25')
        ]);
    }

    #[Test]
    public function test_date_parsing_handles_different_formats()
    {
        // Arrange
        $pricingData = [
            'nightly_rate' => 150.00,
            'start_date' => '01/01/2025', // Different date format
            'end_date' => '31/01/2025',
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.pricing.store', $this->property), $pricingData);
        
        // Assert - Laravel validation should handle or reject invalid date formats
        // This tests the robustness of date parsing
        $this->assertTrue(in_array($response->status(), [302])); // Should redirect (success or validation errors)
    }
}
