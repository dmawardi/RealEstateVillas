<script setup lang="ts">
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
        heroTitle="Privacy Policy"
        heroDescription="Learn how we protect your personal information..."
        contactTitle="Questions About Privacy?"
        contactDescription="Contact us for clarification on our privacy practices."
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
                    <h2 class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-4">Introduction</h2>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        {{ companyName }} ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website, use our services, or engage with us for villa rentals and property services in Bali, Indonesia.
                    </p>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-6">
                        By using our services, you consent to the data practices described in this policy. If you do not agree with this policy, please do not use our services.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Information We Collect -->
            <SupportContentCard title="Information We Collect">
                <div class="prose prose-lg max-w-none">
                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Personal Information</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We may collect personal information that you provide directly to us, including:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Name, email address, phone number, and postal address</li>
                        <li>• Passport information and travel documentation (for bookings)</li>
                        <li>• Payment information and billing details</li>
                        <li>• Property preferences and rental requirements</li>
                        <li>• Communication history and feedback</li>
                        <li>• Emergency contact information</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Automatically Collected Information</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        When you visit our website, we automatically collect certain information:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• IP address and device information</li>
                        <li>• Browser type and operating system</li>
                        <li>• Pages visited and time spent on our website</li>
                        <li>• Referral sources and search terms</li>
                        <li>• Cookies and similar tracking technologies</li>
                    </ul>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Third-Party Information</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        We may receive information about you from third parties such as social media platforms, payment processors, and travel booking platforms when you interact with us through these services.
                    </p>
                </div>
            </SupportContentCard>

            <!-- How We Use Your Information -->
            <SupportContentCard title="How We Use Your Information">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We use the information we collect for various purposes, including:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Processing and managing your villa bookings and property inquiries</li>
                        <li>• Providing customer support and responding to your requests</li>
                        <li>• Sending booking confirmations, updates, and important notices</li>
                        <li>• Processing payments and managing billing</li>
                        <li>• Improving our website, services, and customer experience</li>
                        <li>• Marketing our services (with your consent)</li>
                        <li>• Complying with legal obligations and preventing fraud</li>
                        <li>• Conducting business analysis and market research</li>
                    </ul>
                </div>
            </SupportContentCard>

            <!-- Information Sharing -->
            <SupportContentCard title="How We Share Your Information">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We may share your information in the following circumstances:
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Service Providers</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We share information with trusted third-party service providers who assist us in operating our business, including payment processors, housekeeping services, property managers, technology providers, legal consultants, and notaries involved in property transactions.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Property Owners</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        For villa rentals, we share necessary booking information with property owners to facilitate your stay, including guest names, contact information, and stay details.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Legal Requirements</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We may disclose information when required by law, to protect our rights, or in response to legal processes such as subpoenas or court orders.
                    </p>

                    <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">Business Transfers</h3>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        In the event of a merger, acquisition, or sale of assets, your information may be transferred to the new entity, subject to the same privacy protections.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Data Security -->
            <SupportContentCard title="Data Security">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• SSL encryption for data transmission</li>
                        <li>• Secure data storage and access controls</li>
                        <li>• Regular security assessments and updates</li>
                        <li>• Employee training on data protection</li>
                        <li>• Limited access to personal information on a need-to-know basis</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        However, no method of transmission over the internet or electronic storage is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Your Rights -->
            <SupportContentCard title="Your Privacy Rights">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Depending on your location, you may have the following rights regarding your personal information:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Access:</strong> Request copies of your personal information</li>
                        <li>• <strong>Rectification:</strong> Request correction of inaccurate or incomplete information</li>
                        <li>• <strong>Erasure:</strong> Request deletion of your personal information</li>
                        <li>• <strong>Portability:</strong> Request transfer of your information to another service</li>
                        <li>• <strong>Restriction:</strong> Request limitation of processing your information</li>
                        <li>• <strong>Objection:</strong> Object to certain types of processing</li>
                        <li>• <strong>Withdrawal:</strong> Withdraw consent where processing is based on consent</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        To exercise these rights, please contact us at <a href="mailto:{{ contactEmail }}" class="text-accent hover:text-accent-dark">{{ contactEmail }}</a>. We will respond to your request within the timeframe required by applicable law.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Cookies and Tracking -->
            <SupportContentCard title="Cookies and Tracking Technologies">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We use cookies and similar tracking technologies to enhance your browsing experience, analyze website traffic, and personalize content. Types of cookies we use include:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• <strong>Essential Cookies:</strong> Necessary for website functionality</li>
                        <li>• <strong>Analytics Cookies:</strong> Help us understand website usage</li>
                        <li>• <strong>Marketing Cookies:</strong> Used to deliver relevant advertisements</li>
                        <li>• <strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        You can control cookies through your browser settings. However, disabling certain cookies may affect your ability to use some features of our website. For more detailed information, please see our <a href="/support/cookie-policy" class="text-accent hover:text-accent-dark">Cookie Policy</a>.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Data Retention -->
            <SupportContentCard title="Data Retention">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. Factors that determine retention periods include:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• The nature of the information and why it was collected</li>
                        <li>• Legal, regulatory, and compliance requirements</li>
                        <li>• Business operational needs</li>
                        <li>• Your ongoing relationship with us</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        When information is no longer needed, we securely delete or anonymize it in accordance with our data retention policies.
                    </p>
                </div>
            </SupportContentCard>

            <!-- International Transfers -->
            <SupportContentCard title="International Data Transfers">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        As we operate in Bali, Indonesia, and may work with international service providers, your information may be transferred to and processed in countries other than your country of residence. We ensure that such transfers comply with applicable data protection laws and implement appropriate safeguards to protect your information.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Children's Privacy -->
            <SupportContentCard title="Children's Privacy">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        Our services are not intended for children under 18 years of age. We do not knowingly collect personal information from children under 18. If you believe we have inadvertently collected information from a child under 18, please contact us immediately so we can delete such information.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Changes to Policy -->
            <SupportContentCard title="Changes to This Privacy Policy">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal requirements, or other factors. When we make changes, we will:
                    </p>
                    <ul class="font-body text-primary/80 dark:text-gray-300 space-y-2 mb-6 ml-6">
                        <li>• Update the "Last Updated" date at the top of this policy</li>
                        <li>• Post the revised policy on our website</li>
                        <li>• Notify you of significant changes via email or website notice</li>
                    </ul>
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                        We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information.
                    </p>
                </div>
            </SupportContentCard>

            <!-- Contact Information -->
            <SupportContentCard title="Contact Us">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:
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
                        We will respond to your inquiry as promptly as possible, typically within 30 days of receiving your request.
                    </p>
                </div>
            </SupportContentCard>
        </div>
    </SupportPageLayout>
</template>