<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminBookingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $property;
    protected $availabilityService;

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
        
        // Create a test property
        $this->property = Property::factory()->create([
            'status' => 'active'
        ]);
        
        // Mock the AvailabilityService
        $this->availabilityService = Mockery::mock(AvailabilityService::class);
        $this->app->instance(AvailabilityService::class, $this->availabilityService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function test_index_displays_bookings_list()
    {
        // Arrange
        $bookings = Booking::factory()->count(3)->create([
            'property_id' => $this->property->id
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/bookings/Index')
                ->has('bookings.data', 3)
        );
    }

    #[Test]
    public function test_index_filters_by_search_term()
    {
        // Arrange
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);
        
        Booking::factory()->create([
            'property_id' => $this->property->id,
            'first_name' => 'Jane',
            'email' => 'jane@example.com'
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['search' => 'John Doe']));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('bookings.data', 1)
        );
    }

    #[Test]
    public function test_show_displays_specific_booking()
    {
        // Arrange
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $booking));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/bookings/Show')
                ->where('booking.id', $booking->id)
        );
    }

    #[Test]
    public function test_create_displays_booking_creation_form()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.create'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/bookings/Create')
                ->has('properties')
        );
    }

    #[Test]
    public function test_store_creates_booking_successfully()
    {
        // Arrange
        $this->availabilityService
            ->shouldReceive('isPropertyAvailable')
            ->once()
            ->andReturn(true);

        $bookingData = [
            'property_id' => $this->property->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'check_in_date' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 2,
            'number_of_rooms' => 1,
            'total_price' => 1000,
            'status' => 'pending',
            'source' => 'direct',
            'booking_type' => 'booking'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.store'), $bookingData);
        
        // Assert
        // Debug again to see what's happening
        dump('Response status: ' . $response->status());
        if (session()->has('error')) {
            dump('Error message: ' . session()->get('error'));
        }
        if (session()->has('errors')) {
            dump('Validation errors: ', session()->get('errors')->toArray());
        }
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', [
            'property_id' => $this->property->id,
            'email' => 'john@example.com'
        ]);
    }

    #[Test]
    public function test_store_fails_when_dates_unavailable()
    {
        // Arrange
        $this->availabilityService
            ->shouldReceive('isPropertyAvailable')
            ->once()
            ->andReturn(false);

        $bookingData = [
            'property_id' => $this->property->id,
            'check_in_date' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 2,
            'total_price' => 1000,
            'status' => 'pending',
            'source' => 'direct',
            'booking_type' => 'booking'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.store'), $bookingData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('bookings', [
            'property_id' => $this->property->id
        ]);
    }

    #[Test]
    public function test_store_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.store'), []);
        
        // Assert
        $response->assertSessionHasErrors([
            'property_id',
            'check_in_date',
            'check_out_date',
            'number_of_guests',
            'total_price',
            'status',
            'source',
            'booking_type'
        ]);
    }

    #[Test]
    public function test_edit_displays_booking_edit_form()
    {
        // Arrange
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.edit', $booking));
        
        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/bookings/Edit')
                ->where('booking.id', $booking->id)
                ->has('properties')
        );
    }

    #[Test]
    public function test_update_modifies_booking_successfully()
    {
        // Arrange
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'pending'
        ]);

        $this->availabilityService
            ->shouldReceive('isPropertyAvailable')
            ->once()
            ->andReturn(true);

        $updateData = [
            'property_id' => $this->property->id,
            'first_name' => 'Updated Name',
            'check_in_date' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 3,
            'total_price' => 1500,
            'status' => 'confirmed', // This triggers availability check
            'source' => 'direct',
            'booking_type' => 'booking'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.bookings.update', $booking), $updateData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'first_name' => 'Updated Name',
            'number_of_guests' => 3
        ]);
    }

    #[Test]
    public function test_update_status_to_confirmed_sends_email()
    {
        // Arrange
        Mail::fake(); // Add Mail fake to prevent actual email sending during tests
        
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'pending',
            'email' => 'test@example.com'
        ]);
        $this->availabilityService
            ->shouldReceive('isPropertyAvailable')
            ->once()
            ->andReturn(true);

        $updateData = [
            'status' => 'confirmed',
        ];

        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.bookings.update', $booking), $updateData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        // Check that the booking status was updated
        $this->assertDatabaseHas('bookings', [
                'id' => $booking->id,
                'status' => 'confirmed',
            ]);

        // Check that the mail was queued
        Mail::assertQueued(BookingConfirmedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email);
        });
    }

    #[Test]
    public function test_update_fails_when_confirmed_dates_unavailable()
    {
        // Arrange
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'pending'
        ]);

        $this->availabilityService
            ->shouldReceive('isPropertyAvailable')
            ->once()
            ->andReturn(false);

        $updateData = [
            'property_id' => $this->property->id,
            'check_in_date' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 2,
            'total_price' => 1000,
            'status' => 'confirmed', // This triggers availability check
            'source' => 'direct',
            'booking_type' => 'booking'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->put(route('admin.bookings.update', $booking), $updateData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function test_destroy_deletes_booking()
    {
        // Arrange
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id
        ]);
        
        // Act
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.bookings.destroy', $booking));
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id
        ]);
    }

    #[Test]
    public function test_unauthorized_user_cannot_access_admin_routes()
    {
        // Act & Assert
        $this->actingAs($this->user) // Regular user, not admin
            ->get(route('admin.bookings.index'))
            ->assertStatus(302); // Redirect for unauthorized access
    }

    #[Test]
    public function test_json_requests_return_json_errors()
    {
        // Arrange
        $this->availabilityService
            ->shouldReceive('isPropertyAvailable')
            ->once()
            ->andReturn(false);

        $bookingData = [
            'property_id' => $this->property->id,
            'check_in_date' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'number_of_guests' => 2,
            'total_price' => 1000,
            'status' => 'pending',
            'source' => 'direct',
            'booking_type' => 'booking'
        ];
        
        // Act
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.bookings.store'), $bookingData);
        
        // Assert
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The selected dates are not available for this property.',
            'errors' => [
                'check_in_date' => ['The selected dates are not available for this property.']
            ]
        ]);
    }
}