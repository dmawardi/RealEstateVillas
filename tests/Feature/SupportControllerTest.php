<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test config values
        Config::set('app.business_phone', '+62 123 456 7890');
        Config::set('app.business_email', 'test@example.com');
    }

    #[Test]
    public function test_faq_displays_faq_page()
    {
        $response = $this->get(route('support.faq'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('support/Faq')
                ->has('businessPhone')
                ->has('businessEmail')
                ->has('seoData')
        );
    }

    #[Test]
    public function test_faq_includes_correct_seo_data()
    {
        $response = $this->get(route('support.faq'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('seoData.title')
                ->has('seoData.description')
                ->has('seoData.keywords')
                ->has('seoData.canonicalUrl')
                ->where('seoData.title', 'FAQ - Frequently Asked Questions | Bali Villa Spot')
                ->where('seoData.description', 'Find answers to common questions about villa rentals, property sales, and services in Bali. Get expert guidance for your property journey.')
                ->where('seoData.keywords', 'Bali property FAQ, villa rental questions, property buying Bali, legal requirements')
                ->where('seoData.canonicalUrl', route('support.faq'))
        );
    }

    #[Test]
    public function test_faq_includes_business_contact_info()
    {
        $response = $this->get(route('support.faq'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('businessPhone', '+62 123 456 7890')
                ->where('businessEmail', 'test@example.com')
        );
    }

    #[Test]
    public function test_terms_of_service_displays_terms_page()
    {
        $response = $this->get(route('support.termsOfService'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('support/TermsOfService')
                ->has('businessPhone')
                ->has('businessEmail')
                ->has('seoData')
        );
    }

    #[Test]
    public function test_terms_of_service_includes_correct_seo_data()
    {
        $response = $this->get(route('support.termsOfService'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('seoData.title')
                ->has('seoData.description')
                ->has('seoData.keywords')
                ->has('seoData.canonicalUrl')
                ->where('seoData.title', 'Terms of Service | Bali Villa Spot')
                ->where('seoData.description', 'Read our terms of service for villa rentals and property sales in Bali. Understand your rights and responsibilities when using our services.')
                ->where('seoData.keywords', 'terms of service, Bali villa rental terms, property sales agreement, legal terms')
                ->where('seoData.canonicalUrl', route('support.termsOfService'))
        );
    }

    #[Test]
    public function test_terms_of_service_includes_business_contact_info()
    {
        $response = $this->get(route('support.termsOfService'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('businessPhone', '+62 123 456 7890')
                ->where('businessEmail', 'test@example.com')
        );
    }

    #[Test]
    public function test_cookie_policy_displays_cookie_policy_page()
    {
        $response = $this->get(route('support.cookiePolicy'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('support/CookiePolicy')
                ->has('businessPhone')
                ->has('businessEmail')
                ->has('seoData')
        );
    }

    #[Test]
    public function test_cookie_policy_includes_correct_seo_data()
    {
        $response = $this->get(route('support.cookiePolicy'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('seoData.title')
                ->has('seoData.description')
                ->has('seoData.keywords')
                ->has('seoData.canonicalUrl')
                ->where('seoData.title', 'Cookie Policy | Bali Villa Spot')
                ->where('seoData.description', 'Learn about how we use cookies to improve your experience on our website. Manage your cookie preferences and understand our data practices.')
                ->where('seoData.keywords', 'cookie policy, website cookies, privacy preferences, data collection')
                ->where('seoData.canonicalUrl', route('support.cookiePolicy'))
        );
    }

    #[Test]
    public function test_cookie_policy_includes_business_contact_info()
    {
        $response = $this->get(route('support.cookiePolicy'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('businessPhone', '+62 123 456 7890')
                ->where('businessEmail', 'test@example.com')
        );
    }

    #[Test]
    public function test_privacy_policy_displays_privacy_policy_page()
    {
        $response = $this->get(route('support.privacyPolicy'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('support/PrivacyPolicy')
                ->has('businessPhone')
                ->has('businessEmail')
                ->has('seoData')
        );
    }

    #[Test]
    public function test_privacy_policy_includes_correct_seo_data()
    {
        $response = $this->get(route('support.privacyPolicy'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('seoData.title')
                ->has('seoData.description')
                ->has('seoData.keywords')
                ->has('seoData.canonicalUrl')
                ->where('seoData.title', 'Privacy Policy | Bali Villa Spot')
                ->where('seoData.description', 'Our commitment to protecting your privacy. Learn how we collect, use, and protect your personal information when you use our services.')
                ->where('seoData.keywords', 'privacy policy, data protection, personal information, GDPR compliance')
                ->where('seoData.canonicalUrl', route('support.privacyPolicy'))
        );
    }

    #[Test]
    public function test_privacy_policy_includes_business_contact_info()
    {
        $response = $this->get(route('support.privacyPolicy'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('businessPhone', '+62 123 456 7890')
                ->where('businessEmail', 'test@example.com')
        );
    }

    #[Test]
    public function test_all_support_routes_are_publicly_accessible()
    {
        // Test that all support routes don't require authentication
        $routes = [
            'support.faq',
            'support.termsOfService',
            'support.cookiePolicy',
            'support.privacyPolicy'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
            // Should not redirect (status would be 302 if redirecting to login)
            $response->assertOk();
        }
    }

    #[Test]
    public function test_all_support_pages_have_consistent_structure()
    {
        $routes = [
            'support.faq' => 'support/Faq',
            'support.termsOfService' => 'support/TermsOfService',
            'support.cookiePolicy' => 'support/CookiePolicy',
            'support.privacyPolicy' => 'support/PrivacyPolicy'
        ];

        foreach ($routes as $routeName => $component) {
            $response = $this->get(route($routeName));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->component($component)
                    ->has('businessPhone')
                    ->has('businessEmail')
                    ->has('seoData')
                    ->has('seoData.title')
                    ->has('seoData.description')
                    ->has('seoData.keywords')
                    ->has('seoData.canonicalUrl')
            );
        }
    }

    #[Test]
    public function test_support_routes_handle_missing_config_gracefully()
    {
        // Clear config values to test graceful handling
        Config::set('app.business_phone', null);
        Config::set('app.business_email', null);

        $response = $this->get(route('support.faq'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('businessPhone')
                ->has('businessEmail')
        );
    }

    #[Test]
    public function test_seo_canonical_urls_are_absolute()
    {
        $routes = [
            'support.faq',
            'support.termsOfService', 
            'support.cookiePolicy',
            'support.privacyPolicy'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->where('seoData.canonicalUrl', route($route))
            );

            // Verify the canonical URL is absolute
            $canonicalUrl = route($route);
            $this->assertStringContainsString('http', $canonicalUrl);
        }
    }

    #[Test]
    public function test_seo_data_contains_required_fields()
    {
        $routes = ['support.faq', 'support.termsOfService', 'support.cookiePolicy', 'support.privacyPolicy'];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->has('seoData')
                    ->has('seoData.title')
                    ->has('seoData.description')
                    ->has('seoData.keywords')
                    ->has('seoData.canonicalUrl')
                    ->where('seoData.title', fn ($title) => !empty($title))
                    ->where('seoData.description', fn ($description) => !empty($description))
                    ->where('seoData.keywords', fn ($keywords) => !empty($keywords))
                    ->where('seoData.canonicalUrl', fn ($url) => !empty($url))
            );
        }
    }

    #[Test]
    public function test_support_page_titles_follow_consistent_format()
    {
        $expectedTitles = [
            'support.faq' => 'FAQ - Frequently Asked Questions | Bali Villa Spot',
            'support.termsOfService' => 'Terms of Service | Bali Villa Spot',
            'support.cookiePolicy' => 'Cookie Policy | Bali Villa Spot',
            'support.privacyPolicy' => 'Privacy Policy | Bali Villa Spot'
        ];

        foreach ($expectedTitles as $route => $expectedTitle) {
            $response = $this->get(route($route));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->where('seoData.title', $expectedTitle)
            );
        }
    }

    #[Test]
    public function test_support_routes_return_correct_http_headers()
    {
        $routes = ['support.faq', 'support.termsOfService', 'support.cookiePolicy', 'support.privacyPolicy'];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'text/html; charset=UTF-8');
        }
    }
}
