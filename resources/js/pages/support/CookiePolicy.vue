<script setup lang="ts">
import SupportContentCard from '@/components/support/SupportContentCard.vue';
import SupportPageLayout from '@/components/support/SupportPageLayout.vue';
import type { SEO } from '@/types';

interface Props {
    businessEmail?: string;
    businessPhone?: string;
    seoData?: SEO;
}
const { businessEmail, businessPhone, seoData } = defineProps<Props>();
const lastUpdated = 'November 27, 2025';
const companyName = 'Bali Villa Spot';
const contactEmail = businessEmail || 'privacy@balivillaspot.com';
</script>

<template>
    <SupportPageLayout
        :businessPhone="businessPhone"
        :businessEmail="businessEmail" 
        :seoData="seoData"
        heroTitle="Cookie Policy"
        heroDescription="Learn how we use cookies to enhance your experience..."
        contactTitle="Questions About Cookies?"
        contactDescription="Contact us for clarification on our cookie practices."
    >
        <div class="space-y-8">
            <!-- Last Updated -->
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 bg-accent/10 text-accent rounded-lg font-body font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Last Updated: {{ lastUpdated }}
                </div>
            </div>

            <!-- Introduction -->
            <SupportContentCard>
                <div class="prose prose-lg max-w-none">
                    <h2 class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-4">What Are Cookies?</h2>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Cookies are small text files that are stored on your device (computer, tablet, or mobile) when you visit our website. They help us provide you with a better browsing experience by remembering your preferences and understanding how you use our villa rental and property services.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        This Cookie Policy explains what cookies we use, why we use them, and how you can manage your cookie preferences when visiting {{ companyName }}.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        By continuing to use our website, you agree to our use of cookies as described in this policy and our <a href="/support/privacy-policy" class="text-accent hover:text-accent-dark">Privacy Policy</a>.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Types of Cookies -->
            <SupportContentCard title="Types of Cookies We Use">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Essential Cookies</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        These cookies are necessary for our website to function properly. They enable basic features like page navigation, secure areas access, and form submissions. Without these cookies, some parts of our website would not work.
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Session Management:</strong> Keep you logged in during your visit</li>
                        <li>• <strong>Security:</strong> Protect against security threats and fraud</li>
                        <li>• <strong>Form Data:</strong> Remember information you've entered in booking forms</li>
                        <li>• <strong>Load Balancing:</strong> Distribute website traffic efficiently</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Analytics Cookies</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We use analytics cookies to understand how visitors interact with our website. This helps us improve our services and user experience.
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Google Analytics:</strong> Track website usage and visitor behavior</li>
                        <li>• <strong>Page Views:</strong> Count visits to different pages and properties</li>
                        <li>• <strong>User Journey:</strong> Understand how users navigate our website</li>
                        <li>• <strong>Performance Metrics:</strong> Monitor website speed and functionality</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Functional Cookies</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        These cookies enhance your experience by remembering your preferences and personalizing content.
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Language Preferences:</strong> Remember your preferred language</li>
                        <li>• <strong>Search Filters:</strong> Save your property search criteria</li>
                        <li>• <strong>Favorites:</strong> Remember properties you've saved or liked</li>
                        <li>• <strong>UI Preferences:</strong> Remember your display settings and preferences</li>
                        <li>• <strong>Currency Selection:</strong> Remember your preferred currency for pricing</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Marketing Cookies</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        With your consent, we use marketing cookies to show you relevant advertisements and measure the effectiveness of our marketing campaigns.
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Facebook Pixel:</strong> Track conversions and show relevant ads on Facebook</li>
                        <li>• <strong>Google Ads:</strong> Measure ad performance and show targeted advertisements</li>
                        <li>• <strong>Remarketing:</strong> Show ads for properties you've viewed</li>
                        <li>• <strong>Email Marketing:</strong> Track email campaign effectiveness</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Third-Party Cookies -->
            <SupportContentCard title="Third-Party Cookies and Services">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We work with trusted third-party services that may set their own cookies on your device. These services help us provide better functionality and understand our website performance.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Google Services</h3>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Google Analytics:</strong> Website traffic analysis and user behavior tracking</li>
                        <li>• <strong>Google Maps:</strong> Interactive maps for property locations</li>
                        <li>• <strong>Google Ads:</strong> Advertising and conversion tracking</li>
                        <li>• <strong>reCAPTCHA:</strong> Spam protection for forms</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Social Media Platforms</h3>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Facebook:</strong> Social media integration and advertising</li>
                        <li>• <strong>Instagram:</strong> Photo sharing and social media features</li>
                        <li>• <strong>WhatsApp:</strong> Messaging integration for customer support</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Other Services</h3>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Payment Processors:</strong> Secure payment handling for bookings</li>
                        <li>• <strong>Customer Support:</strong> Live chat and support ticket systems</li>
                        <li>• <strong>Email Services:</strong> Newsletter and booking confirmation delivery</li>
                        <li>• <strong>CDN Services:</strong> Faster website loading and image delivery</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- How We Use Cookies -->
            <SupportContentCard title="How We Use Cookie Information">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        The information collected through cookies helps us:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-3 mb-6 ml-6">
                        <li>• <strong>Improve User Experience:</strong> Remember your preferences and provide personalized content</li>
                        <li>• <strong>Analyze Website Performance:</strong> Understand which pages are most popular and identify areas for improvement</li>
                        <li>• <strong>Enhance Security:</strong> Detect and prevent fraudulent activity and security threats</li>
                        <li>• <strong>Optimize Marketing:</strong> Show relevant villa and property advertisements based on your interests</li>
                        <li>• <strong>Facilitate Bookings:</strong> Streamline the reservation process and remember your booking progress</li>
                        <li>• <strong>Provide Customer Support:</strong> Offer better assistance based on your browsing history and preferences</li>
                        <li>• <strong>Measure Conversions:</strong> Track successful bookings and inquiries to improve our services</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Cookie Management -->
            <SupportContentCard title="Managing Your Cookie Preferences">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Browser Settings</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        You can control cookies through your web browser settings. Most browsers allow you to:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• View and delete existing cookies</li>
                        <li>• Block cookies from specific websites</li>
                        <li>• Block third-party cookies</li>
                        <li>• Block all cookies (not recommended)</li>
                        <li>• Delete cookies when you close your browser</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Browser-Specific Instructions</h3>
                    <div class="bg-secondary/10 rounded-xl p-6 border border-secondary/20 mb-6">
                        <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2">
                            <li>• <strong>Chrome:</strong> Settings > Privacy and Security > Cookies and other site data</li>
                            <li>• <strong>Firefox:</strong> Preferences > Privacy & Security > Cookies and Site Data</li>
                            <li>• <strong>Safari:</strong> Preferences > Privacy > Manage Website Data</li>
                            <li>• <strong>Edge:</strong> Settings > Cookies and site permissions > Cookies and site data</li>
                        </ul>
                    </div>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Opt-Out Links</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        You can opt out of specific third-party cookies using these links:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" class="text-accent hover:text-accent-dark">Google Analytics Opt-out</a></li>
                        <li>• <a href="https://www.google.com/settings/ads" target="_blank" class="text-accent hover:text-accent-dark">Google Ads Settings</a></li>
                        <li>• <a href="https://www.facebook.com/settings?tab=ads" target="_blank" class="text-accent hover:text-accent-dark">Facebook Ad Preferences</a></li>
                        <li>• <a href="http://optout.networkadvertising.org/" target="_blank" class="text-accent hover:text-accent-dark">Network Advertising Initiative Opt-out</a></li>
                    </ul>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-amber-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <div>
                                <h4 class="font-display text-lg font-semibold text-amber-800 mb-2">Important Note</h4>
                                <p class="font-body text-amber-700 leading-relaxed">
                                    Disabling cookies may affect your ability to use certain features of our website, such as saving favorite properties, remembering search preferences, or completing bookings. Essential cookies are necessary for basic functionality and cannot be disabled.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </SupportContentCard>

            <!-- Mobile Devices -->
            <SupportContentCard title="Cookies on Mobile Devices">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        When you access our website through mobile devices, we may collect information through cookies and similar technologies such as:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Mobile Identifiers:</strong> Device-specific identifiers for analytics and advertising</li>
                        <li>• <strong>App Data:</strong> If you use our mobile app, we collect usage and preference data</li>
                        <li>• <strong>Location Data:</strong> With your permission, to show nearby properties</li>
                        <li>• <strong>Push Notifications:</strong> To send booking updates and property alerts</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        You can manage these preferences through your device settings and app permissions. Location sharing and push notifications require your explicit consent.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Cookie Retention -->
            <SupportContentCard title="Cookie Retention Periods">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Different types of cookies are stored for different periods:
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Session Cookies</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Temporary cookies that are deleted when you close your browser. Used for session management and security.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Persistent Cookies</h3>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Functional Cookies:</strong> Up to 2 years (preferences, language settings)</li>
                        <li>• <strong>Analytics Cookies:</strong> Up to 2 years (Google Analytics)</li>
                        <li>• <strong>Marketing Cookies:</strong> 30-90 days (advertising campaigns)</li>
                        <li>• <strong>Authentication Cookies:</strong> 30 days (remember login status)</li>
                    </ul>

                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        You can clear all cookies at any time through your browser settings, which will reset all preferences and require you to log in again.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Updates to Policy -->
            <SupportContentCard title="Changes to This Cookie Policy">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We may update this Cookie Policy from time to time to reflect changes in our practices, technology, or legal requirements. When we make significant changes, we will:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Update the "Last Updated" date at the top of this policy</li>
                        <li>• Post the revised policy on our website</li>
                        <li>• Notify you through email or website banner if changes are substantial</li>
                        <li>• Request new consent for new types of cookies if required by law</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        We encourage you to review this Cookie Policy periodically to stay informed about how we use cookies and similar technologies.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Contact Information -->
            <SupportContentCard title="Contact Us About Cookies">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        If you have any questions about this Cookie Policy or how we use cookies, please contact us:
                    </p>
                    <div class="bg-secondary/10 rounded-xl p-6 border border-secondary/20">
                        <h4 class="font-display text-lg font-semibold text-primary dark:text-gray-100 mb-3">{{ companyName }}</h4>
                        <div class="space-y-2 font-body text-primary/80 dark:text-gray-300">
                            <p><strong>Email:</strong> <a href="mailto:{{ contactEmail }}" class="text-accent hover:text-accent-dark">{{ contactEmail }}</a></p>
                            <p v-if="businessPhone"><strong>Phone:</strong> <a :href="`tel:${businessPhone}`" class="text-accent hover:text-accent-dark">{{ businessPhone }}</a></p>
                            <p><strong>Subject:</strong> Cookie Policy Inquiry</p>
                            <p><strong>Address:</strong> Bali, Indonesia</p>
                        </div>
                    </div>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mt-4">
                        We will respond to your cookie-related inquiries promptly and help you understand or modify your cookie preferences as needed.
                    </p>

                    <div class="mt-6 p-4 bg-accent/5 border border-accent/20 rounded-xl">
                        <p class="font-body text-primary/80 dark:text-gray-300 text-sm leading-relaxed">
                            <strong>Quick Reference:</strong> For immediate cookie management, you can adjust your browser settings or use the opt-out links provided above. For questions about how cookie data relates to your personal information, please refer to our <a href="/support/privacy-policy" class="text-accent hover:text-accent-dark">Privacy Policy</a>.
                        </p>
                    </div>
                </div>
            </SupportContentCard>
        </div>
    </SupportPageLayout>
</template>