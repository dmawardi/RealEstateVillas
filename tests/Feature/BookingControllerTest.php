<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmationAdminMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingUpdateAdminMail;
use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyPrice;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;
    protected $property;
    protected $availabilityService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users for testing
        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Create a property for testing
        $this->property = Property::factory()->create([
            'status' => 'active',
            'listing_type' => 'for_rent'
        ]);
        
        // Add pricing to the property
        PropertyPrice::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(365),
            'nightly_rate' => 100
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function test_index_displays_user_bookings()
    {
        // Arrange
        $userBookings = Booking::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id
        ]);
        
        $otherUserBookings = Booking::factory()->count(2)->create([
            'user_id' => $this->admin->id,
            'property_id' => $this->property->id
        ]);

        // Act
        $response = $this->actingAs($this->user)->get(route('my.bookings'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('bookings/Index')
                ->has('bookings')
                ->has('bookings.data', 3) // Only user's bookings
                ->has('filters')
                ->has('seoData')
                ->where('seoData.title', 'My Bookings')
        );
    }

    #[Test]
    public function test_index_requires_authentication()
    {
        // Act
        $response = $this->get(route('my.bookings'));

        // Assert
        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_index_filters_by_status()
    {
        // Arrange
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'status' => 'pending'
        ]);
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'status' => 'confirmed'
        ]);

        // Act
        $response = $this->actingAs($this->user)->get(route('my.bookings', ['status' => 'pending']));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('bookings.data', 1)
                ->where('filters.status', 'pending')
        );
    }

    #[Test]
    public function test_index_filters_by_date_range()
    {
        // Arrange
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7)
        ]);
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'check_in_date' => now()->addDays(15),
            'check_out_date' => now()->addDays(17)
        ]);

        // Act
        $response = $this->actingAs($this->user)->get(route('my.bookings', [
            'date_from' => now()->addDays(4)->format('Y-m-d'),
            'date_to' => now()->addDays(10)->format('Y-m-d')
        ]));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('bookings.data', 1) // Only one booking in date range
        );
    }

    #[Test]
    public function test_store_creates_booking_successfully()
    {
        // Arrange
        Mail::fake();
        
        // Mock AvailabilityService to return true
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isPropertyAvailable')->andReturn(true);
        });

        $bookingData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '123-456-7890',
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 2,
            'number_of_rooms' => 1,
            'flexible_dates' => false,
            'special_requests' => 'Late check-in requested',
            'total_price' => 200.00
        ];

        // Act
        $response = $this->actingAs($this->user)->post(
            route('properties.bookings.store', $this->property),
            $bookingData
        );

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('bookings', [
            'property_id' => $this->property->id,
            'user_id' => $this->user->id,
            'email' => 'john.doe@example.com',
            'status' => 'pending',
            'source' => 'direct'
        ]);

        Mail::assertQueued(BookingConfirmationMail::class);
        Mail::assertQueued(BookingConfirmationAdminMail::class);
    }

    #[Test]
    public function test_store_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->user)->post(
            route('properties.bookings.store', $this->property),
            []
        );

        // Assert
        $response->assertSessionHasErrors([
            'check_in_date',
            'check_out_date',
            'number_of_guests',
            'total_price'
        ]);
    }

    #[Test]
    public function test_store_validates_date_constraints()
    {
        // Act - Test past check-in date
        $response = $this->actingAs($this->user)->post(
            route('properties.bookings.store', $this->property),
            [
                'check_in_date' => now()->subDays(1)->format('Y-m-d'),
                'check_out_date' => now()->addDays(1)->format('Y-m-d'),
                'number_of_guests' => 2,
                'total_price' => 100
            ]
        );

        $response->assertSessionHasErrors(['check_in_date']);

        // Act - Test check-out before check-in
        $response = $this->actingAs($this->user)->post(
            route('properties.bookings.store', $this->property),
            [
                'check_in_date' => now()->addDays(5)->format('Y-m-d'),
                'check_out_date' => now()->addDays(3)->format('Y-m-d'),
                'number_of_guests' => 2,
                'total_price' => 100
            ]
        );

        $response->assertSessionHasErrors(['check_out_date']);
    }

    #[Test]
    public function test_store_handles_unavailable_dates()
    {
        // Arrange - Mock AvailabilityService to return false
        $this->mock(AvailabilityService::class, function ($mock) {
            $mock->shouldReceive('isPropertyAvailable')->andReturn(false);
        });

        $bookingData = [
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 2,
            'total_price' => 200
        ];

        // Act
        $response = $this->actingAs($this->user)->post(
            route('properties.bookings.store', $this->property),
            $bookingData
        );

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'The selected dates are not available for this property.');
        $this->assertDatabaseMissing('bookings', [
            'property_id' => $this->property->id,
            'user_id' => $this->user->id
        ]);
    }

    // #[Test]
    // public function test_store_handles_email_failure()
    // {
    //     // Arrange
    //     $this->mock(AvailabilityService::class, function ($mock) {
    //         $mock->shouldReceive('isPropertyAvailable')->andReturn(true);
    //     });

    //     // Don't mock Mail to throw exception - let the booking creation succeed
    //     // but simulate that email sending fails in the nested try-catch
    //     Mail::fake();
        
    //     // Mock the Mail facade to simulate email failure without breaking the main flow
    //     Mail::shouldReceive('to')->andReturnSelf();
    //     Mail::shouldReceive('queue')->andThrow(new \Exception('Email service unavailable'));
        
    //     Log::shouldReceive('error')->once(); // For email failure logging

    //     $bookingData = [
    //         'first_name' => 'John',
    //         'last_name' => 'Doe',
    //         'email' => 'john.doe@example.com',
    //         'check_in_date' => now()->addDays(1)->format('Y-m-d'),
    //         'check_out_date' => now()->addDays(3)->format('Y-m-d'),
    //         'number_of_guests' => 2,
    //         'total_price' => 200
    //     ];

    //     // Act
    //     $response = $this->actingAs($this->user)->post(
    //         route('properties.bookings.store', $this->property),
    //         $bookingData
    //     );

    //     // Assert - Booking should still be created even if email fails
    //     $response->assertRedirect();
        
    //     // Debug what's actually in the session
    //     $sessionData = $response->getSession()->all();
        
    //     if ($response->getSession()->has('success')) {
    //         $response->assertSessionHas('success');
    //     } else {
    //         $this->fail('Expected success session message not found. Session contains: ' . json_encode($sessionData));
    //     }
        
    //     $this->assertDatabaseHas('bookings', [
    //         'property_id' => $this->property->id,
    //         'user_id' => $this->user->id,
    //         'email' => 'john.doe@example.com'
    //     ]);
    // }

    // #[Test]
    // public function test_withdraw_updates_booking_status()
    // {
    //     // Arrange
    //     Mail::fake();
    //     $booking = Booking::factory()->create([
    //         'user_id' => $this->user->id,
    //         'property_id' => $this->property->id,
    //         'status' => 'pending'
    //     ]);

    //     // Act
    //     $response = $this->actingAs($this->user)->post(
    //         route('bookings.withdraw', $booking),
    //         ['status' => 'withdrawn']
    //     );

    //     // Assert
    //     $response->assertRedirect();
    //     $response->assertSessionHas('success');
        
    //     $booking->refresh();
    //     $this->assertEquals('withdrawn', $booking->status);
        
    //     Mail::assertQueued(BookingUpdateAdminMail::class);
    // }

    // #[Test]
    // public function test_withdraw_requires_authentication()
    // {
    //     // Arrange
    //     $booking = Booking::factory()->create([
    //         'user_id' => $this->user->id,
    //         'property_id' => $this->property->id,
    //         'status' => 'pending'
    //     ]);

    //     // Act
    //     $response = $this->post(route('bookings.withdraw', $booking), ['status' => 'withdrawn']);

    //     // Assert
    //     $response->assertRedirect('/login');
    // }

    // #[Test]
    // public function test_withdraw_prevents_unauthorized_access()
    // {
    //     // Arrange
    //     $otherUser = User::factory()->create();
    //     $booking = Booking::factory()->create([
    //         'user_id' => $otherUser->id,
    //         'property_id' => $this->property->id,
    //         'status' => 'pending'
    //     ]);

    //     // Act
    //     $response = $this->actingAs($this->user)->post(
    //         route('bookings.withdraw', $booking),
    //         ['status' => 'withdrawn']
    //     );

    //     // Assert
    //     $response->assertRedirect();
    //     $response->assertSessionHasErrors(['message']);
        
    //     $booking->refresh();
    //     $this->assertEquals('pending', $booking->status); // Status unchanged
    // }

    // #[Test]
    // public function test_withdraw_only_allows_pending_or_confirmed_bookings()
    // {
    //     // Arrange
    //     $booking = Booking::factory()->create([
    //         'user_id' => $this->user->id,
    //         'property_id' => $this->property->id,
    //         'status' => 'completed'
    //     ]);

    //     // Act
    //     $response = $this->actingAs($this->user)->post(
    //         route('bookings.withdraw', $booking),
    //         ['status' => 'withdrawn']
    //     );

    //     // Assert
    //     $response->assertRedirect();
    //     $response->assertSessionHasErrors(['message']);
        
    //     $booking->refresh();
    //     $this->assertEquals('completed', $booking->status); // Status unchanged
    // }

    // #[Test]
    // public function test_update_booking_successfully()
    // {
    //     // Arrange
    //     Mail::fake();
    //     $this->mock(AvailabilityService::class, function ($mock) {
    //         $mock->shouldReceive('isPropertyAvailable')->andReturn(true);
    //     });

    //     $booking = Booking::factory()->create([
    //         'property_id' => $this->property->id,
    //         'first_name' => 'John',
    //         'email' => 'john@example.com'
    //     ]);

    //     $updateData = [
    //         'first_name' => 'Jane',
    //         'last_name' => 'Smith',
    //         'email' => 'jane.smith@example.com',
    //         'phone' => '987-654-3210',
    //         'check_in_date' => now()->addDays(2)->format('Y-m-d'),
    //         'check_out_date' => now()->addDays(4)->format('Y-m-d'),
    //         'number_of_guests' => 4,
    //         'number_of_rooms' => 2,
    //         'flexible_dates' => true,
    //         'source' => 'direct',
    //         'booking_type' => 'booking',
    //         'total_price' => 300,
    //         'special_requests' => 'Early check-in'
    //     ];

    //     // Act
    //     $response = $this->putJson('/api/bookings/' . $booking->id, $updateData);

    //     // Assert
    //     $response->assertStatus(200);
    //     $response->assertJson(['message' => 'Booking updated successfully.']);
        
    //     $booking->refresh();
    //     $this->assertEquals('Jane', $booking->first_name);
    //     $this->assertEquals('jane.smith@example.com', $booking->email);
        
    //     Mail::assertQueued(BookingUpdateAdminMail::class);
    // }

    // #[Test]
    // public function test_update_validates_date_availability()
    // {
    //     // Arrange
    //     $this->mock(AvailabilityService::class, function ($mock) {
    //         $mock->shouldReceive('isPropertyAvailable')->andReturn(false);
    //     });

    //     $booking = Booking::factory()->create([
    //         'property_id' => $this->property->id
    //     ]);

    //     $updateData = [
    //         'check_in_date' => now()->addDays(10)->format('Y-m-d'),
    //         'check_out_date' => now()->addDays(12)->format('Y-m-d'),
    //         'number_of_guests' => 2,
    //         'total_price' => 200
    //     ];

    //     // Act
    //     $response = $this->putJson('/api/bookings/' . $booking->id, $updateData);

    //     // Assert
    //     $response->assertStatus(422);
    //     $response->assertJson([
    //         'message' => 'The selected dates are not available for this property.'
    //     ]);
    // }

    // #[Test]
    // public function test_destroy_deletes_booking()
    // {
    //     // Arrange
    //     $booking = Booking::factory()->create([
    //         'property_id' => $this->property->id
    //     ]);

    //     // Act
    //     $response = $this->deleteJson('/api/bookings/' . $booking->id);

    //     // Assert
    //     $response->assertStatus(200);
    //     $response->assertJson(['message' => 'Booking deleted successfully.']);
        
    //     $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    // }

    // #[Test]
    // public function test_destroy_handles_exceptions()
    // {
    //     // This test would need to mock a scenario where deletion fails
    //     // For simplicity, we'll test that a non-existent booking returns appropriate error
        
    //     // Act
    //     $response = $this->deleteJson('/api/bookings/99999');

    //     // Assert
    //     $response->assertStatus(404);
    // }
}
