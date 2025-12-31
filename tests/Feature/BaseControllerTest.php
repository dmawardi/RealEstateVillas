<?php

namespace Tests\Feature;

use App\Mail\ContactEnquiryMail;
use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyAttachment;
use App\Models\PropertyPrice;
use App\Models\User;
use App\Rules\RecaptchaRule;
use DrewM\MailChimp\MailChimp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BaseControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users for testing
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        // Create a testable BaseController that bypasses reCAPTCHA validation for all tests
        $testableController = new class extends \App\Http\Controllers\BaseController {
            protected function getRecaptchaRule(): \App\Rules\RecaptchaRule
            {
                // Return a mock that always passes validation for testing
                return new class extends \App\Rules\RecaptchaRule {
                    public function validate(string $attribute, mixed $value, \Closure $fail): void
                    {
                        // Always pass validation for testing
                        return;
                    }
                };
            }
            
            protected function getMailChimp(): MailChimp
            {
                // Return a mock MailChimp instance that always succeeds
                $mock = Mockery::mock(MailChimp::class);
                $mock->shouldReceive('post')->andReturn(['id' => 'test123']);
                $mock->shouldReceive('success')->andReturn(true);
                $mock->shouldReceive('getLastError')->andReturn(null);
                return $mock;
            }
        };
        
        // Replace the BaseController in the container for all tests
        $this->app->instance(\App\Http\Controllers\BaseController::class, $testableController);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function test_home_displays_featured_and_premium_properties()
    {
        // Arrange
        $featuredProperty = Property::factory()->create(['is_featured' => true]);
        $premiumProperty = Property::factory()->create(['is_premium' => true]);
        $regularProperty = Property::factory()->create(['is_featured' => false, 'is_premium' => false]);

        // Add pricing to featured property
        PropertyPrice::factory()->create([
            'property_id' => $featuredProperty->id,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30)
        ]);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Welcome')
                ->has('featured')
                ->has('premium')
                ->has('businessPhone')
                ->has('businessEmail')
                ->has('seoData')
                ->where('seoData.title', fn ($title) => str_contains($title, 'Bali Villa Rentals'))
                ->where('seoData.canonicalUrl', url('/'))
        );
    }

    #[Test]
    public function test_home_uses_cache_for_properties()
    {
        // Arrange
        $cachedProperties = collect([
            (object) [
                'id' => 1,
                'is_featured' => true,
                'is_premium' => false,
                'attachments' => collect(),
                'features' => collect(),
                'pricing' => collect()
            ]
        ]);
        
        Cache::shouldReceive('get')
            ->with('properties:featured_premium')
            ->once()
            ->andReturn($cachedProperties);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function test_home_caches_properties_when_not_cached()
    {
        // Arrange
        $featuredProperty = Property::factory()->create(['is_featured' => true]);
        
        Cache::shouldReceive('get')
            ->with('properties:featured_premium')
            ->once()
            ->andReturn(null);

        Cache::shouldReceive('put')
            ->with('properties:featured_premium', Mockery::any(), 60)
            ->once();

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function test_home_generates_dynamic_seo_data()
    {
        // Arrange
        Property::factory()->count(3)->create(['is_featured' => true]);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertInertia(fn ($page) =>
            $page->has('seoData')
                ->where('seoData.title', fn ($title) => str_contains($title, '3+ Premium Properties'))
                ->has('seoData.description')
                ->has('seoData.keywords')
                ->has('seoData.ogImage')
        );
    }

    #[Test]
    public function test_dashboard_shows_admin_statistics()
    {
        // Arrange
        Property::factory()->count(5)->create(['status' => 'active']);
        Booking::factory()->count(3)->create(['status' => 'confirmed']);
        Booking::factory()->count(2)->create(['status' => 'pending']);

        // Act
        $response = $this->actingAs($this->admin)->get('/dashboard');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Dashboard')
                ->has('stats')
                ->has('stats.total_properties')
                ->has('stats.active_bookings')
                ->has('stats.pending_bookings')
                ->has('stats.monthly_revenue')
                ->has('stats.properties_needing_pricing')
                ->has('topProperties')
                ->has('recentBookings')
                ->has('seoData')
                ->where('seoData.title', 'Admin Dashboard')
        );
    }

    #[Test]
    public function test_dashboard_shows_user_dashboard_for_regular_users()
    {
        // Act
        $response = $this->actingAs($this->user)->get('/dashboard');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Dashboard')
                ->has('seoData')
                ->where('seoData.title', 'User Dashboard')
                ->missing('stats')
                ->missing('topProperties')
                ->missing('recentBookings')
        );
    }

    #[Test]
    public function test_dashboard_calculates_properties_needing_pricing()
    {
        // Arrange
        // Property with no pricing
        $noPricingProperty = Property::factory()->create([
            'listing_type' => 'for_rent',
            'status' => 'active'
        ]);

        // Property with expired pricing
        $expiredPricingProperty = Property::factory()->create([
            'listing_type' => 'for_rent',
            'status' => 'active'
        ]);
        PropertyPrice::factory()->create([
            'property_id' => $expiredPricingProperty->id,
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1)
        ]);

        // Property with pricing ending soon
        $endingSoonProperty = Property::factory()->create([
            'listing_type' => 'for_rent',
            'status' => 'active'
        ]);
        PropertyPrice::factory()->create([
            'property_id' => $endingSoonProperty->id,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addMonths(3)
        ]);

        // Act
        $response = $this->actingAs($this->admin)->get('/dashboard');

        // Assert
        $response->assertInertia(fn ($page) =>
            $page->has('stats.properties_needing_pricing.no_pricing', 1)
                ->has('stats.properties_needing_pricing.ending_soon', 1)
                ->has('stats.properties_needing_pricing.no_active_pricing', 1)
                ->where('stats.properties_needing_pricing.total_count', 3)
        );
    }

    #[Test]
    public function test_dashboard_shows_top_properties_by_view_count()
    {
        // Arrange
        Property::factory()->create(['status' => 'active', 'view_count' => 100]);
        Property::factory()->create(['status' => 'active', 'view_count' => 200]);
        Property::factory()->create(['status' => 'active', 'view_count' => 50]);

        // Act
        $response = $this->actingAs($this->admin)->get('/dashboard');

        // Assert
        $response->assertInertia(fn ($page) =>
            $page->has('topProperties', 3)
                ->where('topProperties.0.view_count', 200) // Should be ordered by view_count desc
        );
    }

    #[Test]
    public function test_dashboard_shows_recent_bookings()
    {
        // Arrange
        $property = Property::factory()->create();
        Booking::factory()->count(3)->create([
            'property_id' => $property->id,
            'created_at' => now()->subHours(1)
        ]);

        // Act
        $response = $this->actingAs($this->admin)->get('/dashboard');

        // Assert
        $response->assertInertia(fn ($page) =>
            $page->has('recentBookings', 3)
        );
    }

    #[Test]
    public function test_contact_displays_contact_form()
    {
        // Act
        $response = $this->get('/contact');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Contact')
                ->has('businessPhone')
                ->has('businessEmail')
                ->has('recaptchaSiteKey')
                ->has('seoData')
                ->where('seoData.title', 'Contact Us - Bali Villa Spot')
        );
    }

    #[Test]
    public function test_submit_contact_form_validates_required_fields()
    {
        // Act
        $response = $this->post('/contact', []);

        // Assert
        $response->assertSessionHasErrors([
            'name',
            'email',
            'subject',
            'inquiry_type',
            'message',
            'cf-turnstile-response'
        ]);
    }

    #[Test]
    public function test_submit_contact_form_validates_email_format()
    {
        // Act
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'subject' => 'Test Subject',
            'inquiry_type' => 'general',
            'message' => 'Test message',
            'cf-turnstile-response' => 'valid-token'
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function test_submit_contact_form_validates_inquiry_type()
    {
        // Act
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'inquiry_type' => 'invalid_type',
            'message' => 'Test message',
            'cf-turnstile-response' => 'valid-token'
        ]);

        // Assert
        $response->assertSessionHasErrors(['inquiry_type']);
    }

    #[Test]
    public function test_submit_contact_form_sends_email_successfully()
    {
        // Arrange
        // Set a test business email to ensure it's not null/empty in CI
        config(['app.business_email' => 'test@business.com']);
        
        Mail::fake();

        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'subject' => 'Test Subject',
            'inquiry_type' => 'general',
            'property_interest' => 'Villa in Canggu',
            'budget' => '100_300',
            'travel_dates' => 'March 2025',
            'guests' => 4,
            'message' => 'This is a test message',
            'cf-turnstile-response' => 'valid-token'
        ];

        // Act
        $response = $this->post('/contact', $contactData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // More robust email assertion - check if any ContactEnquiryMail was queued
        Mail::assertQueued(ContactEnquiryMail::class);
        
        // Also verify it was sent to the business email
        Mail::assertQueued(ContactEnquiryMail::class, function ($mail) {
            return $mail->hasTo('test@business.com');
        });
    }

    #[Test]
    public function test_submit_contact_form_handles_email_failure()
    {
        // Mock Mail facade to throw exception when queue is called
        Mail::shouldReceive('to')
            ->once()
            ->with(config('app.business_email'))
            ->andReturnSelf();
        Mail::shouldReceive('queue')
            ->once()
            ->with(Mockery::type(ContactEnquiryMail::class))
            ->andThrow(new \Exception('Email service unavailable'));

        // Mock Log to capture the error
        Log::shouldReceive('error')->once();

        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'subject' => 'Test Subject',
            'inquiry_type' => 'general',
            'property_interest' => 'Villa in Canggu',
            'budget' => '100_300',
            'travel_dates' => 'March 2025',
            'guests' => 4,
            'message' => 'This is a test message',
            'cf-turnstile-response' => 'valid-token'
        ];

        // Act
        $response = $this->post('/contact', $contactData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function test_subscribe_validates_email()
    {
        // Act
        $response = $this->post(route('newsletter.subscribe'), ['email' => 'invalid-email']);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Please provide a valid email address.');
    }

    #[Test]
    public function test_subscribe_adds_email_to_mailchimp()
    {
        // Act
        $response = $this->post(route('newsletter.subscribe'), ['email' => 'test@example.com']);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Thank you for subscribing to our newsletter!');
    }

    #[Test]
    public function test_subscribe_handles_duplicate_email()
    {
        // Create a custom controller for this specific test that simulates duplicate email
        $duplicateEmailController = new class extends \App\Http\Controllers\BaseController {
            protected function getRecaptchaRule(): \App\Rules\RecaptchaRule
            {
                return new class extends \App\Rules\RecaptchaRule {
                    public function validate(string $attribute, mixed $value, \Closure $fail): void
                    {
                        return; // Always pass validation for testing
                    }
                };
            }
            
            protected function getMailChimp(): MailChimp
            {
                $mock = Mockery::mock(MailChimp::class);
                $mock->shouldReceive('post')->andReturn([]);
                $mock->shouldReceive('success')->andReturn(false);
                $mock->shouldReceive('getLastError')->andReturn('test@example.com is already a list member');
                return $mock;
            }
        };
        
        // Replace the controller for this specific test
        $this->app->instance(\App\Http\Controllers\BaseController::class, $duplicateEmailController);
        
        // Act
        $response = $this->post(route('newsletter.subscribe'), ['email' => 'test@example.com']);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('info', 'You are already subscribed to our newsletter.');
    }

    #[Test]
    public function test_subscribe_handles_mailchimp_error()
    {
        // Arrange
        // Create a custom controller for this specific test that simulates duplicate email
        $duplicateEmailController = new class extends \App\Http\Controllers\BaseController {
            protected function getRecaptchaRule(): \App\Rules\RecaptchaRule
            {
                return new class extends \App\Rules\RecaptchaRule {
                    public function validate(string $attribute, mixed $value, \Closure $fail): void
                    {
                        return; // Always pass validation for testing
                    }
                };
            }
            
            protected function getMailChimp(): MailChimp
            {
                $mock = Mockery::mock(MailChimp::class);
                $mock->shouldReceive('post')->andReturn([]);
                $mock->shouldReceive('success')->andReturn(false);
                $mock->shouldReceive('getLastError')->andReturn('API key invalid');
                return $mock;
            }
        };
        
        // Replace the controller for this specific test
        $this->app->instance(\App\Http\Controllers\BaseController::class, $duplicateEmailController);

        Log::shouldReceive('error')->once();

        // Act
        $response = $this->post(route('newsletter.subscribe'), ['email' => 'test@example.com']);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'There was an issue subscribing you to the newsletter. Please try again later.');
    }

    #[Test]
    public function test_subscribe_handles_exception()
    {
        Log::shouldReceive('error')->once();

        // Act
        $response = $this->post(route('newsletter.subscribe'), ['email' => 'test@example.com']);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'An unexpected error occurred. Please try again later.');
    }

    #[Test]
    public function test_home_handles_properties_with_attachments()
    {
        // Arrange
        $property = Property::factory()->create(['is_featured' => true]);
        $attachment = PropertyAttachment::factory()->create([
            'property_id' => $property->id,
            'type' => 'image'
        ]);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('featured.0.attachments')
        );
    }

    #[Test]
    public function test_dashboard_requires_authentication()
    {
        // Act
        $response = $this->get('/dashboard');

        // Assert
        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_contact_form_with_all_optional_fields()
    {
        // Arrange
        Mail::fake();
        
        // Mock reCAPTCHA rule
        $this->app->bind(RecaptchaRule::class, function () {
            return Mockery::mock(RecaptchaRule::class, function ($mock) {
                $mock->shouldReceive('passes')->andReturn(true);
            });
        });

        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'subject' => 'Villa Inquiry',
            'inquiry_type' => 'villa_rental',
            'property_interest' => 'Luxury villa with ocean view',
            'budget' => 'over_1000',
            'travel_dates' => 'December 2025',
            'guests' => 8,
            'message' => 'Looking for a luxury villa for my family vacation',
            'cf-turnstile-response' => 'valid-token'
        ];

        // Act
        $response = $this->post('/contact', $contactData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        Mail::assertQueued(ContactEnquiryMail::class);
    }

    #[Test]
    public function test_dashboard_monthly_revenue_calculation()
    {
        // Arrange
        $property = Property::factory()->create();
                
        // Create completed bookings for current month
        Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'completed',
            'total_price' => 1000,
            'check_out_date' => '2025-12-05'
        ]);
        
        Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'completed',
            'total_price' => 1500,
            'check_out_date' => '2025-12-15'
        ]);

        // Create booking for previous month (should not be included)
        Booking::factory()->create([
            'property_id' => $property->id,
            'status' => 'completed',
            'total_price' => 800,
            'check_out_date' => '2025-11-30'
        ]);

        // Act
        $response = $this->actingAs($this->admin)->get('/dashboard');

        // Assert
        $response->assertInertia(fn ($page) =>
            $page->where('stats.monthly_revenue', 2500)
        );
    }

    #[Test]
    public function test_home_seo_data_with_no_featured_properties()
    {
        // Arrange - No properties created

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertInertia(fn ($page) =>
            $page->where('seoData.title', 'Bali Villa Rentals & Land Sales | Villas & Investment Land in Bali')
                ->has('seoData.description')
                ->has('seoData.keywords')
        );
    }
}
