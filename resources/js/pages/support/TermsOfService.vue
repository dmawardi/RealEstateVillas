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
const contactEmail = businessEmail || 'contact@balivillaspot.com';
</script>

<template>
    <SupportPageLayout
        :businessPhone="businessPhone"
        :businessEmail="businessEmail" 
        :seoData="seoData"
        heroTitle="Terms of Service"
        heroDescription="Understand the terms and conditions for using our services."
        contactTitle="Questions About Terms?"
        contactDescription="Contact us for clarification on our terms of service."
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
                    <h2 class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-4">Agreement to Terms</h2>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Welcome to {{ companyName }}. These Terms of Service ("Terms") constitute a legally binding agreement between you and {{ companyName }} ("we," "our," or "us") regarding your use of our website, villa rental services, and property sales services in Bali, Indonesia.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        By accessing our website or using our services, you agree to be bound by these Terms. If you do not agree to these Terms, you may not use our services.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        We reserve the right to modify these Terms at any time. Changes will be effective immediately upon posting on our website. Your continued use of our services after any modifications constitutes acceptance of the updated Terms.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Services Description -->
            <SupportContentCard title="Our Services">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        {{ companyName }} provides the following services:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Villa Rental Services:</strong> Booking and management of vacation rental properties in Bali</li>
                        <li>• <strong>Property Sales:</strong> Assistance with buying and selling real estate in Bali</li>
                        <li>• <strong>Property Management:</strong> Ongoing management and maintenance of rental properties</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        All services are subject to availability and may vary depending on the specific property or circumstances involved.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Villa Rental Terms -->
            <SupportContentCard title="Villa Rental Terms">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Booking and Reservations</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        To book a villa rental, you must:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Be at least 18 years old or have parental consent</li>
                        <li>• Provide accurate and complete booking information</li>
                        <li>• Pay the required deposit to confirm your reservation</li>
                        <li>• Provide valid identification and travel documents</li>
                        <li>• Agree to the specific property rules and guidelines</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Payment Terms</h3>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• A 30% deposit is required to confirm your booking</li>
                        <li>• The remaining balance is due 30 days before arrival</li>
                        <li>• For bookings made within 30 days of arrival, full payment is required immediately</li>
                        <li>• Additional charges may apply for extra services or damages</li>
                        <li>• All payments are subject to our refund and cancellation policies</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Cancellation Policy</h3>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Cancellations more than 30 days before arrival: Full refund minus processing fee</li>
                        <li>• Cancellations 14-30 days before arrival: 50% refund</li>
                        <li>• Cancellations less than 14 days before arrival: No refund</li>
                        <li>• Emergency cancellations may be considered on a case-by-case basis</li>
                        <li>• Travel insurance is recommended to protect against unforeseen circumstances</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Guest Responsibilities</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        As a guest, you agree to:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Treat the property with respect and care</li>
                        <li>• Follow all property rules and local regulations</li>
                        <li>• Not exceed the maximum occupancy limit</li>
                        <li>• Report any damages or issues immediately</li>
                        <li>• Allow reasonable access for maintenance and cleaning</li>
                        <li>• Respect neighbors and maintain appropriate noise levels</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Property Sales Terms -->
            <SupportContentCard title="Property Sales Terms">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Buying Services</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Our property buying assistance includes:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Negotiation support and price guidance</li>
                        <li>• Coordination with legal and financial professionals</li>
                        <li>• Post-purchase support and property management options</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Selling Services</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        For property owners looking to sell, we provide:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Marketing and advertising services</li>
                        <li>• Transaction coordination and support</li>
                        <li>• Legal and regulatory compliance assistance</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Commission and Fees</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Our fees for property sales services will be clearly disclosed and agreed upon in writing before any services are provided. Fees typically include:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Sales/Rental commission (percentage of price/rate)</li>
                        <li>• Marketing and advertising costs</li>
                        <li>• Legal and administrative fees</li>
                        <li>• Third-party service provider costs</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- User Conduct -->
            <SupportContentCard title="User Conduct and Prohibited Activities">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        You agree not to use our services for any unlawful or prohibited activities, including:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Providing false or misleading information</li>
                        <li>• Engaging in fraudulent or deceptive practices</li>
                        <li>• Violating any local, national, or international laws</li>
                        <li>• Damaging or interfering with our website or services</li>
                        <li>• Using our services for commercial purposes without authorization</li>
                        <li>• Harassing or threatening our staff or other users</li>
                        <li>• Attempting to circumvent security measures</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        We reserve the right to suspend or terminate services for any user who violates these conduct requirements.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Liability and Insurance -->
            <SupportContentCard title="Liability and Insurance">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Limitation of Liability</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        To the maximum extent permitted by law, {{ companyName }} shall not be liable for:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Indirect, incidental, or consequential damages</li>
                        <li>• Loss of profits, data, or business opportunities</li>
                        <li>• Damages caused by third parties or force majeure events</li>
                        <li>• Issues arising from property conditions beyond our control</li>
                        <li>• Travel disruptions or changes in travel plans</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Insurance Requirements</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We strongly recommend that all users obtain appropriate insurance coverage, including:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Travel insurance for villa rentals</li>
                        <li>• Property insurance for purchases</li>
                        <li>• Personal liability coverage</li>
                        <li>• Medical and emergency coverage while in Indonesia</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Intellectual Property -->
            <SupportContentCard title="Intellectual Property">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        All content on our website, including but not limited to text, graphics, logos, images, and software, is the property of {{ companyName }} or our licensors and is protected by intellectual property laws.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        You may not:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Copy, modify, or distribute our content without permission</li>
                        <li>• Use our trademarks or branding without authorization</li>
                        <li>• Create derivative works based on our content</li>
                        <li>• Use our content for commercial purposes without a license</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Dispute Resolution -->
            <SupportContentCard title="Dispute Resolution">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Governing Law</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        These Terms shall be governed by and construed in accordance with the laws of Indonesia, without regard to conflict of law principles.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Dispute Resolution Process</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        In the event of any dispute, we encourage the following resolution process:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Direct communication with our customer service team</li>
                        <li>• Mediation through a mutually agreed mediator</li>
                        <li>• Arbitration in accordance with Indonesian arbitration rules</li>
                        <li>• Legal proceedings in Indonesian courts as a last resort</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Privacy and Data -->
            <SupportContentCard title="Privacy and Data Protection">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Your privacy is important to us. Our collection, use, and protection of your personal information is governed by our <a href="/support/privacy-policy" class="text-accent hover:text-accent-dark">Privacy Policy</a>, which is incorporated into these Terms by reference.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        By using our services, you consent to the collection and use of your information as described in our Privacy Policy.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Termination -->
            <SupportContentCard title="Termination">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We reserve the right to terminate or suspend your access to our services at any time, with or without cause, and with or without notice, for any reason including:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Violation of these Terms</li>
                        <li>• Fraudulent or illegal activity</li>
                        <li>• Failure to pay fees or charges</li>
                        <li>• Abusive or inappropriate behavior</li>
                        <li>• Discontinuation of our services</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        Upon termination, your right to use our services will cease immediately, but these Terms will remain in effect regarding any previous use of our services.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Severability -->
            <SupportContentCard title="Severability and Entire Agreement">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Severability</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        If any provision of these Terms is found to be unenforceable or invalid, that provision will be limited or eliminated to the minimum extent necessary so that the remainder of these Terms will remain in full force and effect.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Entire Agreement</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        These Terms, along with our Privacy Policy and any additional terms for specific services, constitute the entire agreement between you and {{ companyName }} regarding your use of our services.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Contact Information -->
            <SupportContentCard title="Contact Information">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        If you have any questions about these Terms of Service, please contact us:
                    </p>
                    <div class="bg-secondary/10 rounded-xl p-6 border border-secondary/20">
                        <h4 class="font-display text-lg font-semibold text-primary dark:text-gray-100 mb-3">{{ companyName }}</h4>
                        <div class="space-y-2 font-body text-primary/80 dark:text-gray-300">
                            <p><strong>Email:</strong> <a href="mailto:{{ contactEmail }}" class="text-accent hover:text-accent-dark">{{ contactEmail }}</a></p>
                            <p v-if="businessPhone"><strong>Phone:</strong> <a :href="`tel:${businessPhone}`" class="text-accent hover:text-accent-dark">{{ businessPhone }}</a></p>
                            <p><strong>Address:</strong> Bali, Indonesia</p>
                        </div>
                    </div>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mt-4">
                        We will respond to your inquiry promptly and work to resolve any concerns you may have.
                    </p>
                </div>
            </SupportContentCard>
        </div>
    </SupportPageLayout>
</template>