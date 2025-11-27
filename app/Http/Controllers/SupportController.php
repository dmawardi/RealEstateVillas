<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function faq()
    {
        $seoData = [
            'title' => 'FAQ - Frequently Asked Questions | Bali Villa Spot',
            'description' => 'Find answers to common questions about villa rentals, property sales, and services in Bali. Get expert guidance for your property journey.',
            'keywords' => 'Bali property FAQ, villa rental questions, property buying Bali, legal requirements',
            'canonicalUrl' => route('support.faq'),
        ];
        return inertia('support/Faq', [
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
            'seoData' => $seoData,
        ]);
    }

    public function termsOfService()
    {
        $seoData = [
            'title' => 'Terms of Service | Bali Villa Spot',
            'description' => 'Read our terms of service for villa rentals and property sales in Bali. Understand your rights and responsibilities when using our services.',
            'keywords' => 'terms of service, Bali villa rental terms, property sales agreement, legal terms',
            'canonicalUrl' => route('support.termsOfService'),
        ];

        return inertia('support/TermsOfService', [
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
            'seoData' => $seoData,
        ]);
    }

    public function cookiePolicy()
    {
        $seoData = [
            'title' => 'Cookie Policy | Bali Villa Spot',
            'description' => 'Learn about how we use cookies to improve your experience on our website. Manage your cookie preferences and understand our data practices.',
            'keywords' => 'cookie policy, website cookies, privacy preferences, data collection',
            'canonicalUrl' => route('support.cookiePolicy'),
        ];

        return inertia('support/CookiePolicy', [
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
            'seoData' => $seoData,
        ]);
    }

    public function privacyPolicy()
    {
        $seoData = [
            'title' => 'Privacy Policy | Bali Villa Spot',
            'description' => 'Our commitment to protecting your privacy. Learn how we collect, use, and protect your personal information when you use our services.',
            'keywords' => 'privacy policy, data protection, personal information, GDPR compliance',
            'canonicalUrl' => route('support.privacyPolicy'),
        ];

        return inertia('support/PrivacyPolicy', [
            'businessPhone' => config('app.business_phone'),
            'businessEmail' => config('app.business_email'),
            'seoData' => $seoData,
        ]);
    }
}
